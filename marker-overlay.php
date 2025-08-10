<?php
session_start();

$id = (int)($_GET['id'] ?? 0);
$team = (int)($_GET['team'] ?? 1);
$points = (int)($_GET['points'] ?? 0);

// Retrieve assigned marker type from session
$markerType = $_SESSION['question_markers'][$id] ?? null;

if (!$markerType || !in_array($markerType, ['HB', 'DC'])) {
    // Fallback assignment, should rarely occur
    $markerType = (rand(0, 1) === 0) ? 'HB' : 'DC';
    $_SESSION['question_markers'][$id] = $markerType;
}

// ✅ Mark question so overlay doesn’t repeat
$_SESSION['marker_revealed_questions'][$id] = true;

// 🧠 Store active marker info for reference
$_SESSION['active_marker'] = [
    'type' => $markerType,
    'question_id' => $id,
    'points' => $points,
    'team' => $team
];

// Customize title and description
$titles = [
    'HB' => 'Receive Double Blessing?',
    'DC' => 'Break The Curse?'
];

$descriptions = [
    'HB' => "A Heavenly Blessing appears! Accept the challenge and answer a bonus question to earn 6× points — or ignore and earn 3× for a correct answer.",
    'DC' => "The Devilish Curse is here! Answer a bonus question to break the curse and earn 2× points — or ignore and face the consequences of the curse."
];

$images = [
    'HB' => 'sounds/angel.png',
    'DC' => 'sounds/devil.png'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $markerType === 'HB' ? 'Heavenly Blessing' : 'Devilish Curse' ?></title>
<style>
    body {
        margin: 0;
        padding: 0;
        background: rgba(0,0,0,0.92);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        font-family: 'Arial', sans-serif;
        color: white;
        text-align: center;
    }

    .overlay-content {
        background: <?= $markerType === 'HB' ? 'radial-gradient(circle, #a5d6a7, #4caf50)' : 'radial-gradient(circle, #ef9a9a, #d32f2f)'; ?>;
        padding: 40px;
        border-radius: 20px;
        max-width: 80%;
        box-shadow: 0 0 30px rgba(0,0,0,0.6);
        border: 4px solid #fff;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.03); }
        100% { transform: scale(1); }
    }

    h1 {
        font-size: 44px;
        margin-bottom: 20px;
        text-shadow: 2px 2px 5px rgba(0,0,0,0.5);
    }

    p {
        font-size: 22px;
        line-height: 1.5;
        margin-bottom: 30px;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }

    img {
        max-width: 200px;
        margin: 20px auto;
        display: block;
        filter: drop-shadow(5px 5px 10px rgba(0,0,0,0.5));
    }

    .btn-group {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        gap: 40px;
    }

    .btn {
        padding: 16px 36px;
        font-size: 20px;
        font-weight: bold;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        transition: transform 0.2s ease;
    }

    .btn:hover {
        transform: scale(1.05);
    }

    .accept {
        background-color: <?= $markerType === 'HB' ? '#ffffff' : '#000000' ?>;
        color: <?= $markerType === 'HB' ? '#4caf50' : '#d32f2f' ?>;
    }

    .ignore {
        background-color: #cccccc;
        color: #333;
    }
</style>
</head>
<body>

<div class="overlay-content">
    <h1><?= htmlspecialchars($titles[$markerType]) ?></h1>
    <img src="<?= $images[$markerType] ?>" alt="<?= $markerType === 'HB' ? 'Angel' : 'Devil' ?>">
    <p><?= htmlspecialchars($descriptions[$markerType]) ?></p>

    <div class="btn-group">
        <!-- Accept button goes to marker-question.php -->
        <form method="GET" action="marker-question.php">
    <input type="hidden" name="team" value="<?= $team ?>">
    <input type="hidden" name="marker" value="<?= $markerType ?>">
    <input type="hidden" name="main_id" value="<?= $id ?>">
    <!-- Add this line to pass a random marker question ID -->
    <input type="hidden" name="marker_id" value="<?= rand(1, 20) ?>"> <!-- Adjust range as needed -->
    <button class="btn accept" type="submit">Accept</button>
</form>

        <!-- Ignore button goes to normal question -->
        <form method="GET" action="question.php">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="team" value="<?= $team ?>">
            <input type="hidden" name="points" value="<?= $points ?>">
            <input type="hidden" name="marker_ignored" value="1">
            <button class="btn ignore" type="submit">Ignore</button>
        </form>
    </div>
</div>

</body>
</html>
