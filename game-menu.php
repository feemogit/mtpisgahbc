<?php
session_start();
require_once __DIR__ . '/config/database.php';
$pdo = getDB(); // Replaces old connection
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_SESSION['question_cache'])) {  
    $_SESSION['question_cache'] = $pdo->query("SELECT * FROM questions")->fetchAll();  
}  

// Load team info
$teamNames = $_SESSION['team_names'] ?? ['Team 1', 'Team 2'];
$total_teams = count($teamNames);
$_SESSION['teams'] = $total_teams;

// Map team indexes to names
$_SESSION['team_index'] = [];
foreach ($teamNames as $i => $name) {
    $_SESSION['team_index'][$i + 1] = $name;
}

// Current round and team
$current_round = $_SESSION['round'] ?? 1;
$current_team = $_SESSION['current_team'] ?? 1;

// Rounds limits
$roundLimits = [1 => 7, 2 => 7, 3 => 3];
$questionsPerTeam = $roundLimits[$current_round] ?? 7;

// Track turns
$turnsCompleted = $_SESSION['turns_this_round'] ?? 0;
$turnsTotal = $questionsPerTeam * $total_teams;

// End of round handling
if ($turnsCompleted >= $turnsTotal) {
    if ($current_round == 3) {
        header("Location: game-over.php");
        exit;
    }

    // Next round setup
    $newRound = $current_round + 1;
    $_SESSION['round'] = $newRound;
    $_SESSION['turns_this_round'] = 0;
    $_SESSION['current_team'] = 1;
    $_SESSION['markers'] = ['HB' => 0, 'DC' => 0]; // Resets counters
    $_SESSION['question_markers'] = [];

    echo "<!DOCTYPE html>
        <html>
        <head><meta http-equiv='refresh' content='3;url=game-menu.php'>
        <title>End of Round</title>
        <style>body{font-family:Arial;text-align:center;padding:100px;} h1{font-size:48px;}</style>
        </head>
        <body>
            <h1>End of Round $current_round</h1>
            <p>Starting Round $newRound...</p>
        </body>
        </html>";
    exit;
}

// Initialize markers count if not set
if (!isset($_SESSION['markers'])) {
    $_SESSION['markers'] = ['HB' => 0, 'DC' => 0];
}

// Retrieve asked questions to exclude
$_SESSION['asked_questions'] = $_SESSION['asked_questions'] ?? [];
$exclude = $_SESSION['asked_questions'];
$placeholders = !empty($exclude) ? implode(',', array_fill(0, count($exclude), '?')) : '';

// Prepare SQL to get 2 random questions not asked yet
$sql = "SELECT id, letter, points FROM questions" .
       (!empty($exclude) ? " WHERE id NOT IN ($placeholders)" : "") .
       " ORDER BY RAND() LIMIT 2";

$stmt = $pdo->prepare($sql);
$stmt->execute($exclude);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$questions) {
    die("No more questions available.");
}

// Choose the first question as the next question
$nextQuestion = $questions[0];

// Determine if a marker should appear for this question (2 allowed per type per round)
$canUseHB = $_SESSION['markers']['HB'] < 2;
$canUseDC = $_SESSION['markers']['DC'] < 2;

$useMarker = false;
$markerType = null;

if ($canUseHB || $canUseDC) {
    // Choose which question gets the marker (0 or 1 index)
    $markedQuestionIndex = rand(0, 1);
    
    if ($canUseHB && $canUseDC) {
        $markerType = (rand(0, 1) === 0) ? 'HB' : 'DC';
    } elseif ($canUseHB) {
        $markerType = 'HB';
    } else {
        $markerType = 'DC';
    }
    
    // Assign marker to the selected question
    $_SESSION['markers'][$markerType]++;
    $_SESSION['question_markers'][$questions[$markedQuestionIndex]['id']] = $markerType;
    
    // Mark that we'll show a generic badge
    $_SESSION['question_badges'][$questions[$markedQuestionIndex]['id']] = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Game Menu - Round <?= $current_round ?> - Team <?= $current_team ?></title>
<style>
    body { 
        font-family: Arial, sans-serif; 
        padding: 40px; 
        max-width: 700px; 
        margin: auto; 
        background: #fafafa; 
    }
    .current-team {
    border: 2px solid #2196F3;
    transform: scale(1.05);
    transition: all 0.3s ease;
}

.team-score:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
    
    .btn { 
        padding: 15px 30px; 
        margin: 10px 0; 
        font-size: 20px; 
        border: none; 
        border-radius: 6px; 
        cursor: pointer; 
        background: #2196F3; 
        color: white; 
        display: block;
        width: 100%;
        text-align: center;
    }
    .question-options {
    display: grid;
    gap: 15px;
    margin: 25px 0;
}
.question-btn {
    position: relative;
    padding: 20px;
    text-align: center;
}
.marker-badge {
    position: absolute;
    top: -10px;
    right: -10px;
    background: #d35400;
    color: white;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}
    .skip-section {
        margin-top: 40px;
        border-top: 2px dashed #ccc;
        padding-top: 20px;
    }
    .btn-skip {
        background: #ff9800;
    }
    .skip-btn { 
    background: #FF9800 !important; 
    color: #000 !important;
    font-size: 20px !important;
}
    .skip-notice {
        animation: pulseWarning 1.5s infinite;
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 12px;
        margin: 20px 0;
        font-size: 18px;
    }
    @keyframes pulseWarning {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    .exit-btn:hover {
    background: #d32f2f;
    transform: scale(1.1);
}
.rules-btn:hover {
    background: #1976D2;
    transform: scale(1.1);
}
.game-controls {
    position: fixed;
    top: 15px;
    left: 15px;
    z-index: 1000;
}

.control-btn {
    display: block;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    color: #333;
    font-size: 24px;
    text-align: center;
    line-height: 50px;
    margin-bottom: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.reset-btn { background: #FFC107; color: #000; }
.rules-btn { background: #2196F3; color: white; }
.exit-btn { 
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #f44336;
    color: white;
}

.control-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}
 .marker-badge {
        position: absolute;
        top: -10px;
        right: -10px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        border: 2px solid white;
    }
    
    .marker-badge::before {
        content: "😇";
        position: absolute;
        left: -5px;
        filter: drop-shadow(2px 2px 1px rgba(0,0,0,0.3));
    }
    
    .marker-badge::after {
        content: "👿";
        position: absolute;
        right: -5px;
        filter: drop-shadow(2px 2px 1px rgba(0,0,0,0.3));
    }
    
    .marker-badge span {
        position: relative;
        z-index: 2;
        font-size: 12px;
        background: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .marker-badge {
        position: absolute;
        top: -12px;
        right: -12px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ffd700 0%, #ff8c00 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        box-shadow: 0 3px 6px rgba(0,0,0,0.16);
        border: 2px solid #fff;
        color: #333;
    }
    
    .marker-badge i {
        position: absolute;
    }
    
    .marker-badge .halo {
        top: -8px;
        left: 6px;
        color: #fff;
        text-shadow: 0 0 3px gold;
        font-size: 16px;
    }
    
    .marker-badge .pitchfork {
        bottom: -6px;
        right: 6px;
        color: #d00;
        font-size: 14px;
        transform: rotate(15deg);
    }
    
    .marker-badge .question-mark {
        font-weight: bold;
        position: relative;
        z-index: 2;
    }
</style>
</head>
<body>
<!-- Add right after <body> -->
<!-- Navigation Icons -->
<div class="game-controls">
    <!-- Reset Icon (Top-Left) -->
    <a href="reset-game.php" class="control-btn reset-btn" title="Reset Game">
        🔄
    </a>
    
    <!-- Rules Icon (Below Reset) -->
    <a href="rules.php" class="control-btn rules-btn" title="Game Rules">
        📖
    </a>
    <!-- Skip Turn Icon (Top-Left) -->
    

    <!-- Exit Icon (Bottom-Right) -->
    <a href="end-game.php" class="control-btn exit-btn" title="Exit Game">
        ✕
    </a>
     <a href="skip-turn.php" class="control-btn skip-btn" title="Skip Turn" 
       onclick="return confirm('Skip this turn?<?= ($current_round>1)?'\n\n⚠️ 5 point penalty!':'' ?>')">⏭</a>
</div>


<h1>Round <?= $current_round ?> - Team <?= htmlspecialchars($_SESSION['team_index'][$current_team]) ?></h1>

<!-- NEW: Scores Display -->
<div class="scoreboard" style="
    display: flex;
    justify-content: center;
    gap: 20px;
    margin: 15px 0;
    flex-wrap: wrap;
">
    <?php foreach ($_SESSION['team_index'] as $teamId => $teamName): ?>
        <div class="team-score <?= $teamId == $current_team ? 'current-team' : '' ?>" style="
            background: <?= $teamId == $current_team ? '#e3f2fd' : '#f5f5f5' ?>;
            padding: 25px 15px; /* Increased vertical padding only */
            border-radius: 8px;
            width: 100px; /* Changed from min-width to fixed width */
            height: 150px; /* Fixed height for exact control */
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: inline-flex; /* Changed to inline-flex */
            flex-direction: column;
            justify-content: center;
            align-items: center;
        ">
            <div style="font-weight: bold; font-size: 2.4rem;"><?= htmlspecialchars($teamName) ?></div>
            <div style="font-size: 72px;"><?= $_SESSION['scores'][$teamId] ?? 0 ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div style="
    margin: 15px auto;
    max-width: 400px;
    background: #f0f0f0;
    border-radius: 10px;
    padding: 10px;
">
    <div style="
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 16px;
    ">
        <span>Progress:</span>
        <span>
            <?= floor($_SESSION['turns_this_round'] / $total_teams) ?> / <?= $questionsPerTeam ?> 
            (Round <?= $current_round ?>)
        </span>
    </div>
    <div style="
        height: 12px;
        background: #e0e0e0;
        border-radius: 6px;
        overflow: hidden;
    ">
        <div style="
            height: 100%;
            width: <?= ($_SESSION['turns_this_round'] / ($questionsPerTeam * $total_teams)) * 100 ?>%;
            background: linear-gradient(to right, #4CAF50, #2E7D32);
            transition: width 0.3s ease;
        "></div>
    </div>
</div>

<?php if (!empty($_SESSION['skip_notice'])): ?>
    <div class="skip-notice">
        <?= htmlspecialchars($_SESSION['skip_notice']) ?>
    </div>
    <?php unset($_SESSION['skip_notice']); ?>
<?php endif; ?>

<!-- Two Question Buttons -->
<div class="question-options">
    <?php foreach ($questions as $question): ?>
        <?php 
        $hasBadge = isset($_SESSION['question_badges'][$question['id']]);
        $hasMarker = isset($_SESSION['question_markers'][$question['id']]);
        ?>
        <form method="GET" action="<?= $hasMarker ? 'marker-overlay.php' : 'question.php' ?>">
            <input type="hidden" name="id" value="<?= $question['id'] ?>">
            <input type="hidden" name="team" value="<?= $current_team ?>">
            <button type="submit" class="btn question-btn">
                <?= htmlspecialchars($question['letter']) ?> 
                (<?= $question['points'] ?> pts)
                <?php if ($hasBadge): ?>
                    <span class="marker-badge">🌟</span> <!-- Generic badge -->
                <?php endif; ?>
            </button>
        </form>
    <?php endforeach; ?>
</div>


<script>
document.querySelectorAll('.control-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        if(!confirm('Are you sure?')) e.preventDefault();
    });
});
</script>

</body>
</html>