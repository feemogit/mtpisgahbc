<?php
session_start();

$team = (int)($_POST['team'] ?? 1);
$choice = $_POST['brr_choice'] ?? 'no';
$multiplier = (int)($_POST['multiplier'] ?? 1);

if ($choice === 'yes') {
    $_SESSION['brr'][$team] = [
        'active' => true,
        'questions_left' => 2,
        'multiplier' => $multiplier,
    ];
} else {
    $_SESSION['brr'][$team] = [
        'active' => false,
        'questions_left' => 0,
        'multiplier' => 1,
    ];
}

header("Location: game-menu.php");
exit;