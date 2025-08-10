
<?php
session_start();
require_once __DIR__ . '/config/database.php';
$pdo = getDB();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Debug: Log incoming GET parameters
error_log("Marker Question GET: " . print_r($_GET, true));

// Validate all required parameters exist
if (!isset($_GET['team'], $_GET['marker'], $_GET['main_id'], $_GET['marker_id'])) {
    die("Missing required parameters");
}

// Sanitize inputs
$team = (int)$_GET['team'];
$markerType = $_GET['marker'];
$mainQuestionId = (int)$_GET['main_id'];
$markerId = (int)$_GET['marker_id'];

// Validate marker type
if (!in_array($markerType, ['HB', 'DC'])) {
    die("Invalid marker type");
}

// Validate IDs
if ($team <= 0 || $mainQuestionId <= 0 || $markerId <= 0) {
    die("Invalid IDs provided");
}

// Get marker question from database
try {
    $stmt = $pdo->prepare("SELECT * FROM markers WHERE id = ?");
    $stmt->execute([$markerId]);
    $markerQuestion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$markerQuestion) {
        die("Marker question not found in database");
    }

    // Store pending marker data
    $_SESSION['marker_pending'] = [
        'type' => $markerType,
        'team' => $team,
        'main_question_id' => $mainQuestionId,
        'marker_question_id' => $markerId,
        'question' => $markerQuestion['question'],
        'answer' => $markerQuestion['answer']
    ];

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $markerType === 'HB' ? 'Heavenly Blessing' : 'Devilish Curse' ?> Challenge</title>
<style>
    body { 
        font-family: Arial, sans-serif; 
        padding: 40px; 
        max-width: 700px; 
        margin: auto; 
        background: #f9f9f9; 
        text-align: center; 
    }
    .question-box { 
        background: #fff; 
        padding: 30px; 
        border-radius: 10px; 
        box-shadow: 0 0 10px rgba(0,0,0,0.1); 
    }
    .timer { 
        font-size: 36px; 
        margin-top: 20px; 
        color: #333; 
    }
    .btn { 
        margin-top: 30px; 
        padding: 15px 30px; 
        font-size: 20px; 
        background: #007BFF; 
        color: white; 
        border: none; 
        border-radius: 6px; 
        cursor: pointer; 
    }
</style>
</head>
<body>

<div class="question-box">
    <h2><?= $markerType === 'HB' ? '🌟 Heavenly Blessing' : '👹 Devilish Curse' ?> Challenge</h2>
    <h3>Team <?= htmlspecialchars($_SESSION['team_index'][$team] ?? "Team $team") ?></h3>
    <p><?= htmlspecialchars($markerQuestion['question']) ?></p>

    <div class="timer" id="timer">10</div>

    <form method="post" action="marker-result.php">
        <input type="hidden" name="from_question" value="1">
        <button type="submit" class="btn">Reveal Answer</button>
    </form>
</div>

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
        buzzerSound.play();
        document.querySelector('form').submit();
    }
}, 1000);
</script>

</body>
</html>