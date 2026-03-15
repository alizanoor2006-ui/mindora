<?php
$pageTitle = 'Home';
require_once '../includes/config.php';
require_once '../includes/header.php';

// Prepare phrase of the day
$phrases = [
    "Today is a lovely day to be happy 💛",
    "Breathe in calm, breathe out stress 🌿",
    "Small steps every day lead to big changes 🌸",
    "You are capable of amazing things ✨"
];
$phrase_of_the_day = $phrases[array_rand($phrases)];

// Mock dates for graphs
$dates = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
$screen_times = [4, 5, 3, 6, 4, 8, 7];
$sleep_times = [7, 6, 8, 7.5, 6, 9, 8];
?>

<div class="dashboard-header mb-4">
    <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
    <p class="text-muted" style="font-size: 1.2rem; font-style: italic;"><?php echo $phrase_of_the_day; ?></p>
</div>

<div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
    <!-- Screen Time Section -->
    <div class="glass-card">
        <h3>📱 Screen Time</h3>
        <p>Log your screen time today (hours)</p>
        <div class="d-flex align-center gap-2 mb-4">
            <input type="range" min="1" max="24" value="5" class="form-control" style="padding: 0;" id="screenRange" oninput="document.getElementById('screenInput').value = this.value">
            <input type="number" id="screenInput" value="5" min="1" max="24" class="form-control" style="width: 80px;" oninput="document.getElementById('screenRange').value = this.value">
            <button class="btn btn-primary" onclick="logData('screen')">Log</button>
        </div>
        <canvas id="screenChart" height="200"></canvas>
    </div>

    <!-- Sleep Time Section -->
    <div class="glass-card">
        <h3>😴 Sleep Time</h3>
        <p>Log your sleep time today (hours)</p>
        <div class="d-flex align-center gap-2 mb-4">
            <input type="range" min="1" max="24" value="7" class="form-control" style="padding: 0;" id="sleepRange" oninput="document.getElementById('sleepInput').value = this.value">
            <input type="number" id="sleepInput" value="7" min="1" max="24" class="form-control" style="width: 80px;" oninput="document.getElementById('sleepRange').value = this.value">
            <button class="btn btn-primary" onclick="logData('sleep')">Log</button>
        </div>
        <canvas id="sleepChart" height="200"></canvas>
    </div>

    <!-- AI Suggestions Section -->
    <div class="glass-card" style="grid-column: 1 / -1;">
        <h3>🤖 Personal AI Suggestion</h3>
        <div class="d-flex align-center gap-4" id="ai-suggestion-container">
            <div style="font-size: 3rem;">✨</div>
            <div style="flex: 1;">
                <h4 style="margin-bottom: 0.5rem;" id="ai-title">Analyzing your routine...</h4>
                <p id="ai-desc">Generating personalized insights based on your recent activity.</p>
            </div>
            <button class="btn btn-secondary ml-auto" onclick="fetchCustomSuggestion()">Refresh ✨</button>
        </div>
    </div>
</div>

<script>
    // Initialize Charts
    const screenCtx = document.getElementById('screenChart').getContext('2d');
    const sleepCtx = document.getElementById('sleepChart').getContext('2d');

    const screenChart = new Chart(screenCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [{
                label: 'Screen Time (hrs)',
                data: <?php echo json_encode($screen_times); ?>,
                backgroundColor: 'rgba(163, 196, 243, 0.7)',
                borderRadius: 4
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, max: 24 } } }
    });

    const sleepChart = new Chart(sleepCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [{
                label: 'Sleep Time (hrs)',
                data: <?php echo json_encode($sleep_times); ?>,
                backgroundColor: 'rgba(205, 180, 219, 0.7)',
                borderRadius: 4
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, max: 24 } } }
    });

    function logData(type) {
        let val = document.getElementById(type + 'Input').value;
        alert('Logged ' + type + ' time: ' + val + ' hours.');
    }

    function fetchCustomSuggestion() {
        document.getElementById('ai-title').innerText = "Consider a digital detox 🌿";
        document.getElementById('ai-desc').innerText = "You've had high screen time this week. Try reading a book or taking a walk before bedtime to improve your sleep quality.";
    }
    
    // Auto-fetch suggestion on load
    setTimeout(fetchCustomSuggestion, 1500);
</script>

<?php require_once '../includes/footer.php'; ?>
