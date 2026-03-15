<?php
// Shared navigation component – include after session_start() + config.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<style>
#navLinks {
    display: none;
    position: absolute;
    top: 70px; left: 0;
    width: 100%;
    background: rgba(255,255,255,0.98);
    flex-direction: column;
    padding: 1rem 2rem;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    z-index: 99;
}
#navLinks.open { display: flex !important; }
#navLinks a {
    padding: .75rem 0;
    text-decoration: none;
    color: #4a4a8a;
    font-weight: 600;
    border-bottom: 1px solid #f0edf8;
    display: block;
    font-family: 'Segoe UI', sans-serif;
    transition: color 0.2s;
}
#navLinks a:hover  { color: #b39ddb; }
#navLinks a:last-child { border-bottom: none; color: #e57373; }
</style>

<nav style="
    background: rgba(255,255,255,0.75);
    backdrop-filter: blur(16px);
    padding: 1rem 2rem;
    display: flex; align-items: center; justify-content: space-between;
    box-shadow: 0 2px 16px rgba(0,0,0,0.07);
    position: sticky; top: 0; z-index: 100;
    font-family: 'Segoe UI', sans-serif;
">
    <div style="font-size:1.4rem; font-weight:800; color:#4a4a8a; display:flex; align-items:center; gap:12px;">
        <?php
        // Fetch gender and latest mood for profile photo
        $uid = $_SESSION['user_id'];
        
        // Auto-fix: Ensure gender column exists (prevents fatal error)
        $check_gender = $conn->query("SHOW COLUMNS FROM users LIKE 'gender'");
        if ($check_gender->num_rows === 0) {
            $conn->query("ALTER TABLE users ADD COLUMN gender ENUM('male', 'female', 'other') DEFAULT 'female'");
        }

        $u_info = $conn->query("SELECT gender FROM users WHERE id = $uid")->fetch_assoc();
        $gender = $u_info['gender'] ?? 'female';
        
        $latest_mood = $conn->query("SELECT mood FROM mood_data WHERE user_id = $uid ORDER BY id DESC LIMIT 1")->fetch_assoc();
        $mood_file = $latest_mood ? strtolower($latest_mood['mood']) : 'neutral';
        $avatar_path = "assets/avatars/$gender/$mood_file.png";
        ?>
        <img id="navProfilePic" src="<?= $avatar_path ?>" style="width:40px; height:40px; border-radius:50%; border:2px solid #b39ddb; background:white; object-fit:cover;" alt="Profile">
        <span>Mind<span style="color:#a8d5a2;">ora</span> 🌿</span>
    </div>
    <button id="hamburgerBtn" style="font-size:1.6rem; cursor:pointer; background:none; border:none; color:#4a4a8a;" aria-label="Menu">☰</button>
    <div id="navLinks">
        <a href="student_dashboard.php">🏠 Home</a>
        <a href="activity.php">📊 Activity</a>
        <a href="insights.php">🔎 Insights</a>
        <a href="journaling.php">📔 Journaling</a>
        <a href="chatbot.php">💬 Chatbot</a>
        <a href="settings.php">⚙ Settings</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
</nav>

<!-- Hidden Global Ambient Audio -->
<audio id="globalAmbientAudio" loop>
    <source src="" type="audio/mpeg">
</audio>

<script>
window.userGender = '<?= $gender ?>';
(function () {
    var btn = document.getElementById('hamburgerBtn');
    var menu = document.getElementById('navLinks');

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.classList.toggle('open');
    });

    // Close when clicking anywhere outside the nav
    document.addEventListener('click', function (e) {
        if (!e.target.closest('nav')) {
            menu.classList.remove('open');
        }
    });

    // Apply saved theme globally
    const savedTheme = localStorage.getItem('mindora_theme');
    if (savedTheme) {
        document.body.style.background = `linear-gradient(135deg, ${savedTheme})`;
    }

    // Global Audio Initialization
    const soundSources = {
        'ocean': 'https://www.orangefreesounds.com/wp-content/uploads/2015/08/Ocean-waves.mp3',
        'rain': 'https://www.orangefreesounds.com/wp-content/uploads/2014/10/Free-rain-sounds.mp3',
        'forest': 'https://www.orangefreesounds.com/wp-content/uploads/2016/04/Rainforest-sounds.mp3',
        'zen': 'https://www.orangefreesounds.com/wp-content/uploads/2021/12/Tibetan-bowl-meditation-music.mp3',
        'stress': 'https://www.orangefreesounds.com/wp-content/uploads/2025/03/Relaxing-music-for-stress-relief.mp3'
    };

    let savedAudio = localStorage.getItem('mindora_audio');
    let savedSoundType = localStorage.getItem('mindora_sound_type') || 'ocean';

    // Default to true if not set before
    if (savedAudio === null) {
        savedAudio = 'true';
        localStorage.setItem('mindora_audio', 'true');
        localStorage.setItem('mindora_sound_type', 'ocean');
    }

    const audioEl = document.getElementById('globalAmbientAudio');
    if (audioEl) {
        const sourceEl = audioEl.querySelector('source');
        sourceEl.src = soundSources[savedSoundType];
        audioEl.load();

        if (savedAudio === 'true') {
            const playAudio = () => {
                audioEl.play().catch(e => console.log("Autoplay blocked. Will play on interaction."));
                document.removeEventListener('click', playAudio);
                document.removeEventListener('keydown', playAudio);
            };
            playAudio();
            document.addEventListener('click', playAudio);
            document.addEventListener('keydown', playAudio);
        }
    }
})();
</script>
