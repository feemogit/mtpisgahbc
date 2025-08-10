<?php
session_start();

$team = (int)($_GET['team'] ?? 1);

// Record the skipped turn
$_SESSION['turns_this_round'] = ($_SESSION['turns_this_round'] ?? 0) + 1;

// Advance to next team
$total_teams = $_SESSION['teams'] ?? 2;
$_SESSION['current_team'] = ($_SESSION['current_team'] ?? 1) % $total_teams + 1;

// Handle BRR penalty if active
$team_brr = $_SESSION['brr'][$team] ?? ['active' => false, 'multiplier' => 1, 'questions_left' => 0];

if ($team_brr['active']) {
    // Apply penalty for skipping during BRR
    $penalty = 100; // Fixed penalty amount
    $_SESSION['scores'][$team] = max(0, ($_SESSION['scores'][$team] ?? 0) - $penalty);
    
    // Decrement BRR questions left
    $_SESSION['brr'][$team]['questions_left']--;
    
    // Deactivate BRR if no questions left
    if ($_SESSION['brr'][$team]['questions_left'] <= 0) {
        $_SESSION['brr'][$team] = ['active' => false, 'multiplier' => 1, 'questions_left' => 0];
    }
}

// Redirect back to game menu
header("Location: game-menu.php");
exit;
?>