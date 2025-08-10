<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=mtpicaxm_bible-game', 'mtpicaxm_mtpisgahmedia', 'lordJesus2021!!');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = (int)($_GET['id'] ?? 0);
$team = (int)($_GET['team'] ?? 1);
$result = $_GET['result'] ?? '';

$stmt = $pdo->prepare("SELECT question, answer, points FROM questions WHERE id = ?");
$stmt->execute([$id]);
$qa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$qa) {
    die("Question not found.");
}

$points = (int)$qa['points'];

// Show page first, logic executes only after overlay
$shouldRedirect = false;
$finalRedirectResult = '';

if (in_array($result, ['correct', 'wrong', 'timeout'])) {
    $shouldRedirect = true;
    $finalRedirectResult = $result === 'timeout' ? 'wrong' : $result;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Answer</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 40px; background: #f9f9f9; }
        .btn {
            margin: 15px 10px;
            padding: 15px 30px;
            font-size: 18px;
            background: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            cursor: pointer;
        }
        .correct { background-color: #4CAF50; }
        .wrong { background-color: #f44336; }

        #overlay {
            white-space: pre-line;
            position: fixed;
            top:0; left:0; width:100%; height:100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 48px;
            font-weight: bold;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        #overlay.show {
            opacity: 1;
            pointer-events: auto;
        }
        #overlay.correct { background-color: rgba(76,175,80,0.85); }
        #overlay.wrong { background-color: rgba(244,67,54,0.85); }
        #overlay.timeout { background-color: rgba(255, 193, 7, 0.85); }
        .marker-effect {
            font-size: 24px;
            margin-top: 20px;
            padding: 10px;
            border-radius: 10px;
            background: rgba(255,255,255,0.2);
        }
    </style>
</head>
<body>
    <h2>Question:</h2>
    <p style="font-size: 24px;"><?php echo htmlspecialchars($qa['question']); ?></p>

    <h2>Answer:</h2>
    <p style="font-size: 28px; font-weight: bold; color: green;"><?php echo htmlspecialchars($qa['answer']); ?></p>

    <a href="#" onclick="handleAnswer('correct')" class="btn correct">Correct</a>
    <a href="#" onclick="handleAnswer('wrong')" class="btn wrong">Wrong</a>

    <audio id="chime" src="sounds/chime.mp3"></audio>
    <audio id="buzzer" src="sounds/buzzer.mp3"></audio>

    <div id="overlay"></div>

    <script>
        const overlay = document.getElementById('overlay');
        const chime = document.getElementById('chime');
        const buzzer = document.getElementById('buzzer');

        function handleAnswer(result) {
            let pointsText = '';
            let text = '';
            let overlayClass = '';

            if (result === 'correct') {
                pointsText = "+<?php echo $points; ?> pts";
                text = "Correct!\n" + pointsText;
                overlayClass = 'show correct';
                chime.play();
            } else if (result === 'wrong') {
                pointsText = "+0 pts";
                text = "Wrong!\n" + pointsText;
                overlayClass = 'show wrong';
                buzzer.play();
            } else if (result === 'timeout') {
                pointsText = "+0 pts";
                text = "Time’s Up!\n" + pointsText;
                overlayClass = 'show timeout';
                buzzer.play();
            }

            overlay.textContent = text;
            overlay.className = overlayClass;
            overlay.style.whiteSpace = "pre-line";

            setTimeout(() => {
                window.location.href = `answer.php?id=<?php echo $id; ?>&team=<?php echo $team; ?>&points=<?php echo $points; ?>&result=${result === 'timeout' ? 'wrong' : result}&final=1`;
            }, 1400);
        }

        <?php if ($shouldRedirect): ?>
            handleAnswer('<?php echo $result; ?>');
        <?php endif; ?>
    </script>
</body>
</html>

<?php
// Only execute score updates and game progression after redirect from overlay
if (isset($_GET['final']) && in_array($finalRedirectResult, ['correct', 'wrong'])) {
    if (!isset($_SESSION['answered_questions'][$id])) {
        $_SESSION['answered_questions'][$id] = $finalRedirectResult;
        $_SESSION['turns_this_round'] = ($_SESSION['turns_this_round'] ?? 0) + 1;

        $total_teams = $_SESSION['teams'] ?? 2;
        $_SESSION['current_team'] = ($_SESSION['current_team'] ?? 1) % $total_teams + 1;

        if (!isset($_SESSION['scores'][$team])) {
            $_SESSION['scores'][$team] = 0;
        }

        $current_round = $_SESSION['round'] ?? 1;
        $round_multiplier = ($current_round >= 1 && $current_round <= 3) ? $current_round : 1;

        $team_brr = $_SESSION['brr'][$team] ?? ['active' => false, 'multiplier' => 1, 'questions_left' => 0];
        $markerEffect = $_SESSION['active_marker'] ?? null;
        $markerPoints = 0;

        if ($markerEffect && $markerEffect['question_id'] == $id) {
            if ($markerEffect['type'] === 'HB') {
                $markerPoints = ($finalRedirectResult === 'correct') ? $points + 10 : $points;
            } elseif ($markerEffect['type'] === 'DC') {
                $markerPoints = ($finalRedirectResult === 'correct') ? ceil($points / 2) : -10;
            }
            $_SESSION['scores'][$team] += $markerPoints;
            unset($_SESSION['active_marker']);
        } else {
            if ($finalRedirectResult === 'correct') {
                if ($team_brr['active']) {
                    $_SESSION['scores'][$team] += $points * $round_multiplier * 2;
                    $_SESSION['brr'][$team]['questions_left']--;
                } else {
                    $_SESSION['scores'][$team] += $points * $round_multiplier;
                }
            } else {
                if ($team_brr['active']) {
                    $_SESSION['scores'][$team] -= 2 * $points;
                    $_SESSION['brr'][$team]['questions_left']--;
                }
            }
        }

        if ($team_brr['active'] && $_SESSION['brr'][$team]['questions_left'] <= 0) {
            $_SESSION['brr'][$team] = ['active' => false, 'multiplier' => 1, 'questions_left' => 0];
        }

        // ✅ TRACK CORRECT ANSWER COUNT
        if ($finalRedirectResult === 'correct') {
            if (!isset($_SESSION['correct_counts'][$team])) {
                $_SESSION['correct_counts'][$team] = 0;
            }
            $_SESSION['correct_counts'][$team]++;
        }
    }

    if ($finalRedirectResult === 'correct' && in_array($current_round, [2, 3]) && !($team_brr['active'] ?? false)) {
        header("Location: offer-brr.php?id=$id&team=$team&points=$points");
        exit;
    }

    header("Location: game-menu.php");
    exit;
}
?>
