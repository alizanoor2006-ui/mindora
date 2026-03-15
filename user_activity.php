<?php
session_start();
require_once 'config.php';

// Guard: admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin_controls.php");
    exit();
}

$uid = intval($_GET['id']);
$userQuery = $conn->query("SELECT name, email FROM users WHERE id = $uid AND role = 'student'");
if ($userQuery->num_rows === 0) {
    header("Location: admin_controls.php");
    exit();
}
$user = $userQuery->fetch_assoc();

// --- FETCH WELLNESS METRICS (AGGREGATED/ANALYTIC ONLY) ---

// 1. Mood History (Last 10 entries)
$moods = $conn->query("SELECT mood, date FROM mood_data WHERE user_id = $uid ORDER BY date DESC LIMIT 10");

// 2. Stress & Quiz Analytics
$quizzes = $conn->query("SELECT type, score, stress_level, date FROM quiz_results WHERE user_id = $uid ORDER BY date DESC");

// 3. Sentiment Distribution (Journal Analytics)
$sentimentQuery = $conn->query("SELECT sentiment_label, COUNT(*) as count FROM journals WHERE user_id = $uid GROUP BY sentiment_label");
$sentHistory = [];
while($row = $sentimentQuery->fetch_assoc()) $sentHistory[$row['sentiment_label']] = $row['count'];

// 4. Activity Volume (Chatbot interactions count)
$chatCount = $conn->query("SELECT COUNT(*) FROM chatbot_logs WHERE user_id = $uid")->fetch_row()[0];

// 5. Screen & Sleep History (Last 7 entries)
$screenSleep = $conn->query("SELECT screen_time, sleep_time, date FROM screen_sleep_data WHERE user_id = $uid ORDER BY date DESC LIMIT 7");
$ssLabels = []; $screenData = []; $sleepData = [];
while($row = $screenSleep->fetch_assoc()) {
    $ssLabels[] = date('M d', strtotime($row['date']));
    $screenData[] = $row['screen_time'];
    $sleepData[] = $row['sleep_time'];
}
$ssLabels = array_reverse($ssLabels);
$screenData = array_reverse($screenData);
$sleepData = array_reverse($sleepData);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindora | Wellness Metrics - <?= htmlspecialchars($user['name']) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #e8f5e9, #ede7f6, #e3f2fd); min-height: 100vh; color: #333; }
        
        nav {
            background: rgba(255,255,255,0.75); backdrop-filter: blur(16px);
            padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 16px rgba(0,0,0,0.07); position: sticky; top: 0; z-index: 100;
        }
        .nav-brand { font-size: 1.4rem; font-weight: 800; color: #4a4a8a; }
        .nav-brand span { color: #a8d5a2; }

        .container { padding: 2rem; max-width: 1000px; margin: 0 auto; }
        .back-link { text-decoration: none; color: #7c6ab5; font-weight: 600; display: inline-block; margin-bottom: 1rem; }
        
        .profile-header {
            background: rgba(255,255,255,0.7); backdrop-filter: blur(16px);
            border-radius: 24px; padding: 2rem; margin-bottom: 1.5rem;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .user-info h1 { color: #4a4a8a; margin-bottom: 0.2rem; }
        .user-info p { color: #888; font-size: 0.9rem; }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .card {
            background: rgba(255,255,255,0.8); border-radius: 20px; padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .card h3 { color: #4a4a8a; margin-bottom: 1rem; font-size: 1.1rem; }

        .metric-badge {
            background: #f0edf8; color: #4a4a8a; padding: 1rem; border-radius: 15px;
            text-align: center; margin-top: 1rem;
        }
        .metric-badge span { font-size: 1.8rem; font-weight: 800; display: block; }
        .metric-badge small { font-size: 0.75rem; font-weight: 700; color: #888; }

        table { width: 100%; border-collapse: collapse; margin-top: 0.5rem; }
        th, td { text-align: left; padding: 0.6rem; border-bottom: 1px solid #eee; font-size: 0.85rem; }
        
        .badge { padding: 3px 8px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; color: white; }
        .badge-high { background: #e57373; }
        .badge-medium { background: #ffb74d; }
        .badge-low { background: #66bb6a; }

        .privacy-box {
            background: #fff3e0; border: 1px dashed #ffa726; border-radius: 12px;
            padding: 0.75rem; font-size: 0.8rem; color: #e65100; margin-bottom: 1rem;
            display: flex; align-items: center; gap: 0.5rem;
        }
    </style>
</head>
<body>
<nav>
    <div class="nav-brand">Mind<span>ora</span> 🌿 <small style="font-size:0.75rem; border:1px solid #7c6ab5; color:#7c6ab5; padding:2px 8px; border-radius:6px; margin-left:6px;">Wellness Analysis</small></div>
    <a href="logout.php" style="text-decoration:none; color:#e57373; font-weight:600;">🚪 Logout</a>
</nav>

<div class="container">
    <a href="admin_controls.php" class="back-link">← Back to Controls</a>

    <div class="profile-header">
        <div class="user-info">
            <h1><?= htmlspecialchars($user['name']) ?></h1>
            <p><?= htmlspecialchars($user['email']) ?> • Wellness Overview</p>
        </div>
        <div class="metric-badge">
            <span><?= $chatCount ?></span>
            <small>AI CHAT SESSIONS</small>
        </div>
    </div>

    <div class="privacy-box">
        🛡️ <strong>Privacy Protection:</strong> Raw journal content and chat logs are encrypted/private and cannot be viewed by administrative staff. You are viewing analyzed sentiment and stress patterns only.
    </div>

    <div class="grid">
        <!-- Screen vs Sleep Chart -->
        <div class="card" style="grid-column: span 2;">
            <h3>📊 Screen Time vs Sleep History</h3>
            <div style="height: 250px;"><canvas id="ssChart"></canvas></div>
        </div>

        <!-- Sentiment distribution -->
        <div class="card">
            <h3>🧠 Sentiment Distribution</h3>
            <canvas id="sentimentChart" height="250"></canvas>
        </div>

        <!-- Mood History -->
        <div class="card">
            <h3>📈 Recent Mood Log</h3>
            <table>
                <thead><tr><th>Date</th><th>Mood</th></tr></thead>
                <tbody>
                    <?php while($m = $moods->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($m['date'])) ?></td>
                        <td style="font-weight:600; color:#4a4a8a;"><?= $m['mood'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($moods->num_rows == 0): ?><tr><td colspan="2" style="text-align:center;">No logs yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Quiz Performance -->
        <div class="card" style="grid-column: span 2;">
            <h3>📊 Stress & Success Patterns</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Quiz Type</th>
                        <th>Performance Score</th>
                        <th>Stress Level</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($q = $quizzes->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('M d', strtotime($q['date'])) ?></td>
                        <td><?= ucfirst($q['type']) ?> Form</td>
                        <td><strong><?= $q['score'] ?> / 100</strong></td>
                        <td><span class="badge badge-<?= strtolower($q['stress_level']) ?>"><?= $q['stress_level'] ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($quizzes->num_rows == 0): ?><tr><td colspan="4" style="text-align:center;">User hasn't taken any quizzes yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Screen vs Sleep Chart
    new Chart(document.getElementById('ssChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: <?= json_encode($ssLabels) ?>,
            datasets: [
                { label: 'Screen Time (h)', data: <?= json_encode($screenData) ?>, borderColor: '#b39ddb', backgroundColor: 'rgba(179,157,219,0.1)', fill: true, tension: 0.4 },
                { label: 'Sleep Time (h)', data: <?= json_encode($sleepData) ?>, borderColor: '#a8d5a2', backgroundColor: 'rgba(168,213,162,0.1)', fill: true, tension: 0.4 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, max: 24 } }
        }
    });

    // Sentiment Chart
    const sentCtx = document.getElementById('sentimentChart').getContext('2d');
    new Chart(sentCtx, {
        type: 'doughnut',
        data: {
            labels: ['Positive', 'Neutral', 'Negative'],
            datasets: [{
                data: [
                    <?= $sentHistory['Positive'] ?? 0 ?>,
                    <?= $sentHistory['Neutral'] ?? 0 ?>,
                    <?= $sentHistory['Negative'] ?? 0 ?>
                ],
                backgroundColor: ['#a8d5a2', '#d0e1f9', '#ffb3ba'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, weight: '700' } } }
            }
        }
    });
</script>
</body>
</html>
