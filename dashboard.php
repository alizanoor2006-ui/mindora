<?php
session_start();
require_once 'config.php';

// Guard: admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- DATA FETCHING FOR ANALYTICS ---

// 1. Overall User Count
$totalUsers = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetch_row()[0];

// 2. Stress Level Distribution (Doughnut)
$stressDataRaw = $conn->query("SELECT stress_level, COUNT(*) as count FROM quiz_results GROUP BY stress_level");
$stressLabels = ["High", "Medium", "Low"];
$stressCounts = [0, 0, 0];
while($row = $stressDataRaw->fetch_assoc()) {
    if($row['stress_level'] == 'High') $stressCounts[0] = $row['count'];
    if($row['stress_level'] == 'Medium') $stressCounts[1] = $row['count'];
    if($row['stress_level'] == 'Low') $stressCounts[2] = $row['count'];
}

// 3. Weekly Mood Trends (Stacked Bar)
$weekDates = [];
for ($i = 6; $i >= 0; $i--) $weekDates[] = date('Y-m-d', strtotime("-$i days"));
$moods = ['Happy', 'Calm', 'Neutral', 'Sad', 'Stressed'];
$moodTrends = [];
foreach($moods as $m) $moodTrends[$m] = array_fill(0, 7, 0);

$moodDataRaw = $conn->query("SELECT mood, date, COUNT(*) as count FROM mood_data WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY mood, date");
while($row = $moodDataRaw->fetch_assoc()) {
    $idx = array_search($row['date'], $weekDates);
    if($idx !== false && isset($moodTrends[$row['mood']])) {
        $moodTrends[$row['mood']][$idx] = intval($row['count']);
    }
}
$weekLabels = array_map(function($d) { return date('D', strtotime($d)); }, $weekDates);

// 4. Screen Time vs Stress Correlation (Line Chart)
$correlationData = $conn->query("
    SELECT ROUND(s.screen_time) as hr, AVG(q.score) as avg_stress 
    FROM screen_sleep_data s
    JOIN quiz_results q ON s.user_id = q.user_id AND s.date = q.date
    GROUP BY hr ORDER BY hr ASC
");
$corrLabels = [];
$corrValues = [];
while($row = $correlationData->fetch_assoc()) {
    $corrLabels[] = $row['hr'] . "h";
    $corrValues[] = round($row['avg_stress'], 1);
}

// 5. Journal Sentiment Overview (Polar Area)
$sentimentDataRaw = $conn->query("SELECT sentiment_label, COUNT(*) as count FROM journals GROUP BY sentiment_label");
$sentLabels = ["Positive", "Neutral", "Negative"];
$sentCounts = [0, 0, 0];
while($row = $sentimentDataRaw->fetch_assoc()) {
    if($row['sentiment_label'] == 'Positive') $sentCounts[0] = $row['count'];
    if($row['sentiment_label'] == 'Neutral') $sentCounts[1] = $row['count'];
    if($row['sentiment_label'] == 'Negative') $sentCounts[2] = $row['count'];
}

// 6. Alert Ticker
$highAlert = $conn->query("SELECT u.name, q.stress_level, q.score, q.date 
                           FROM users u 
                           JOIN quiz_results q ON u.id = q.user_id 
                           WHERE q.stress_level = 'High' 
                           ORDER BY q.date DESC LIMIT 5");

// 7. Quiz Performance Analytics (NEW)
$quizPerformanceRaw = $conn->query("SELECT type, AVG(score) as avg_score, COUNT(*) as take_count FROM quiz_results GROUP BY type");
$quizPerf = [];
while($row = $quizPerformanceRaw->fetch_assoc()) {
    $quizPerf[$row['type']] = ['avg' => round($row['avg_score'], 1), 'count' => $row['take_count']];
}

$dailyQuizScores = array_fill(0, 7, 0);
$quizScoreRaw = $conn->query("SELECT date, AVG(score) as avg_score FROM quiz_results WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY date");
while($row = $quizScoreRaw->fetch_assoc()) {
    $idx = array_search($row['date'], $weekDates);
    if($idx !== false) $dailyQuizScores[$idx] = round($row['avg_score'], 1);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindora | Admin Dashboard</title>
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

        .container { padding: 2rem; max-width: 1300px; margin: 0 auto; }
        .page-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .page-head h1 { font-size: 1.8rem; color: #4a4a8a; }
        
        .stat-box {
            background: rgba(255,255,255,0.7); backdrop-filter: blur(12px);
            border-radius: 16px; padding: 1rem 2rem; text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        }
        .stat-box small { color: #aaa; font-size: 0.85rem; }
        .stat-box .big  { font-size: 2.2rem; font-weight: 800; color: #4a4a8a; }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; }
        .card {
            background: rgba(255,255,255,0.7); backdrop-filter: blur(16px);
            border-radius: 20px; padding: 1.5rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08); margin-bottom: 1.5rem;
        }
        .card h3 { color: #4a4a8a; margin-bottom: 1.2rem; display: flex; align-items: center; gap: 0.5rem; font-size: 1.15rem; }

        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { text-align: left; padding: 0.75rem; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        th { color: #888; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
        
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; color: white; }
        .badge-high   { background: #e57373; }

        .btn {
            display: inline-block; padding: 0.8rem 1.8rem;
            border-radius: 50px; font-size: 1rem; font-weight: 700;
            cursor: pointer; border: none; text-decoration: none; transition: all 0.2s;
            background: linear-gradient(135deg,#a8d5a2,#b39ddb); color: white;
            box-shadow: 0 4px 15px rgba(179, 157, 219, 0.4);
        }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(179, 157, 219, 0.6); }

        .alert-row { background: rgba(229, 115, 115, 0.08); }
        .chart-container { height: 280px; position: relative; }

        .controls-banner {
            background: linear-gradient(135deg, #4a4a8a, #7c6ab5);
            color: white; border-radius: 24px; padding: 2.5rem; margin-bottom: 2rem;
            display: flex; justify-content: space-between; align-items: center;
        }

        .quiz-perf-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; }
        .perf-item { background: rgba(255,255,255,0.5); padding: 1rem; border-radius: 15px; text-align: center; }
        .perf-item h4 { font-size: 0.8rem; color: #888; margin-bottom: 0.5rem; text-transform: uppercase; }
        .perf-item .val { font-size: 1.5rem; font-weight: 800; color: #4a4a8a; }
    </style>
</head>
<body>
<nav>
    <div class="nav-brand">Mind<span>ora</span> 🌿 <small style="font-size:0.75rem; border:1px solid #7c6ab5; color:#7c6ab5; padding:2px 8px; border-radius:6px; margin-left:6px;">Admin</small></div>
    <div>
        <a href="dashboard.php" style="text-decoration:none; color:#b39ddb; font-weight:800; margin-right:20px;">📊 Dashboard</a>
        <a href="admin_controls.php" style="text-decoration:none; color:#4a4a8a; font-weight:600; margin-right:20px;">⚙️ Controls</a>
        <a href="logout.php" style="text-decoration:none; color:#e57373; font-weight:600;">🚪 Logout</a>
    </div>
</nav>

<div class="container">
    <div class="controls-banner">
        <div>
            <h2 style="font-size:1.8rem; margin-bottom:0.5rem;">Admin Controls & User Management</h2>
            <p style="opacity:0.9;">Manage student access, publish resources, and download CSV reports.</p>
        </div>
        <a href="admin_controls.php" class="btn" style="background:#fff; color:#4a4a8a;">Open Controls Panel ⚙️</a>
    </div>

    <div class="page-head">
        <div>
            <h1>Real-time Analytics Dashboard</h1>
            <p>Aggregated campus health trends and stress monitoring.</p>
        </div>
        <div class="stat-box">
            <small>Active Students</small>
            <div class="big"><?= $totalUsers ?></div>
        </div>
    </div>

    <div class="grid">
        <!-- Stress Distribution -->
        <div class="card">
            <h3>📊 Current Stress Distribution</h3>
            <div class="chart-container"><canvas id="stressChart"></canvas></div>
        </div>

        <!-- Quiz Performance (NEW) -->
        <div class="card">
            <h3>🎓 Quiz Performance Analysis</h3>
            <div class="quiz-perf-grid">
                <div class="perf-item">
                    <h4>Short Quiz Avg</h4>
                    <div class="val"><?= $quizPerf['short']['avg'] ?? '0' ?>%</div>
                    <small style="font-size:0.7rem; color:#aaa;"><?= $quizPerf['short']['count'] ?? '0' ?> takes</small>
                </div>
                <div class="perf-item">
                    <h4>Long Quiz Avg</h4>
                    <div class="val"><?= $quizPerf['long']['avg'] ?? '0' ?>%</div>
                    <small style="font-size:0.7rem; color:#aaa;"><?= $quizPerf['long']['count'] ?? '0' ?> takes</small>
                </div>
            </div>
            <div class="chart-container" style="height:180px; margin-top:1rem;">
                <canvas id="quizTrendChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h3>📅 Weekly Mood Trends</h3>
            <div class="chart-container"><canvas id="moodTrendsChart"></canvas></div>
        </div>

        <div class="card">
            <h3>📈 Screen Time vs Stress Score</h3>
            <div class="chart-container"><canvas id="correlationChart"></canvas></div>
        </div>

        <div class="card">
            <h3>📝 Journal Sentiment Overview</h3>
            <div class="chart-container"><canvas id="sentimentChart"></canvas></div>
        </div>

        <!-- High Alert Section -->
        <div class="card" style="grid-column: span 2;">
            <h3 style="color:#c62828;">🚨 Student High Alert</h3>
            <table>
                <thead><tr><th>Student Name</th><th>Level</th><th>Score</th><th>Date</th></tr></thead>
                <tbody>
                    <?php while($row = $highAlert->fetch_assoc()): ?>
                    <tr class="alert-row">
                        <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                        <td><span class="badge badge-high"><?= $row['stress_level'] ?></span></td>
                        <td><strong><?= $row['score'] ?></strong></td>
                        <td><?= date('M d', strtotime($row['date'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($highAlert->num_rows == 0): ?><tr><td colspan="4" style="text-align:center;">No high alert students.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// 1. Stress Chart
new Chart(document.getElementById('stressChart').getContext('2d'), { type: 'doughnut', data: { labels: <?= json_encode($stressLabels) ?>, datasets: [{ data: <?= json_encode($stressCounts) ?>, backgroundColor: ['#e57373','#ffb74d','#66bb6a'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom' } } } });

// 2. Quiz Trend Chart (NEW)
new Chart(document.getElementById('quizTrendChart').getContext('2d'), { 
    type: 'line', 
    data: { 
        labels: <?= json_encode($weekLabels) ?>, 
        datasets: [{ 
            label: 'Avg Score', 
            data: <?= json_encode($dailyQuizScores) ?>, 
            borderColor: '#4caf50', 
            backgroundColor: 'rgba(76,175,80,0.1)', 
            fill: true, 
            tension: 0.3 
        }] 
    }, 
    options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        scales: { y: { beginAtZero: true, max: 100 } },
        plugins: { legend: { display: false } }
    } 
});

// 3. Mood Trends
new Chart(document.getElementById('moodTrendsChart').getContext('2d'), { type: 'bar', data: { labels: <?= json_encode($weekLabels) ?>, datasets: [ { label: 'Happy', data: <?= json_encode($moodTrends['Happy']) ?>, backgroundColor: '#baf2bb' }, { label: 'Calm', data: <?= json_encode($moodTrends['Calm']) ?>, backgroundColor: '#a8d5a2' }, { label: 'Neutral', data: <?= json_encode($moodTrends['Neutral']) ?>, backgroundColor: '#d0e1f9' }, { label: 'Sad', data: <?= json_encode($moodTrends['Sad']) ?>, backgroundColor: '#fdfd96' }, { label: 'Stressed', data: <?= json_encode($moodTrends['Stressed']) ?>, backgroundColor: '#ffb3ba' } ] }, options: { responsive: true, maintainAspectRatio: false, scales: { x: { stacked: true }, y: { stacked: true } } } });

// 4. Correlation
new Chart(document.getElementById('correlationChart').getContext('2d'), { type: 'line', data: { labels: <?= json_encode($corrLabels) ?>, datasets: [{ label: 'Avg Stress Score', data: <?= json_encode($corrValues) ?>, borderColor: '#9575cd', backgroundColor: 'rgba(149,117,205,0.1)', fill: true, tension: 0.4 }] }, options: { responsive: true, maintainAspectRatio: false } });

// 5. Sentiment
new Chart(document.getElementById('sentimentChart').getContext('2d'), { type: 'polarArea', data: { labels: <?= json_encode($sentLabels) ?>, datasets: [{ data: <?= json_encode($sentCounts) ?>, backgroundColor: ['rgba(186,242,187,0.7)', 'rgba(208,225,249,0.7)', 'rgba(255,179,186,0.7)'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false } });
</script>
</body>
</html>
