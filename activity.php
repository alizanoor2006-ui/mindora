<?php
session_start();
require_once 'config.php';
$pageTitle = 'Activity';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindora | Activity</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #e8f5e9, #ede7f6, #e3f2fd); min-height: 100vh; }
        .container { padding: 2rem; max-width: 1100px; margin: 0 auto; }
        .page-head { margin-bottom: 1.5rem; }
        .page-head h1 { font-size: 1.8rem; color: #4a4a8a; }
        .page-head p  { color: #888; margin-top: 0.3rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .card {
            background: rgba(255,255,255,0.7); backdrop-filter: blur(16px);
            border-radius: 20px; padding: 1.5rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .card h3 { color: #4a4a8a; margin-bottom: 0.5rem; }
        .card p  { color: #777; font-size: 0.9rem; }
        .full-width { grid-column: 1 / -1; }
        .streak-num { font-size: 5rem; font-weight: 900; color: #b39ddb; text-align: center; line-height: 1; margin: 1rem 0; }
        .emoji-row { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; font-size: 2.8rem; cursor: pointer; margin: 1rem 0; }
        .emoji-row div { transition: transform 0.2s; user-select: none; }
        .emoji-row div:hover { transform: scale(1.25); }
        #mood-response { text-align: center; color: #7c6ab5; font-weight: 600; min-height: 1.5rem; margin-top: 0.5rem; }
        .quiz-grid { display: flex; gap: 1.5rem; flex-wrap: wrap; margin-top: 1.5rem; }
        .quiz-card {
            flex: 1; min-width: 240px;
            background: rgba(255,255,255,0.5); border-radius: 16px; padding: 1.5rem;
            border: 1.5px solid #e0d7f5;
        }
        .quiz-card .icon { font-size: 2.2rem; margin-bottom: 0.75rem; }
        .quiz-card h4  { color: #4a4a8a; margin-bottom: 0.4rem; }
        .quiz-card p   { color: #777; font-size: 0.88rem; margin-bottom: 1rem; }
        .btn {
            display: inline-block; padding: 0.6rem 1.4rem;
            border-radius: 50px; font-size: 0.95rem; font-weight: 700;
            cursor: pointer; border: none; text-decoration: none;
            transition: transform 0.2s;
        }
        .btn-primary  { background: linear-gradient(135deg,#a8d5a2,#b39ddb); color: white; }
        .btn-secondary{ background: rgba(255,255,255,0.7); color: #7c6ab5; border: 2px solid #e0d7f5; }
        .btn:hover { transform: translateY(-2px); }
        .w-full { width: 100%; }
    </style>
</head>
<body>
<?php require_once 'nav.php'; ?>

<div class="container">
    <div class="page-head">
        <a href="student_dashboard.php" style="text-decoration:none; color:#4a4a8a; font-weight:600; display:inline-block; margin-bottom:1rem; transition:0.2s;" onmouseover="this.style.color='#b39ddb'; this.style.transform='translateX(-5px)'" onmouseout="this.style.color='#4a4a8a'; this.style.transform='translateX(0)'">← Back to Home</a>
        <h1>Your Activity Center 📊</h1>
        <p>Track your progress and check in with your feelings.</p>
    </div>

    <div class="grid">
        <!-- Streak -->
        <div class="card" style="text-align:center;">
            <h3>🔥 Day Streak</h3>
            <?php
            $uid = $_SESSION['user_id'];
            
            // Calculate real streak
            $streak = 0;
            $current_date = date('Y-m-d');
            
            // 1. Check if user logged mood today or yesterday (to keep streak alive)
            $check_mood = $conn->query("SELECT date as log_date FROM mood_data WHERE user_id = $uid ORDER BY id DESC");
            $logs = [];
            while ($row = $check_mood->fetch_assoc()) {
                $logs[] = $row['log_date'];
            }
            $logs = array_values(array_unique($logs)); // Only one per day, reset keys
            
            if (!empty($logs)) {
                $yesterday = date('Y-m-d', strtotime("-1 day"));
                $today = date('Y-m-d');
                
                // If the most recent log isn't today or yesterday, streak is 0
                if ($logs[0] == $today || $logs[0] == $yesterday) {
                    $streak = 1;
                    $expected_date = $logs[0];
                    
                    for ($i = 1; $i < count($logs); $i++) {
                        $prev_day = date('Y-m-d', strtotime($expected_date . " -1 day"));
                        if ($logs[$i] == $prev_day) {
                            $streak++;
                            $expected_date = $prev_day;
                        } else {
                            break;
                        }
                    }
                }
            }
            ?>
            <div class="streak-num"><?= $streak ?></div>
            <p>Consecutive active days — keep it up!</p>
        </div>

        <!-- Mood Check -->
        <div class="card" style="text-align:center;">
<?php
// Fetch gender for Bitmojis
$u_info = $conn->query("SELECT gender FROM users WHERE id = $uid")->fetch_assoc();
$gender = $u_info['gender'] ?? 'female';

// Ensure directory exists for male avatars (fallback logic)
if ($gender == 'male' && !is_dir("assets/avatars/male")) {
    mkdir("assets/avatars/male", 0777, true);
    // Note: User needs to add actual male bitmojis here.
}

$moods = [
    ['name' => 'Happy'],
    ['name' => 'Calm'],
    ['name' => 'Neutral'],
    ['name' => 'Sad'],
    ['name' => 'Stressed']
];
?>
            <h3>😊 How are you feeling today?</h3>
            <div class="emoji-row" style="display:grid; grid-template-columns: repeat(5, 1fr); gap:10px;">
                <?php foreach($moods as $m): ?>
                <div onclick="setMood('<?= $m['name'] ?>', this)" style="display:flex; flex-direction:column; align-items:center;">
                    <img src="assets/avatars/<?= $gender ?>/<?= strtolower($m['name']) ?>.png" 
                         style="width:100%; max-width:70px; border-radius:15px; border:2px solid transparent; transition:0.3s;" 
                         alt="<?= $m['name'] ?>">
                    <span style="font-size:0.8rem; margin-top:5px; color:#4a4a8a; font-weight:700;"><?= $m['name'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <p id="mood-response"></p>
        </div>

        <!-- Quiz -->
        <div class="card full-width">
            <h3>📝 Mental Health Quiz</h3>
            <p>Regular assessments help the AI understand your patterns over time.</p>
            <div class="quiz-grid">
                <div class="quiz-card">
                    <div class="icon">⏱️</div>
                    <h4>Daily Check-in</h4>
                    <p>5 quick questions for a daily overview of your mood and stress levels.</p>
                    <button class="btn btn-secondary w-full" onclick="startQuiz('short')">Take Short Quiz</button>
                </div>
                <div class="quiz-card" style="background:rgba(205,180,219,0.15); border-color:#b39ddb;">
                    <div class="icon">🧠</div>
                    <h4>Deep Assessment</h4>
                    <p>15 questions for detailed stress analysis. We recommend weekly.</p>
                    <button class="btn btn-primary w-full" onclick="startQuiz('long')">Take Long Quiz</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quiz Modal -->
<div id="quizModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:200; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:24px; padding:2rem; max-width:550px; width:95%; max-height:85vh; overflow-y:auto;">
        <h3 id="quiz-title" style="color:#4a4a8a; margin-bottom:1rem;">Quiz</h3>
        <div id="quiz-questions"></div>
        <div style="display:flex; gap:1rem; margin-top:1.5rem;">
            <button class="btn btn-primary" onclick="submitQuiz()">Submit ✨</button>
            <button class="btn btn-secondary" onclick="closeQuiz()">Cancel</button>
        </div>
        <div id="quiz-result" style="margin-top:1rem; font-weight:600; color:#4a4a8a;"></div>
    </div>
</div>

<script>
async function setMood(mood, el) {
    const pureMood = mood.split(' ')[0]; // Extract "Happy", "Calm", etc.
    document.getElementById('mood-response').innerText = `Saving your mood... ⏳`;
    
    const formData = new FormData();
    formData.append('action', 'save_mood');
    formData.append('mood', pureMood);

    try {
        const res = await fetch('api_handler.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.status === 'success') {
            document.getElementById('mood-response').innerText = `You selected: ${mood} — saved to your profile! ✨`;
            
            // Instant Profile Update
            const navPic = document.getElementById('navProfilePic');
            if (navPic && window.userGender) {
                navPic.src = `assets/avatars/${window.userGender}/${pureMood.toLowerCase()}.png`;
            }

            const siblings = el.parentElement.children;
            for (let s of siblings) { s.style.opacity = '0.4'; s.style.filter = 'grayscale(80%)'; }
            el.style.opacity = '1'; el.style.filter = 'none'; el.style.transform = 'scale(1.3)';
        } else {
            document.getElementById('mood-response').innerText = `❌ Error saving mood: ${data.message}`;
        }
    } catch (err) {
        document.getElementById('mood-response').innerText = `❌ Network error saving mood.`;
    }
}

const shortQuestions = [
    "I feel overwhelmed by my workload today.",
    "I had enough sleep last night.",
    "I feel anxious or worried right now.",
    "I enjoyed at least one activity today.",
    "I feel supported by people around me."
];
const longQuestions = [
    "I feel overwhelmed by my university workload.",
    "I had difficulty sleeping (less than 6 hours) this week.",
    "I feel nervous, anxious or on edge.",
    "I have been unable to stop worrying.",
    "I have little interest or pleasure in doing things.",
    "I feel down, depressed, or hopeless.",
    "I have been eating well and staying hydrated.",
    "I feel connected to my friends and family.",
    "I struggle to concentrate during studies.",
    "I use screens for more than 6 hours a day.",
    "I feel like my academic performance is declining.",
    "I feel physically tired most of the day.",
    "I find it hard to relax after studying.",
    "I feel confident about my future.",
    "Overall, I feel mentally healthy this week."
];

let currentType = 'short';
function startQuiz(type) {
    currentType = type;
    const qs = type === 'short' ? shortQuestions : longQuestions;
    document.getElementById('quiz-title').innerText = type === 'short' ? '⏱️ Daily Check-in Quiz' : '🧠 Deep Stress Assessment';
    document.getElementById('quiz-result').innerText = '';
    const container = document.getElementById('quiz-questions');
    const labels = {
        1: 'Very Low / Negative',
        2: 'Low',
        3: 'Moderate / Neutral',
        4: 'High',
        5: 'Very High / Positive'
    };
    container.innerHTML = qs.map((q, i) => `
        <div style="margin-bottom:1.5rem;">
            <p style="color:#4a4a8a; font-size:1rem; margin-bottom:0.6rem;"><strong>Q${i+1}.</strong> ${q}</p>
            <div style="display:flex; flex-direction:column; gap:8px;">
                ${[1,2,3,4,5].map(v=>`<label style="display:flex; align-items:center; gap:10px; font-size:0.9rem; cursor:pointer; background:rgba(0,0,0,0.03); padding:8px 12px; border-radius:10px; transition:0.2s;">
                    <input type="radio" name="q${i}" value="${v}"> <span style="font-weight:700;">${v}</span> - ${labels[v]}</label>`).join('')}
            </div>
        </div>`).join('');
    const modal = document.getElementById('quizModal');
    modal.style.display = 'flex';
}
function closeQuiz() { document.getElementById('quizModal').style.display = 'none'; }
async function submitQuiz() {
    const qs = currentType === 'short' ? shortQuestions : longQuestions;
    const answers = [];
    for (let i = 0; i < qs.length; i++) {
        const sel = document.querySelector(`input[name="q${i}"]:checked`);
        if (!sel) { alert('Please answer all questions.'); return; }
        answers.push(parseInt(sel.value));
    }
    
    document.getElementById('quiz-result').innerText = 'Analyzing with AI... 🤖';
    
    try {
        // 1. Get analysis from Python API
        const res  = await fetch('http://localhost:5000/api/quiz', { 
            method:'POST', 
            headers:{'Content-Type':'application/json'}, 
            body: JSON.stringify({answers}) 
        });
        const data = await res.json();
        
        // 2. Save result to MySQL via PHP API
        const formData = new FormData();
        formData.append('action', 'save_quiz');
        formData.append('type', currentType);
        formData.append('score', data.score);
        formData.append('stress_level', data.stress_level);

        const saveRes = await fetch('api_handler.php', {
            method: 'POST',
            body: formData
        });
        const saveData = await saveRes.json();

        if (saveData.status === 'success') {
            document.getElementById('quiz-result').innerHTML =
                `Stress Level: <span style="color:${data.stress_level==='High'?'#e57373':data.stress_level==='Medium'?'#ffb74d':'#66bb6a'}">${data.stress_level}</span> (Score: ${data.score})<br><small>${data.insight}</small><br><span style="color:#66bb6a; font-size: 0.8rem;">Saved to database! ✅</span>`;
        } else {
            document.getElementById('quiz-result').innerHTML = `Analysis complete, but error saving to DB: ${saveData.message}`;
        }
    } catch (err) {
        document.getElementById('quiz-result').innerText = 'Could not reach AI backend or database. Ensure servers are running.';
    }
}
</script>
</body>
</html>
