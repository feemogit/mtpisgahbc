<?php
session_start();

if (!isset($_SESSION['team_names']) || count($_SESSION['team_names']) < 2) {
    die("Missing team data. <a href='setup.php'>Start again</a>");
}

$names = $_SESSION['team_names'];
shuffle($names); // Randomize team order

$_SESSION['team_names'] = $names;
$_SESSION['teams'] = count($names);
$_SESSION['current_team'] = 1;
$_SESSION['scores'] = array_fill(1, $_SESSION['teams'], 0);
$_SESSION['round'] = 1;
$_SESSION['turns_this_round'] = 0;
$_SESSION['asked_questions'] = [];
$_SESSION['brr'] = [];
$_SESSION['brr_penalties'] = [];
$_SESSION['correct_counts'] = array_fill(1, $_SESSION['teams'], 0);

$firstTeam = $names[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Team Selection</title>
  <style>
    body {
      font-family: sans-serif;
      text-align: center;
      padding-top: 100px;
      background-color: #f0f0f0;
    }
    #teamName {
      font-size: 0;
      opacity: 0;
      transition: all 2s ease-in-out;
      color: #333;
      font-weight: bold;
    }
    #teamName.show {
      font-size: 48pt;
      opacity: 1;
    }
    #beginButton {
      display: none;
      margin-top: 30px;
      padding: 15px 30px;
      font-size: 20px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<h2>Top Scholar – Starting Team</h2>
<div id="teamName"><?php echo htmlspecialchars($firstTeam); ?></div>

<form action="game-menu.php" method="post">
  <button id="beginButton" type="submit">Begin</button>
</form>

<script>
  window.onload = () => {
    const name = document.getElementById("teamName");
    const button = document.getElementById("beginButton");

    setTimeout(() => name.classList.add('show'), 500);
    setTimeout(() => button.style.display = 'inline-block', 2500);
  };
</script>

</body>
</html>
