<?php
session_start();

// DB connection
$pdo = new PDO('mysql:host=localhost;dbname=mtpicaxm_bible-game', 'mtpicaxm_mtpisgahmedia', 'lordJesus2021!!');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Input validation with error logging
if (!isset($_POST['team'], $_POST['marker_type'], $_POST['marker_question_id'], $_POST['main_question_id'], $_POST['answer'])) {
    error_log("Missing POST data: " . print_r($_POST, true));
    die("Invalid marker answer data. Missing required fields.");
}

// Store the pending marker info for the answer screen
$_SESSION['marker_pending']['correct_answer'] = $marker['answer'];
    

// Validate marker type
if (!in_array($markerType, ['HB', 'DC'])) {
    error_log("Invalid marker type: $markerType");
    die("Invalid marker type.");
}

// Validate IDs
if ($markerQuestionId <= 0 || $mainQuestionId <= 0) {
    error_log("Invalid IDs: marker=$markerQuestionId, main=$mainQuestionId");
    die("Invalid question IDs.");
}

// Get the correct answer from database
$stmt = $pdo->prepare("SELECT answer FROM markers WHERE id = ?");
$stmt->execute([$_POST['marker_question_id']]);
$marker = $stmt->fetch();

    if (!$marker) {
        error_log("Marker question not found: ID $markerQuestionId");
        die("Marker question not found.");
    }

    $correctAnswer = strtolower(trim($marker['answer']));
    $isCorrect = ($givenAnswer === $correctAnswer);

    // Store marker result
    $_SESSION['marker_results'][$team][$mainQuestionId] = [
        'type' => $markerType,
        'correct' => $isCorrect,
        'accepted' => true,
        'question_id' => $mainQuestionId
    ];

    // Redirect to main question
    header("Location: marker-answer.php");
exit;
?>

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("A database error occurred.");
}
?>