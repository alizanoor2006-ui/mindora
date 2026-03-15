<?php
session_start();
require_once 'config.php';

// Guard: students only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch real entries with AI insights
$stmt = $conn->prepare("SELECT content, sentiment_score, sentiment_label, expression_type, ai_insight, date FROM journals WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$entries = [];
while ($row = $result->fetch_assoc()) {
    $entries[] = $row;
}

// Warm AI Insights Library
function getWarmInsight($label) {
    switch ($label) {
        case 'Positive': return "Your entry radiates positivity! It's beautiful to see you embracing good feelings. Keep nurturing this energy. ✨";
        case 'Neutral': return "A balanced perspective is a sign of great self-awareness. You're doing well in processing your thoughts. 🌿";
        case 'Negative': return "It sounds like you're going through a tough time. Remember, it's okay to feel this way. You're strong for expressing these feelings. 🧡";
        default: return "Thank you for sharing your thoughts. Reflection is a powerful tool for growth. 🧘‍♂️";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindora | Journaling</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #e8f5e9, #ede7f6, #e3f2fd); min-height: 100vh; }
        .container { padding: 2rem; max-width: 860px; margin: 0 auto; }
        .page-head { margin-bottom: 1.5rem; }
        .page-head h1 { font-size: 1.8rem; color: #4a4a8a; }
        .page-head p  { color: #888; margin-top: 0.3rem; }
        .card {
            background: rgba(255,255,255,0.7); backdrop-filter: blur(16px);
            border-radius: 20px; padding: 1.5rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08); margin-bottom: 1.5rem;
        }
        textarea {
            width: 100%; border: 2px solid #e0d7f5; border-radius: 14px;
            padding: 1rem; font-size: 1rem; font-family: inherit;
            resize: vertical; outline: none; background: rgba(255,255,255,0.8);
            line-height: 1.7; transition: border 0.2s;
        }
        textarea:focus { border-color: #b39ddb; }
        .action-row { display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; flex-wrap: wrap; gap: 1rem; }
        .action-row small { color: #aaa; font-size: 0.85rem; }
        .btn {
            display: inline-block; padding: 0.6rem 1.4rem;
            border-radius: 50px; font-size: 0.95rem; font-weight: 700;
            cursor: pointer; border: none; text-decoration: none; transition: transform 0.2s;
        }
        .btn-primary   { background: linear-gradient(135deg,#a8d5a2,#b39ddb); color: white; }
        .btn-secondary { background: rgba(255,255,255,0.8); color: #7c6ab5; border: 2px solid #e0d7f5; }
        .btn:hover { transform: translateY(-2px); }
        
        /* Toggle Styles */
        .toggle-group { display: flex; background: rgba(224,215,245,0.3); border-radius: 12px; padding: 4px; gap: 4px; }
        .toggle-option {
            padding: 8px 16px; border-radius: 10px; cursor: pointer; font-size: 0.85rem; font-weight: 600;
            color: #7c6ab5; transition: 0.3s;
        }
        .toggle-option.active { background: white; color: #4a4a8a; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .section-header h2 { color: #4a4a8a; }
        .add-btn {
            width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg,#a8d5a2,#b39ddb);
            color: white; font-size: 1.4rem; font-weight: bold; border: none;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
        }
        .entry { position: relative; }
        .entry-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem; }
        .sentiment-badge {
            padding: 4px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: 700;
        }
        .expression-badge {
            font-size: 0.75rem; color: #9a8c98; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .entry-text { color: #555; font-size: 0.95rem; line-height: 1.7; }
        .ai-insight-box {
            background: rgba(179,157,219,0.1); border-left: 4px solid #b39ddb;
            padding: 10px 15px; border-radius: 0 12px 12px 0; margin-top: 1rem;
            font-size: 0.9rem; font-style: italic; color: #5e4b8b;
        }
        .del-btn { background: none; border: none; cursor: pointer; font-size: 1.1rem; color: #e57373; }
        #ai-feedback { color: #7c6ab5; font-weight: 600; font-size: 0.95rem; margin-top: 0.75rem; min-height: 1.2rem; }
    </style>
</head>
<body>
<?php require_once 'nav.php'; ?>

<div class="container">
    <div class="page-head">
        <a href="student_dashboard.php" style="text-decoration:none; color:#4a4a8a; font-weight:600; display:inline-block; margin-bottom:1rem; transition:0.2s;" onmouseover="this.style.color='#b39ddb'; this.style.transform='translateX(-5px)'" onmouseout="this.style.color='#4a4a8a'; this.style.transform='translateX(0)'">← Back to Home</a>
        <h1>Your Private Space 📔</h1>
        <p>Write down your thoughts. Our AI will analyze your energy and offer support.</p>
    </div>

    <!-- Write Entry -->
    <div class="card">
        <textarea id="journalInput" rows="7" placeholder="What's on your mind today? Write as much as you need... 🌸"></textarea>
        
        <div class="action-row" style="margin-top:1.5rem;">
            <div style="display:flex; flex-direction:column; gap:8px;">
                <span style="font-size:0.85rem; font-weight:600; color:#4a4a8a;">Expression Type:</span>
                <div class="toggle-group" id="expressionToggle">
                    <div class="toggle-option active" onclick="setExpression('unexpressed', this)">Unexpressed (Private)</div>
                    <div class="toggle-option" onclick="setExpression('expressed', this)">Expressed (Outward)</div>
                </div>
            </div>
            <button class="btn btn-primary" onclick="saveJournal()" style="padding:0.8rem 2rem;">Save &amp; Analyze ✨</button>
        </div>
        
        <div id="ai-feedback" style="margin-top:1.5rem; text-align:center; padding:10px; background:rgba(255,255,255,0.5); border-radius:12px; display:none;"></div>
    </div>

    <!-- Entries -->
    <div class="section-header">
        <h2>Recent Entries</h2>
        <button class="add-btn" onclick="document.getElementById('journalInput').focus()" title="New Entry">+</button>
    </div>

    <div id="entriesList">
        <?php if (empty($entries)): ?>
            <div id="noEntries" style="text-align:center; padding:2rem; color:#888;">No entries yet. Start writing! 🌸</div>
        <?php else: ?>
            <?php foreach ($entries as $index => $entry): ?>
                <div class="card entry">
                    <div class="entry-meta">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="color:#aaa; font-size:0.85rem;"><?= date('M d, Y h:i A', strtotime($entry['date'])) ?></span>
                            <span class="expression-badge">#<?= $entry['expression_type'] ?></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <?php 
                                $label = $entry['sentiment_label'];
                                $score = $entry['sentiment_score'] >= 0 ? '+' . number_format($entry['sentiment_score'], 2) : number_format($entry['sentiment_score'], 2);
                                $colors = ['Positive' => '#66bb6a', 'Neutral' => '#64b5f6', 'Negative' => '#e57373'];
                                $bgMap  = ['Positive' => 'rgba(186,242,187,0.4)', 'Neutral' => 'rgba(186,225,255,0.4)', 'Negative' => 'rgba(255,179,186,0.4)'];
                                $color = $colors[$label] ?? '#64b5f6';
                                $bg = $bgMap[$label] ?? 'rgba(186,225,255,0.4)';
                            ?>
                            <span class="sentiment-badge" style="background:<?= $bg ?>; color:<?= $color ?>;"><?= $label ?></span>
                        </div>
                    </div>
                    <p class="entry-text"><?= nl2br(htmlspecialchars($entry['content'])) ?></p>
                    <div class="ai-insight-box">
                        <strong>AI Insight:</strong> <?= htmlspecialchars($entry['ai_insight'] ?: getWarmInsight($label)) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<script>
let currentExpression = 'unexpressed';
function setExpression(type, el) {
    currentExpression = type;
    document.querySelectorAll('.toggle-option').forEach(opt => opt.classList.remove('active'));
    el.classList.add('active');
}

const warmInsights = {
    'Positive': "Your entry radiates positivity! It's beautiful to see you embracing good feelings. Keep nurturing this energy. ✨",
    'Neutral': "A balanced perspective is a sign of great self-awareness. You're doing well in processing your thoughts. 🌿",
    'Negative': "It sounds like you're going through a tough time. Remember, it's okay to feel this way. You're strong for expressing these feelings. 🧡"
};

async function saveJournal() {
    const text = document.getElementById('journalInput').value.trim();
    if (!text) { alert('Please write something first.'); return; }

    const feedback = document.getElementById('ai-feedback');
    feedback.innerText = '🔄 Analyzing your energy...';
    feedback.style.display = 'block';

    try {
        // 1. Get sentiment from Python AI
        const res  = await fetch('http://localhost:5000/api/sentiment', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ text })
        });
        const aiData = await res.json();

        // 2. Save to MySQL with Groq suggestion
        const formData = new FormData();
        formData.append('action', 'save_journal');
        formData.append('content', text);
        formData.append('sentiment_score', aiData.score);
        formData.append('sentiment_label', aiData.label);
        formData.append('ai_insight', aiData.suggestion); // Groq reflection
        formData.append('expression_type', currentExpression);

        const saveRes = await fetch('api_handler.php', { method: 'POST', body: formData });
        const saveData = await saveRes.json();

        if (saveData.status === 'success') {
            feedback.innerHTML = `<div style="color:#4a4a8a; font-weight:700;">${aiData.label} Entry</div><p style="font-size:0.9rem; margin-top:5px;">${aiData.suggestion}</p>`;
            setTimeout(() => location.reload(), 3000);
        } else {
            feedback.innerText = '⚠️ Error: ' + saveData.message;
        }
    } catch (err) {
        feedback.innerText = '⚠️ Analysis server unreachable.';
    }
}
</script>
</body>
</html>
