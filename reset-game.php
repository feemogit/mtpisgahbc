<?php
session_start();

// Preserve only team names and round
$team_names = $_SESSION['team_names'] ?? [];
$current_round = $_SESSION['round'] ?? 1;

// Reset all game progress
$_SESSION['asked_questions'] = [];
$_SESSION['recent_letters'] = [];
$_SESSION['turns_this_round'] = 0;
$_SESSION['current_team'] = 1;
$_SESSION['scores'] = array_fill(1, count($team_names), 0);
unset($_SESSION['team_index']); // Cleanup deprecated var

// Preserve original marker state
if (!isset($_SESSION['markers'])) {
    $_SESSION['markers'] = ['HB' => 0, 'DC' => 0];
}

// Preserve round
$_SESSION['round'] = $current_round;

header("Location: game-menu.php");
exit;