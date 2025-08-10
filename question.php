<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=mtpicaxm_bible-game', 'mtpicaxm_mtpisgahmedia', 'lordJesus2021!!');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = (int)($_GET['id'] ?? 0);
$team = (int)($_GET['team'] ?? 1);

$stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
$stmt->execute([$id]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$question) {
    die("Question not found.");
}

$markerResult = $_SESSION['marker_results'][$team][$id] ?? null;
$markerType = $markerResult['type'] ?? null;
$markerCorrect = $markerResult['correct'] ?? null;

// If time runs out, apply penalty right now (JS will redirect)
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $points = (int)$question['points'];
    $finalPoints = 0;

    if ($markerType === 'HB') {
        if ($markerCorrect) {
            $finalPoints = -1 * $points; // HB accepted, marker failed = wrong
        } else {
            $finalPoints = 0; // HB ignored, wrong = no penalty
        }
    } elseif ($markerType === 'DC') {
        if ($markerCorrect) {
            $finalPoints = -2 * $points;
        } else {
            $finalPoints = -1 * $points; // DC ignored, wrong = -1x
        }
    } else {
        $finalPoints = 0; // No marker, wrong = no points
    }

    $_SESSION['scores'][$team] = ($_SESSION['scores'][$team] ?? 0) + $finalPoints;

    // Advance turn
    $_SESSION['current_team'] = ($_SESSION['current_team'] % count($_SESSION['team_names'])) + 1;
    $_SESSION['turns_this_round'] = ($_SESSION['turns_this_round'] ?? 0) + 1;

    header("Location: game-menu.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Question</title>
<style>
    body { font-family: Arial, sans-serif; padding: 40px; max-width: 700px; margin: auto; background: #f9f9f9; text-align: center; }
    .question-box { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .timer { font-size: 36px; margin-top: 20px; color: #333; }
    .btn { margin-top: 30px; padding: 15px 30px; font-size: 20px; background: #007BFF; color: white; border: none; border-radius: 6px; cursor: pointer; }
    .overlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        display: none;
        justify-content: center;
        align-items: center;
        font-size: 48px;
        z-index: 999;
    }
    .timeout-overlay { background: rgba(255, 204, 0, 0.95); color: black; }
</style>
</head>
<body>

<div class="question-box">
    <h2>Team <?= htmlspecialchars($_SESSION['team_index'][$team] ?? "Team $team") ?></h2>
    <h3>Question: <?= htmlspecialchars($question['letter']) ?></h3>
    <p><?= htmlspecialchars($question['question']) ?></p>

    <div class="timer" id="timer">10</div>

    <form method="GET" action="answer.php">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="team" value="<?= $team ?>">
        <input type="hidden" name="points" value="<?= $question['points'] ?>">
        <button type="submit" class="btn">Reveal Answer</button>
    </form>
</div>

<div class="overlay timeout-overlay" id="timeoutOverlay">Time's Up!</div>

<audio id="beep" src="sounds/chime.mp3" preload="auto"></audio>
<audio id="buzzer" src="sounds/buzzer.mp3" preload="auto"></audio>
<audio id="ding" src="sounds/beep.mp3" preload="auto"></audio>

<script>
let timeLeft = 10;
const timerDisplay = document.getElementById('timer');
const beepSound = document.getElementById('beep');
const buzzerSound = document.getElementById('buzzer');
const dingSound = document.getElementById('ding');

const countdown = setInterval(() => {
    timeLeft--;
    timerDisplay.textContent = timeLeft;

    if (timeLeft <= 3 && timeLeft > 0) {
        dingSound.play();
    }

    if (timeLeft === 0) {
        clearInterval(countdown);
        document.getElementById('timeoutOverlay').style.display = 'flex';
        buzzerSound.play();

        // After 2 seconds, auto-submit as wrong (timeout)
        setTimeout(() => {
            window.location.href = 'game-menu.php?id=<?= $id ?>&team=<?= $team ?>&timeout=1';
        }, 2000);
    }
}, 1000);
</script>

</body>
</html>
