<?php
session_start();
require_once 'config.php';

// Guard: students only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Phrases
$phrases = [
    "Today is a lovely day to be happy 💛",
    "Breathe in calm, breathe out stress 🌿",
    "Small steps every day lead to big changes 🌸",
    "You are capable of amazing things ✨",
    "Be gentle with yourself today 🍃",
    "One breath at a time, you've got this 🌼"
];
$phrase_of_the_day = $phrases[array_rand($phrases)];

// Fetch last 7 days of screen/sleep data
$dates = [];
$screen_times = [];
$sleep_times = [];

// Get labels for last 7 days
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('D', strtotime("-$i days"));
}

// Initialize arrays with 0
$chart_data = [];
foreach ($dates as $d) {
    $chart_data[$d] = ['screen' => 0, 'sleep' => 0];
}

// Fetch from DB
$stmt = $conn->prepare("SELECT date, screen_time, sleep_time FROM screen_sleep_data WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) ORDER BY date ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $day = date('D', strtotime($row['date']));
    if (isset($chart_data[$day])) {
        $chart_data[$day]['screen'] = floatval($row['screen_time']);
        $chart_data[$day]['sleep'] = floatval($row['sleep_time']);
    }
}

$screen_times = array_column($chart_data, 'screen');
$sleep_times = array_column($chart_data, 'sleep');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindora | Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #ede7f6, #e3f2fd);
            min-height: 100vh;
        }

        /* ── NAV ── */
        nav {
            background: rgba(255,255,255,0.75);
            backdrop-filter: blur(16px);
            padding: 1rem 2rem;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 16px rgba(0,0,0,0.07);
            position: sticky; top: 0; z-index: 100;
        }

        /* ── CONTENT ── */
        .container { padding: 2rem; max-width: 1200px; margin: 0 auto; }
        .welcome-head { margin-bottom: 2rem; }
        .welcome-head h1 { font-size: 2.2rem; color: #4a4a8a; }
        .welcome-head p { color: #888; font-style: italic; font-size: 1.1rem; margin-top: 0.4rem; }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }

        .card {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(16px);
            border-radius: 24px;
            padding: 1.8rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        .card:hover { transform: translateY(-5px); }
        .card h3 { color: #4a4a8a; margin-bottom: 0.8rem; font-size: 1.3rem; }
        .card p { color: #777; font-size: 0.95rem; margin-bottom: 1.2rem; line-height: 1.5; }

        .slider-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
        input[type=range] { flex: 1; accent-color: #b39ddb; }
        input[type=number] {
            width: 70px; padding: 0.4rem 0.5rem;
            border: 2px solid #e0d7f5; border-radius: 10px;
            text-align: center; font-size: 0.95rem;
        }
        .btn {
            display: inline-block;
            padding: 0.7rem 1.4rem;
            background: linear-gradient(135deg, #a8d5a2, #b39ddb);
            color: white; border: none; border-radius: 50px;
            font-weight: 700; cursor: pointer; transition: all 0.2s;
            text-decoration: none; text-align: center;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(179, 157, 219, 0.3); opacity: 0.9; }

        .full-width { grid-column: 1 / -1; }

        /* Quick Actions Grid (Now at the bottom) */
        .actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-top: 2rem; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 2rem; }
        .section-label { color: #4a4a8a; font-weight: 800; font-size: 1.4rem; margin-bottom: 1rem; }
        
        .action-card {
            background: rgba(255,255,255,0.8);
            padding: 1.5rem; border-radius: 20px;
            display: flex; flex-direction: column; justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            border: 1px solid rgba(255,255,255,0.3);
            text-decoration: none; transition: 0.3s;
        }
        .action-card:hover { background: #fff; box-shadow: 0 8px 25px rgba(0,0,0,0.08); transform: scale(1.02); }
        .action-card .icon { font-size: 2rem; margin-bottom: 1rem; }
        .action-card h4 { color: #4a4a8a; margin-bottom: 0.5rem; font-size: 1.1rem; }
        .action-card p { color: #777; font-size: 0.85rem; line-height: 1.4; margin-bottom: 1rem; }

        /* Animation */
        .ai-box { display: flex; align-items: center; gap: 1rem; }
        .ai-icon { font-size: 2.5rem; animation: float 3s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .ai-text h4 { color: #4a4a8a; margin-bottom: 0.3rem; }
        .ai-text p { color: #777; font-size: 0.9rem; }
    </style>
</head>
<body>

<?php require_once 'nav.php'; ?>

<div class="container">
    <div class="welcome-head">
        <h1>Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>! 👋</h1>
        <p><?= $phrase_of_the_day ?></p>
    </div>

    <div class="grid">
        <!-- Screen Time -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <h3>📱 Screen Usage</h3>
                <span id="syncBadge" style="font-size:0.65rem; background:#e8f5e9; color:#2e7d32; padding:3px 8px; border-radius:10px; font-weight:800; display:none; border:1px solid #c8e6c9;">● LIVE AUTO-SYNC</span>
            </div>
            <p>Spending too much time on your phone? Tracking helps you regain control.</p>
            <div class="slider-row">
                <input type="range" min="1" max="24" value="5" id="screenRange"
                       oninput="document.getElementById('screenInput').value=this.value">
                <input type="number" id="screenInput" value="5" min="1" max="24"
                       oninput="document.getElementById('screenRange').value=this.value">
                <button class="btn" onclick="logData('screen')">Log</button>
            </div>
            <p id="autoTrackLabel" style="font-size:0.75rem; color:#888; margin-top:-0.5rem; display:none;">✨ Automatic tracking active</p>
            <canvas id="screenChart" height="200"></canvas>
        </div>

        <!-- Sleep Time -->
        <div class="card">
            <h3>😴 Sleep Quality</h3>
            <p>Consistency is key to a rested mind. Log your hours for better insights.</p>
            <div class="slider-row">
                <input type="range" min="1" max="12" value="7" id="sleepRange"
                       oninput="document.getElementById('sleepInput').value=this.value">
                <input type="number" id="sleepInput" value="7" min="1" max="12"
                       oninput="document.getElementById('sleepRange').value=this.value">
                <button class="btn" onclick="logData('sleep')">Log</button>
            </div>
            <canvas id="sleepChart" height="200"></canvas>
        </div>

        <!-- AI Suggestion -->
        <div class="card">
            <h3>🤖 AI Wellness Suggestion <span id="aiStatus" style="font-size: 0.6rem; vertical-align: middle; padding: 2px 8px; border-radius: 10px; background: #eee; color: #888; margin-left:10px;">Checking AI...</span></h3>
            <div class="ai-box" style="margin-top:0.5rem;">
                <div class="ai-icon">✨</div>
                <div class="ai-text">
                    <h4 id="ai-title">Analyzing your routine…</h4>
                    <p id="ai-desc">Generating personalized insights based on your recent activity.</p>
                </div>
            </div>
            <button class="btn" style="width:100%; margin-top:1rem;" onclick="fetchSuggestion()">Refresh ✨</button>
        </div>

        <!-- AI Daily To-Do (NEW) -->
        <div class="card">
            <h3>✅ Daily Wellness To-Do</h3>
            <p>3 personalized steps to improve your day.</p>
            <div id="todoList" style="margin-top:1rem; display:flex; flex-direction:column; gap:12px;">
                <p style="color:#aaa; font-size:0.85rem;">Generating your list...</p>
            </div>
            <button class="btn" style="width:100%; margin-top:1.5rem; background:rgba(0,0,0,0.05); color:#4a4a8a; border:1px solid #e0d7f5;" onclick="fetchTodo()">Update Tasks 🔄</button>
        </div>
    </div>

    <div class="section-label">Quick Actions 🚀</div>
    <div class="actions-grid">
        <a href="chatbot.php" class="action-card">
            <div>
                <div class="icon">💬</div>
                <h4>Talk to AI</h4>
                <p>Need to talk? Tap to chat with your AI companion.</p>
            </div>
            <span class="btn" style="width:fit-content; padding: 0.4rem 1rem; font-size: 0.8rem;">Start Chat ✨</span>
        </a>
        <a href="activity.php" class="action-card">
            <div>
                <div class="icon">🧘</div>
                <h4>Mood Log</h4>
                <p>Haven't logged in your mood today, come and login.</p>
            </div>
            <span class="btn" style="width:fit-content; padding: 0.4rem 1rem; font-size: 0.8rem;">Log Mood 🌸</span>
        </a>
        <a href="journaling.php" class="action-card">
            <div>
                <div class="icon">📝</div>
                <h4>Mindful Journal</h4>
                <p>Reflect on your day. Write your heart out in your journal.</p>
            </div>
            <span class="btn" style="width:fit-content; padding: 0.4rem 1rem; font-size: 0.8rem;">Write Now ✍️</span>
        </a>
        <a href="insights.php" class="action-card">
            <div>
                <div class="icon">💡</div>
                <h4>Curated Insights</h4>
                <p>Peace of mind is just a click away. Explore curated insights.</p>
            </div>
            <span class="btn" style="width:fit-content; padding: 0.4rem 1rem; font-size: 0.8rem;">Explore 🌿</span>
        </a>
    </div>
</div>

<script>
// Charts
const dates       = <?= json_encode($dates) ?>;
const screenTimes = <?= json_encode($screen_times) ?>;
const sleepTimes  = <?= json_encode($sleep_times) ?>;

new Chart(document.getElementById('screenChart'), {
    type: 'bar',
    data: { labels: dates, datasets: [{ label: 'Screen Time (hrs)', data: screenTimes, backgroundColor: 'rgba(163,196,243,0.7)', borderRadius: 10 }] },
    options: { responsive: true, scales: { y: { beginAtZero: true, max: 24 } }, plugins: { legend: { display: false } } }
});
new Chart(document.getElementById('sleepChart'), {
    type: 'bar',
    data: { labels: dates, datasets: [{ label: 'Sleep Time (hrs)', data: sleepTimes, backgroundColor: 'rgba(205,180,219,0.7)', borderRadius: 10 }] },
    options: { responsive: true, scales: { y: { beginAtZero: true, max: 12 } }, plugins: { legend: { display: false } } }
});

async function logData(type) {
    const screenVal = document.getElementById('screenInput').value;
    const sleepVal = document.getElementById('sleepInput').value;
    
    const formData = new FormData();
    formData.append('action', 'save_screen_sleep');
    formData.append('screen_time', screenVal);
    formData.append('sleep_time', sleepVal);

    try {
        const res = await fetch('api_handler.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.status === 'success') {
            location.reload(); 
        } else {
            alert('❌ Error saving data: ' + data.message);
        }
    } catch (err) {
        alert('❌ Network error saving data.');
    }
}

// Check for Auto-Sync Status
async function checkSyncStatus() {
    try {
        const res = await fetch('api_handler.php?action=get_sync_status');
        const data = await res.json();
        const badge = document.getElementById('syncBadge');
        const label = document.getElementById('autoTrackLabel');
        
        if (data.status === 'success' && data.active) {
            badge.style.display = 'block';
            label.style.display = 'block';
        } else {
            badge.style.display = 'none';
            label.style.display = 'none';
        }
    } catch(e){}
}
checkSyncStatus();
setInterval(checkSyncStatus, 15000); // Check every 15s

async function fetchSuggestion() {
    const titleEl = document.getElementById('ai-title');
    const descEl = document.getElementById('ai-desc');
    
    titleEl.innerText = "Analyzing your vibe... ✨";
    descEl.innerText = "Connecting with Groq to personalize your day.";

    try {
        const res = await fetch('api_handler.php?action=get_ai_suggestions');
        const userData = await res.json();
        
        const aiRes = await fetch(`http://${window.location.hostname}:5000/api/suggestions`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                screen_time: userData.screen_time || 0,
                sleep_time: userData.sleep_time || 0,
                mood: userData.mood || 'Neutral'
            })
        });
        const aiData = await aiRes.json();
        titleEl.innerText = aiData.title;
        descEl.innerText  = aiData.desc;
    } catch (err) {
        titleEl.innerText = "Connection Issue ⚠️";
        descEl.innerText  = "Mindora AI is currently resting. Please make sure 'python app.py' is running in your terminal!";
        console.error("AI Fetch Error:", err);
    }
}
setTimeout(fetchSuggestion, 1000);

async function checkAIHealth() {
    const statusEl = document.getElementById('aiStatus');
    if (!statusEl) return;
    try {
        const res = await fetch(`http://${window.location.hostname}:5000/api/health`);
        if (res.ok) {
            statusEl.innerText = "● AI Online";
            statusEl.style.background = "#d7f5d7";
            statusEl.style.color = "#2e7d32";
        } else {
            throw new Error();
        }
    } catch (e) {
        statusEl.innerText = "○ AI Offline";
        statusEl.style.background = "#ffdce0";
        statusEl.style.color = "#c62828";
    }
}
checkAIHealth();
setInterval(checkAIHealth, 60000); // Check every 60s

async function fetchTodo() {
    const listEl = document.getElementById('todoList');
    try {
        // 1. Get user context from PHP
        const ctxRes = await fetch('api_handler.php?action=get_comprehensive_data');
        const ctxData = await ctxRes.json();
        
        // 2. Call AI Backend
        const aiRes = await fetch(`http://${window.location.hostname}:5000/api/todo`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(ctxData.data)
        });
        const aiData = await aiRes.json();
        
        if (aiData.todos) {
            listEl.innerHTML = aiData.todos.map((t, i) => `
                <label style="display:flex; align-items:center; gap:12px; cursor:pointer; background:rgba(255,255,255,0.4); padding:10px; border-radius:12px; border:1px solid rgba(0,0,0,0.03);">
                    <input type="checkbox" style="width:18px; height:18px; accent-color:#a8d5a2;" onchange="confettiEffect(this)">
                    <span style="font-size:0.9rem; color:#4a4a8a; font-weight:500;">${t}</span>
                </label>
            `).join('');
        }
    } catch (e) {
        listEl.innerHTML = '<p style="color:#e57373; font-size:0.85rem;">Could not connect to AI server. Please make sure the backend is running!</p>';
    }
}
fetchTodo();

function confettiEffect(cb) {
    if (cb.checked) {
        cb.parentElement.style.opacity = '0.5';
        cb.parentElement.style.textDecoration = 'line-through';
    } else {
        cb.parentElement.style.opacity = '1';
        cb.parentElement.style.textDecoration = 'none';
    }
}
</script>
</body>
</html>
