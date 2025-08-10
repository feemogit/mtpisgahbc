<?php
session_start();
require_once __DIR__ . '/config/database.php';
$pdo = getDB();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if we have pending marker data
if (!isset($_SESSION['marker_pending'])) {
    header("Location: game-menu.php");
    exit;
}

$pending = $_SESSION['marker_pending'];

// First visit - show answer screen
if (isset($_POST['from_question'])) {
    // Show answer screen
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <title>Judge Marker Answer</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 40px; 
            max-width: 700px; 
            margin: auto; 
            background: #f9f9f9; 
            text-align: center; 
        }
        .answer-box { 
            background: #fff; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
            margin: 20px 0;
        }
        .btn { 
            padding: 15px 30px; 
            font-size: 20px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            margin: 10px;
        }
        .correct { background: #4CAF50; color: white; }
        .wrong { background: #f44336; color: white; }
    </style>
    </head>
    <body>

    <div class="answer-box">
        <h2><?= $pending['type'] === 'HB' ? '🌟 Heavenly Blessing' : '👹 Devilish Curse' ?> Answer</h2>
        <h3>Team <?= htmlspecialchars($_SESSION['team_index'][$pending['team']] ?? "Team {$pending['team']}") ?></h3>
        
        <p><strong>Question:</strong><br><?= htmlspecialchars($pending['question']) ?></p>
        <p><strong>Correct Answer:</strong><br><?= htmlspecialchars($pending['answer']) ?></p>
    </div>

    <form method="post">
        <button type="submit" name="result" value="correct" class="btn correct">Correct</button>
        <button type="submit" name="result" value="wrong" class="btn wrong">Wrong</button>
    </form>

    </body>
    </html>
    <?php
    exit;
}

// Handle judgment submission
if (isset($_POST['result'])) {
    $isCorrect = ($_POST['result'] === 'correct');
    
    // Store results
    $_SESSION['marker_results'][$pending['team']][$pending['main_question_id']] = [
        'type' => $pending['type'],
        'correct' => $isCorrect,
        'accepted' => true,
        'question_id' => $pending['main_question_id']
    ];
    
    // Clean up
    unset($_SESSION['marker_pending']);
    
    // Redirect back to main question
    header("Location: question.php?id={$pending['main_question_id']}&team={$pending['team']}");
    exit;
}

// Fallback redirect if something goes wrong
header("Location: game-menu.php");
exit;
?>