<?php
session_start();

// ======================
// 1. GAME STATE VALIDATION
// ======================
if (empty($_SESSION['team_names']) || !isset($_SESSION['current_team'])) {
    // Log error for debugging
    error_log("Skip turn attempted with invalid session: " . print_r($_SESSION, true));
    die("No active game. <a href='setup.php'>Start a new game</a>");
}

// ======================
// 2. LOAD GAME DATA
// ======================
$current_team = (int)$_SESSION['current_team'];
$total_teams = count($_SESSION['team_names']);
$current_round = (int)($_SESSION['round'] ?? 1);

// ======================
// 3. PENALTY CALCULATION
// ======================
$penalty = ($current_round > 1) ? 5 : 0;

// Initialize scores array if missing
if (!isset($_SESSION['scores'])) {
    $_SESSION['scores'] = array_fill(1, $total_teams, 0);
}

// Apply penalty (minimum 0)
$_SESSION['scores'][$current_team] = max(0, ($_SESSION['scores'][$current_team] ?? 0) - $penalty);

// ======================
// 4. TURN ADVANCEMENT  
// ======================
$_SESSION['current_team'] = ($current_team % $total_teams) + 1;
$_SESSION['turns_this_round'] = ($_SESSION['turns_this_round'] ?? 0) + 1;

// ======================
// 5. NOTIFICATION SETUP
// ======================
$team_name = $_SESSION['team_index'][$current_team] ?? "Team $current_team";
$_SESSION['skip_notice'] = $penalty > 0 
    ? "$team_name skipped (-{$penalty} points)" 
    : "Turn skipped";

// ======================
// 6. REDIRECT
// ======================
header("Location: game-menu.php");
exit;
?>