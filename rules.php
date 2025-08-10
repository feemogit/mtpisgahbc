<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Alphabet Bible Challenge - Game Rules</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }
        .rules-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        h2 {
            color: #3498db;
            margin-top: 25px;
            border-bottom: 2px solid #eee;
            padding-bottom: 8px;
        }
        .rule-section {
            margin-bottom: 20px;
        }
        .rule-item {
            margin-bottom: 15px;
            padding-left: 20px;
            position: relative;
        }
        .rule-item:before {
            content: "•";
            color: #e74c3c;
            font-size: 20px;
            position: absolute;
            left: 0;
            top: -2px;
        }
        .btn {
            display: block;
            width: 200px;
            margin: 30px auto 0;
            padding: 12px;
            background: #3498db;
            color: white;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2980b9;
        }
        .marker-example {
            display: inline-block;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            text-align: center;
            line-height: 25px;
            margin: 0 5px;
            font-size: 14px;
        }
        .hb-example {
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            color: white;
        }
        .dc-example {
            background: linear-gradient(135deg, #f44336, #c62828);
            color: white;
        }
        .mystery-marker {
            background: linear-gradient(135deg, #FFD700, #FF8C00);
            color: #333;
            font-weight: bold;
        }
        ul {
            margin-top: 5px;
            padding-left: 25px;
        }
        li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="rules-container">
        <h1>Alphabet Bible Challenge Rules</h1>
        
        <div class="rule-section">
            <h2>Game Overview</h2>
            <div class="rule-item">2+ teams compete through 3 rounds of Bible questions</div>
            <div class="rule-item">Questions are tied to alphabet letters with varying point values</div>
            <div class="rule-item">Highest score after Round 3 wins the game</div>
            <div class="rule-item">10-second time limit per question</div>
        </div>
        
        <div class="rule-section">
            <h2>Round Structure</h2>
            <div class="rule-item">
                <strong>Round 1:</strong> 7 questions per team (1× points)
                <ul>
                    <li>Basic Bible knowledge</li>
                    <li>Points range: 1-5 per question</li>
                </ul>
            </div>
            <div class="rule-item">
                <strong>Round 2:</strong> 7 questions per team (2× points)
                <ul>
                    <li>Intermediate difficulty</li>
                    <li>Points range: 3-7 per question</li>
                </ul>
            </div>
            <div class="rule-item">
                <strong>Round 3:</strong> 3 questions per team (3× points)
                <ul>
                    <li>Challenging questions</li>
                    <li>Points range: 5-10 per question</li>
                </ul>
            </div>
        </div>
        
        <div class="rule-section">
            <h2>Special Markers</h2>
            <div class="rule-item">
                Mystery Marker <span class="marker-example mystery-marker">?</span>:
                <ul>
                    <li>Appears randomly on questions (max 2 per round)</li>
                    <li>Reveals as either Heavenly Blessing or Devilish Curse when selected</li>
                </ul>
            </div>
            <div class="rule-item">
                <strong>Heavenly Blessing</strong> <span class="marker-example hb-example">HB</span>:
                <ul>
                    <li>Accept challenge: Answer bonus question for 6× points</li>
                    <li>Decline: Earn normal points for correct answer</li>
                    <li>Fail challenge: Lose question's point value</li>
                </ul>
            </div>
            <div class="rule-item">
                <strong>Devilish Curse</strong> <span class="marker-example dc-example">DC</span>:
                <ul>
                    <li>Accept challenge: Answer bonus question to break curse (earn 2× points)</li>
                    <li>Decline: Normal points but with 1× penalty if wrong</li>
                    <li>Fail challenge: Lose 2× question's point value</li>
                </ul>
            </div>
        </div>
        
        <div class="rule-section">
            <h2>Scoring</h2>
            <div class="rule-item">Correct answer: Full points × round multiplier</div>
            <div class="rule-item">Wrong answer: 0 points</div>
            <div class="rule-item">Timeout: 0 points (buzzer sound)</div>
            <div class="rule-item">Skip turn: 0 points (5-point penalty in Rounds 2-3)</div>
            <div class="rule-item">Markers can significantly modify scores</div>
        </div>
        
        <div class="rule-section">
            <h2>Game Flow</h2>
            <div class="rule-item">Teams take turns in fixed order</div>
            <div class="rule-item">Each turn presents 2 random questions to choose from</div>
            <div class="rule-item">Progress bar shows round completion</div>
            <div class="rule-item">Game automatically advances rounds</div>
            <div class="rule-item">Final scores displayed on Game Over screen</div>
        </div>
        
        <div class="rule-section">
            <h2>Controls</h2>
            <div class="rule-item">Reset Game: Starts completely over</div>
            <div class="rule-item">Skip Turn: Passes current turn (with penalty)</div>
            <div class="rule-item">Exit Game: Ends current session</div>
        </div>
        
        <a href="game-menu.php" class="btn">Back to Game</a>
    </div>
</body>
</html>