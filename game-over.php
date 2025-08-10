<?php
session_start();

// Get team scores and correct answers
$teamScores = $_SESSION['scores'] ?? [];
$teamCorrect = $_SESSION['correct_counts'] ?? []; // should be tracked elsewhere
$teamNames  = $_SESSION['team_index'] ?? [];

// Step 1: Find highest score(s)
$maxScore = max($teamScores);
$topScoringTeams = array_keys(array_filter($teamScores, fn($s) => $s === $maxScore));

// Step 2: If tie, break with most correct answers
if (count($topScoringTeams) > 1) {
    $maxCorrect = -1;
    $winnerId = null;
    foreach ($topScoringTeams as $teamId) {
        $correct = $teamCorrect[$teamId] ?? 0;
        if ($correct > $maxCorrect) {
            $maxCorrect = $correct;
            $winnerId = $teamId;
        }
    }
} else {
    $winnerId = $topScoringTeams[0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Game Over</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      text-align: center;
      padding: 60px;
    }
    h1 {
      font-size: 48px;
      margin-bottom: 20px;
    }
    .scoreboard {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 30px;
      margin: 40px 0;
    }
    .team-box {
      padding: 20px;
      background: #e0e0e0;
      border-radius: 12px;
      width: 200px;
      font-size: 22px;
      font-weight: bold;
      position: relative;
    }
    .winner {
      animation: flash 1s infinite alternate;
      border: 4px solid #4CAF50;
      background: #C8E6C9;
    }
    @keyframes flash {
      0% { box-shadow: 0 0 10px 4px #4CAF50; }
      100% { box-shadow: 0 0 20px 8px #81C784; }
    }
    .btn {
      margin-top: 40px;
      padding: 16px 32px;
      font-size: 20px;
      background: #2196F3;
      color: white;
      border: none;
      border-radius: 8px;
      text-decoration: none;
      cursor: pointer;
    }
    .btn:hover {
      background: #1976D2;
    }
  </style>
</head>
<body>

  <h1>🎉 Game Over! 🎉</h1>
  <p>Thanks for playing!</p>

  <div class="scoreboard">
    <?php foreach ($teamScores as $id => $score): ?>
      <div class="team-box <?= $id === $winnerId ? 'winner' : '' ?>">
        <?= htmlspecialchars($teamNames[$id] ?? 'Team') ?><br>
        <?= $score ?> pts
      </div>
    <?php endforeach; ?>
  </div>

  <form method="POST" action="end-summary.php">
    <button type="submit" class="btn">View Summary</button>
  </form>

</body>
</html>
