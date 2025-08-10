<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['team_names'])) {
    $_SESSION['team_names'] = array_map('trim', $_POST['team_names']); // clean input
    header("Location: team-selection.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Alphabet Bible Challenge Setup</title>
  <style>
    body { font-family: Arial, sans-serif; text-align: center; padding: 60px; }
    h1 { font-size: 3em; }
    .btn {
      padding: 15px 30px;
      font-size: 20px;
      margin: 20px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }
    .btn:hover { background-color: #388E3C; }
    .team-inputs { margin-top: 20px; }
    input[type="text"] {
      font-size: 16px;
      padding: 8px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
  </style>
</head>
<body>
  <h1>Alphabet Bible Challenge</h1>
  <p>Select number of teams:</p>
  <form id="teamCountForm">
    <button type="button" class="btn" onclick="setTeamCount(2)">2 Teams</button>
    <button type="button" class="btn" onclick="setTeamCount(3)">3 Teams</button>
  </form>

  <form id="nameForm" method="POST" action="" style="display:none;" class="team-inputs">
    <div id="teamInputs"></div>
    <button type="submit" class="btn">Find Top Scholar</button>
  </form>

  <script>
    function setTeamCount(count) {
      const inputDiv = document.getElementById('teamInputs');
      const form = document.getElementById('nameForm');
      inputDiv.innerHTML = '';
      for (let i = 1; i <= count; i++) {
        inputDiv.innerHTML += `
          <label>Team ${i} Name: 
            <input type="text" name="team_names[]" maxlength="25" required>
          </label><br><br>`;
      }
      form.style.display = 'block';
    }
  </script>
</body>
</html>
