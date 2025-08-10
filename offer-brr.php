<?php
session_start();

$id = (int)($_GET['id'] ?? 0);
$team = (int)($_GET['team'] ?? 1);
$points = (int)($_GET['points'] ?? 0);
$current_round = $_SESSION['round'] ?? 1;

$allowed = in_array($current_round, [2, 3]);
$already = $_SESSION['brr'][$team]['active'] ?? false;
$multiplier = $allowed ? 2 : 1; // BRR multiplier is always ×2 in rounds 2 & 3

if (!$allowed || $already) {
    header("Location: game-menu.php");
    exit;
}

// Calculate potential earnings
$round_multiplier = 1;
if ($current_round == 2) $round_multiplier = 2;
if ($current_round == 3) $round_multiplier = 3;

$potential_multiplier = $round_multiplier * $multiplier;
$example_points = $points * $potential_multiplier;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['decision'] === 'accept') {
        $_SESSION['brr'][$team] = ['active' => true, 'questions_left' => 2, 'multiplier' => $multiplier];
        echo "
            <audio id='brrSound' src='sounds/brr-activate.mp3' autoplay></audio>
            <div style='font-family:Arial;text-align:center;font-size:28px;color:#f44336;
                margin-top:50px; animation:pulse 1.5s infinite;'>⚠️ Bonus Risk Round Activated ⚠️</div>
            <style>@keyframes pulse{0%,100%{opacity:1;}50%{opacity:0.3;}}</style>
            <script>setTimeout(() => location='game-menu.php', 2000);</script>
        ";
        exit;
    } else {
        header("Location: game-menu.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Enter Bonus Risk Round?</title>
<style>
    body { 
        font-family: Arial; 
        text-align: center; 
        padding: 60px; 
        background: #f9f9f9; 
        max-width: 800px;
        margin: 0 auto;
    }
    h1 { font-size: 32px; margin-bottom: 30px; }
    .card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    .multiplier-display {
        font-size: 48px;
        font-weight: bold;
        color: #4CAF50;
        margin: 20px 0;
    }
    .points-example {
        font-size: 24px;
        margin: 15px 0;
        color: #333;
    }
    .btn {
        padding: 15px 35px; 
        font-size: 20px; 
        margin: 15px;
        border: none; 
        border-radius: 6px; 
        cursor: pointer; 
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 10px rgba(0,0,0,0.2);
    }
    .yes { 
        background: linear-gradient(to bottom, #4CAF50, #388E3C); 
    }
    .no { 
        background: linear-gradient(to bottom, #f44336, #d32f2f); 
    }
    .risk-info {
        background: #fff8e1;
        border-left: 5px solid #ffa000;
        padding: 15px;
        margin: 20px 0;
        text-align: left;
        border-radius: 0 8px 8px 0;
    }
</style>
</head>
<body>

<div class="card">
    <h1>Team <?php echo $team; ?> — Enter Bonus Risk Round?</h1>
    
    <div class="multiplier-display">
        Earn <?php echo $potential_multiplier; ?>× Points!
    </div>
    
    <div class="points-example">
        Example: <?php echo $points; ?> point questions become <strong><?php echo $example_points; ?> points</strong>
    </div>
    
    <p>You correctly answered a <?php echo $points; ?> point question.</p>
    <p>Activate Bonus Risk Round for your next <strong>2</strong> questions to earn:</p>
    <ul style="text-align: left; font-size: 20px; margin: 20px 0;">
        <li><strong><?php echo $round_multiplier; ?>× round multiplier</strong></li>
        <li><strong>2× BRR multiplier</strong></li>
        <li><strong>Total: <?php echo $potential_multiplier; ?>× points!</strong></li>
    </ul>
    
    <div class="risk-info">
        <strong>⚠️ Risk Warning:</strong> 
        Wrong answers during BRR will deduct <strong>2×</strong> the question's value points. 
        BRR lasts for exactly 2 questions regardless of outcome.
    </div>
    
    <form method="post">
        <button type="submit" name="decision" value="accept" class="btn yes">
            ✅ Yes - Activate BRR (Earn <?php echo $potential_multiplier; ?>×)
        </button>
        <button type="submit" name="decision" value="decline" class="btn no">
            ❌ No - Decline BRR
        </button>
    </form>
</div>

</body>
</html>