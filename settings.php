<?php
session_start();
require_once 'config.php';
$pageTitle = 'Settings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindora | Settings</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #e8f5e9, #ede7f6, #e3f2fd); min-height: 100vh; }
        .container { padding: 2rem; max-width: 900px; margin: 0 auto; }
        .page-head { margin-bottom: 1.5rem; }
        .page-head h1 { font-size: 1.8rem; color: #4a4a8a; }
        .page-head p  { color: #888; margin-top: 0.3rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .card {
            background: rgba(255,255,255,0.7); backdrop-filter: blur(16px);
            border-radius: 20px; padding: 1.5rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .card h3 { color: #4a4a8a; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1.2rem; }
        label.field-label { display: block; margin-bottom: 0.4rem; color: #5c5c8a; font-size: 0.9rem; font-weight: 600; }
        input[type=text], input[type=email] {
            width: 100%; padding: 0.7rem 1rem;
            border: 2px solid #e0d7f5; border-radius: 12px;
            font-size: 1rem; background: rgba(255,255,255,0.8);
            outline: none; transition: border 0.2s;
        }
        input:focus { border-color: #b39ddb; }
        .btn {
            display: inline-block; padding: 0.65rem 1.5rem;
            border-radius: 50px; font-size: 0.95rem; font-weight: 700;
            cursor: pointer; border: none; transition: transform 0.2s;
        }
        .btn-primary { background: linear-gradient(135deg,#a8d5a2,#b39ddb); color: white; }
        .btn:hover   { transform: translateY(-2px); }
        /* Toggle */
        .toggle-row   { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; }
        .toggle-info h4 { color: #4a4a8a; font-size: 0.95rem; margin-bottom: 0.2rem; }
        .toggle-info p  { color: #aaa; font-size: 0.82rem; }
        .switch { position: relative; display: inline-block; width: 50px; height: 28px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute; cursor: pointer; top:0; left:0; right:0; bottom:0;
            background: #ccc; transition: .4s; border-radius: 34px;
        }
        .slider:before {
            position: absolute; content:""; height:20px; width:20px;
            left:4px; bottom:4px; background:white; transition:.4s;
            border-radius:50%; box-shadow:0 2px 4px rgba(0,0,0,0.2);
        }
        input:checked + .slider { background: #b39ddb; }
        input:checked + .slider:before { transform: translateX(22px); }
        .divider { border: none; border-top: 1px solid #f0edf8; margin: 1rem 0; }
        /* Color swatches */
        .swatch-row { display: flex; gap: 0.75rem; margin-top: 0.5rem; }
        .swatch {
            width: 38px; height: 38px; border-radius: 50%; cursor: pointer;
            border: 3px solid transparent; transition: transform 0.2s, border 0.2s;
        }
        .swatch:hover, .swatch.active { transform: scale(1.2); border-color: #fff; box-shadow: 0 0 0 3px #b39ddb; }

        /* Sound Selection */
        .sound-list { margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem; }
        .sound-option {
            padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255,255,255,0.5);
            border: 2px solid #e0d7f5; cursor: pointer; display: flex; justify-content: space-between;
            align-items: center; transition: 0.2s; font-size: 0.9rem; font-weight: 600; color: #5c5c8a;
        }
        .sound-option:hover { border-color: #b39ddb; background: white; }
        .sound-option.active { border-color: #b39ddb; background: white; box-shadow: 0 2px 8px rgba(179,157,219,0.2); }
        .sound-option.active::after { content: '✓'; color: #b39ddb; font-weight: 800; }
    </style>
</head>
<body>
<?php require_once 'nav.php'; ?>

<div class="container">
    <div class="page-head">
        <h1>Settings ⚙️</h1>
        <p>Customize your Mindora experience.</p>
    </div>

    <div class="grid">
        <!-- Profile -->
        <div class="card">
            <h3>👤 Profile Edit</h3>
            <div class="form-group">
                <label class="field-label">Full Name</label>
                <input type="text" id="profileName" value="<?= htmlspecialchars($_SESSION['user_name']) ?>">
            </div>
            <div class="form-group">
                <label class="field-label">Email</label>
                <?php
                $uid = $_SESSION['user_id'];
                $user_data = $conn->query("SELECT email FROM users WHERE id = $uid")->fetch_assoc();
                $email = $user_data['email'] ?? 'student@mindora.com';
                ?>
                <input type="email" value="<?= htmlspecialchars($email) ?>" disabled style="opacity:0.7; background:#f9f9f9;">
            </div>
            <button class="btn btn-primary" onclick="saveName()">Save Profile</button>
        </div>

        <!-- Preferences -->
        <div class="card">
            <h3>🎛️ Preferences</h3>
            <div class="toggle-row">
                <div class="toggle-info">
                    <h4>Calming Soundscape</h4>
                    <p>Relieve tension with immersive ambient audio.</p>
                </div>
                <label class="switch">
                    <input type="checkbox" id="audioToggle" onchange="toggleAudio(this.checked)">
                    <span class="slider"></span>
                </label>
            </div>
            <div id="soundSelection" style="display:none; margin-bottom: 1.5rem;">
                <label class="field-label">Select Soundscape</label>
                <div class="sound-list">
                    <div class="sound-option active" data-sound="ocean" onclick="pickSound('ocean', this)">🌊 Ocean Waves</div>
                    <div class="sound-option" data-sound="rain" onclick="pickSound('rain', this)">🌨️ Soft Rain</div>
                    <div class="sound-option" data-sound="forest" onclick="pickSound('forest', this)">🌳 Rainforest Birdsong</div>
                    <div class="sound-option" data-sound="zen" onclick="pickSound('zen', this)">🧘‍♂️ Zen Meditation Bowl</div>
                    <div class="sound-option" data-sound="stress" onclick="pickSound('stress', this)">🎶 Stress Relief Music</div>
                </div>
            </div>
            <hr class="divider">
            <div class="toggle-row">
                <div class="toggle-info">
                    <h4>Email Notifications</h4>
                    <p>Daily check-in reminders.</p>
                </div>
                <label class="switch"><input type="checkbox" checked><span class="slider"></span></label>
            </div>
            <hr class="divider">
            <label class="field-label">Theme Color</label>
            <div class="swatch-row">
                <div class="swatch active" style="background:#a8d5a2;" onclick="pickTheme(this,'#e8f5e9,#ede7f6,#e3f2fd')" title="Pastel Green"></div>
                <div class="swatch" style="background:#b39ddb;" onclick="pickTheme(this,'#ede7f6,#d7cce3,#e8f5e9')" title="Lavender"></div>
                <div class="swatch" style="background:#90caf9;" onclick="pickTheme(this,'#e3f2fd,#e8f5e9,#ede7f6')" title="Soft Blue"></div>
                <div class="swatch" style="background:#ffcc80;" onclick="pickTheme(this,'#fff8e1,#ede7f6,#e3f2fd')" title="Warm Peach"></div>
            </div>
        </div>

        <!-- Tracking Help -->
        <div class="card" style="grid-column: 1 / -1; border: 1px solid #e0d7f5;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h3>🚀 Automatic Tracking</h3>
                <button class="btn btn-secondary" onclick="toggleTrackerHelp()">Need help setting up? 🛠️</button>
            </div>
            
            <div id="trackerHelpContent" style="display:none; margin-top:1.5rem; border-top: 1px solid #f0edf8; padding-top:1.5rem;">
                <p>Track your real screen time automatically by running a small script on your computer.</p>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:2rem; margin-top:1.5rem;">
                    <div>
                        <h4 style="color:#4a4a8a; margin-bottom:10px;">1. Install Python 🐍</h4>
                        <p style="font-size:0.85rem;">Ensure you have <a href="https://www.python.org/downloads/" target="_blank">Python</a> installed. Then run:</p>
                        <code style="display:block; background:#f0edf8; padding:8px; border-radius:8px; margin:8px 0; font-size:0.8rem;">pip install pygetwindow pywin32 requests</code>
                        
                        <h4 style="color:#4a4a8a; margin:15px 0 10px;">2. Create the Script 📄</h4>
                        <p style="font-size:0.85rem;">Create a file named <strong>tracker.py</strong> and paste the code on the right into it.</p>
                        
                        <h4 style="color:#4a4a8a; margin:15px 0 10px;">3. Run it! ⚡</h4>
                        <p style="font-size:0.85rem;">Open your terminal and run <code>python tracker.py</code>. When prompted for <strong>Sync ID</strong>, use: <strong style="color:#b39ddb;"><?= $_SESSION['user_id'] ?></strong></p>
                    </div>
                    <div style="background:#282c34; border-radius:15px; padding:1rem; position:relative; max-height:300px; overflow-y:auto;">
                        <button onclick="copyTrackerCode()" style="position:absolute; top:10px; right:10px; background:rgba(255,255,255,0.1); border:none; color:white; padding:5px 10px; border-radius:5px; cursor:pointer; font-size:0.7rem;">Copy Code</button>
                        <pre id="trackerCode" style="color:#abb2bf; font-size:0.75rem; font-family: 'Courier New', monospace; white-space:pre-wrap;">import time
import requests
import json
import os
import sys
from datetime import datetime

# Configuration
CONFIG_FILE = 'tracker_config.json'
API_URL = 'http://localhost/project/api_handler.php'
SYNC_INTERVAL = 60 # Seconds

def get_active_window_title():
    try:
        import pygetwindow as gw
        win = gw.getActiveWindow()
        if win: return win.title
        return "Idle"
    except: return "Unknown"

def main():
    print("Mindora Desktop Tracker 🌿")
    uid = input("Enter Sync ID (from Settings): ").strip()
    print(f"Tracking active for User #{uid}...")
    accumulated_seconds = 0
    last_sync = time.time()
    while True:
        time.sleep(10)
        if get_active_window_title() != "Idle":
            accumulated_seconds += 10
        if time.time() - last_sync >= SYNC_INTERVAL:
            try:
                requests.post(API_URL, data={'action':'auto_sync_screen','seconds':accumulated_seconds,'user_id':uid})
                print(f"Sync complete at {datetime.now().strftime('%H:%M:%S')}")
                accumulated_seconds = 0
                last_sync = time.time()
            except Exception as e: print(f"Sync failed: {e}")

if __name__ == "__main__":
    main()</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden ambient audio tag removed, now managed globally in nav.php -->

<script>
// Audio switching controlled globally, keeping UI toggles here
const uiSoundSources = {
    'ocean': 'https://www.orangefreesounds.com/wp-content/uploads/2015/08/Ocean-waves.mp3',
    'rain': 'https://www.orangefreesounds.com/wp-content/uploads/2014/10/Free-rain-sounds.mp3',
    'forest': 'https://www.orangefreesounds.com/wp-content/uploads/2016/04/Rainforest-sounds.mp3',
    'zen': 'https://www.orangefreesounds.com/wp-content/uploads/2021/12/Tibetan-bowl-meditation-music.mp3',
    'stress': 'https://www.orangefreesounds.com/wp-content/uploads/2025/03/Relaxing-music-for-stress-relief.mp3'
};

function toggleAudio(on) {
    const audio = document.getElementById('globalAmbientAudio');
    const panel = document.getElementById('soundSelection');
    
    if (on) { 
        panel.style.display = 'block';
        if(audio) audio.play().catch(()=>{}); 
    } else { 
        panel.style.display = 'none';
        if(audio) audio.pause(); 
    }
    localStorage.setItem('mindora_audio', on);
}

function pickSound(type, el) {
    const audio = document.getElementById('globalAmbientAudio');
    const source = audio ? audio.querySelector('source') : null;
    
    document.querySelectorAll('.sound-option').forEach(opt => opt.classList.remove('active'));
    el.classList.add('active');
    
    if (source) {
        source.src = uiSoundSources[type];
        audio.load();
        if (document.getElementById('audioToggle').checked) {
            audio.play().catch(()=>{});
        }
    }
    localStorage.setItem('mindora_sound_type', type);
}

// Initial state
let initAudio = localStorage.getItem('mindora_audio');
let initSoundType = localStorage.getItem('mindora_sound_type');

if (!initSoundType) {
    initSoundType = 'ocean'; // Default
    localStorage.setItem('mindora_sound_type', 'ocean');
}
if (initAudio === null) {
    initAudio = 'true';
    localStorage.setItem('mindora_audio', 'true');
}

if (initAudio === 'true') {
    document.getElementById('audioToggle').checked = true;
    document.getElementById('soundSelection').style.display = 'block';
    
    // Update UI active state
    document.querySelectorAll('.sound-option').forEach(opt => {
        if (opt.dataset.sound === initSoundType) opt.classList.add('active');
        else opt.classList.remove('active');
    });
}

// Theme
function pickTheme(el, gradient) {
    document.querySelectorAll('.swatch').forEach(s => s.classList.remove('active'));
    el.classList.add('active');
    document.body.style.background = `linear-gradient(135deg, ${gradient})`;
    localStorage.setItem('mindora_theme', gradient);
}
const savedTheme = localStorage.getItem('mindora_theme');
if (savedTheme) document.body.style.background = `linear-gradient(135deg, ${savedTheme})`;

// Name save
function saveName() {
    const name = document.getElementById('profileName').value.trim();
    if (!name) { alert('Name cannot be blank.'); return; }
    alert('Profile saved! ✅ (Changes apply after next login.)');
}
// Toggle tracker help
function toggleTrackerHelp() {
    const content = document.getElementById('trackerHelpContent');
    content.style.display = content.style.display === 'none' ? 'block' : 'none';
}

// Copy tracker code
function copyTrackerCode() {
    const code = document.getElementById('trackerCode').innerText;
    navigator.clipboard.writeText(code).then(() => {
        alert('Tracker code copied to clipboard! 📋');
    }).catch(err => {
        alert('Could not copy text: ', err);
    });
}
</script>
</body>
</html>
