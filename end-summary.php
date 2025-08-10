<?php
session_start();

// Ensure required session data exists
$teamNames = $_SESSION['team_index'] ?? [];
$scores = $_SESSION['scores'] ?? [];
$correctCounts = $_SESSION['correct_counts'] ?? [];

// If no teams or scores, redirect to setup
if (empty($teamNames) || empty($scores)) {
    header("Location: setup.php");
    exit;
}

// Prepare team results: team_num => ['name'=>..., 'score'=>..., 'correct'=>...]
$results = [];
foreach ($teamNames as $teamNum => $teamName) {
    $results[$teamNum] = [
        'name' => $teamName,
        'score' => $scores[$teamNum] ?? 0,
        'correct' => $correctCounts[$teamNum] ?? 0,
    ];
}

// Determine highest score
$maxScore = max(array_column($results, 'score'));

// Filter teams with max score for tie-break
$topTeams = array_filter($results, fn($t) => $t['score'] === $maxScore);

// If multiple tied on score, use correct answers tie-breaker
if (count($topTeams) > 1) {
    $maxCorrect = max(array_column($topTeams, 'correct'));
    $winners = array_filter($topTeams, fn($t) => $t['correct'] === $maxCorrect);
} else {
    $winners = $topTeams;
}

// If still multiple winners, consider it a tie among them
$winnerTeamNums = array_keys($winners);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Final Game Summary</title>
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f7f9fc;
    color: #222;
    padding: 60px;
    text-align: center;
  }
  h1 {
    margin-bottom: 36px;
    font-weight: 700;
    font-size: 3rem; /* approx 48px * 1.5 */
  }
  table {
    margin: 0 auto 45px;
    border-collapse: collapse;
    width: 80%;
    max-width: 600px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    font-size: 1.5rem; /* increased from ~1rem */
  }
  th, td {
    padding: 21px 30px; /* 14px and 20px increased by 50% */
    border-bottom: 1px solid #ddd;
  }
  th {
    background-color: #2c3e50;
    color: white;
  }
  tr.winner {
    background-color: #ffd700;
    animation: glow 2s ease-in-out infinite alternate;
    font-weight: bold;
  }
  @keyframes glow {
    from { box-shadow: 0 0 8px 2px #ffec73; }
    to { box-shadow: 0 0 15px 5px #ffd700; }
  }
  .buttons {
    margin-top: 30px;
  }
  .btn {
    background: #3498db;
    color: white;
    border: none;
    padding: 18px 36px; /* 12px 24px *1.5 */
    margin: 0 15px;
    border-radius: 9px; /* 6px * 1.5 */
    cursor: pointer;
    font-size: 1.5rem;
  }
  .btn:hover {
    background: #2980b9;
  }
</style>
</head>
<body>
  <h1>Final Game Summary</h1>
  <table>
    <thead>
      <tr>
        <th>Team</th>
        <th>Final Score</th>
        <th>Correct Answers</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($results as $teamNum => $team): 
        $isWinner = in_array($teamNum, $winnerTeamNums);
        $status = $isWinner ? (count($winnerTeamNums) > 1 ? 'Tie 🎉' : 'Winner 🎉') : '';
      ?>
        <tr class="<?= $isWinner ? 'winner' : '' ?>">
          <td><?= htmlspecialchars($team['name']) ?></td>
          <td><?= $team['score'] ?> pts</td>
          <td><?= $team['correct'] ?></td>
          <td><?= $status ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="buttons">
    <button class="btn" onclick="location.href='setup.php'">Play Again</button>
    <button class="btn" onclick="location.href='rules.php'">View Rules</button>
  </div>
</body>
</html>
