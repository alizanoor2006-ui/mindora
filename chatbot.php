<?php
session_start();
require_once 'config.php';
$pageTitle = 'Chatbot';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindora | AI Chatbot</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #e8f5e9, #ede7f6, #e3f2fd); min-height: 100vh; display: flex; flex-direction: column; }
        .container { padding: 2rem; max-width: 800px; margin: 0 auto; flex: 1; display: flex; flex-direction: column; width: 100%; }
        .page-head { margin-bottom: 1rem; }
        .page-head h1 { font-size: 1.8rem; color: #4a4a8a; }
        .page-head p  { color: #888; margin-top: 0.3rem; }
        .chat-wrap {
            flex: 1; display: flex; flex-direction: column;
            background: rgba(255,255,255,0.7); backdrop-filter: blur(16px);
            border-radius: 24px; box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            overflow: hidden; min-height: 420px; max-height: 65vh;
        }
        .chat-window { flex: 1; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
        .msg { display: flex; gap: 0.75rem; align-items: flex-end; }
        .msg.user { flex-direction: row-reverse; }
        .avatar {
            width: 38px; height: 38px; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            font-size: 1.2rem; flex-shrink: 0;
        }
        .avatar.bot  { background: #d7f5d7; }
        .avatar.user { background: #ede7f6; }
        .bubble {
            padding: 0.75rem 1.1rem; border-radius: 18px;
            max-width: 75%; font-size: 0.95rem; line-height: 1.55;
        }
        .bubble.bot  { background: rgba(255,255,255,0.9); color: #4a4a8a; border-radius: 18px 18px 18px 4px; border: 1px solid #e0d7f5; }
        .bubble.user { background: linear-gradient(135deg,#a8d5a2,#b39ddb); color: white; border-radius: 18px 18px 4px 18px; }
        .input-area {
            padding: 1rem 1.5rem; border-top: 1px solid rgba(0,0,0,0.05);
            background: rgba(255,255,255,0.6);
            display: flex; gap: 0.75rem; align-items: center;
        }
        .input-area input {
            flex: 1; padding: 0.75rem 1rem; border-radius: 50px;
            border: 2px solid #e0d7f5; font-size: 1rem; outline: none;
            background: rgba(255,255,255,0.9); transition: border 0.2s;
        }
        .input-area input:focus { border-color: #b39ddb; }
        .icon-btn {
            width: 44px; height: 44px; border-radius: 50%;
            border: none; cursor: pointer; font-size: 1.2rem;
            display: flex; align-items: center; justify-content: center;
            transition: transform 0.2s;
        }
        .icon-btn:hover { transform: scale(1.1); }
        .mic-btn  { background: rgba(255,255,255,0.8); border: 2px solid #e0d7f5; }
        .send-btn { background: linear-gradient(135deg,#a8d5a2,#b39ddb); color: white; }
        .typing { opacity: 0.6; font-style: italic; font-size: 0.85rem; color: #777; display: none; padding: 0.5rem 1.5rem; }

        /* History Sidebar */
        .history-sidebar {
            position: fixed; top: 0; right: -320px; width: 320px; height: 100vh;
            background: white; box-shadow: -4px 0 24px rgba(0,0,0,0.1);
            transition: 0.3s ease; z-index: 2000; padding: 2rem 1.5rem;
            display: flex; flex-direction: column;
        }
        .history-sidebar.open { right: 0; }
        .history-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .history-list { flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 1rem; }
        .history-item { padding: 1rem; border-radius: 12px; background: #f9f9fb; border: 1px solid #eee; font-size: 0.85rem; }
        .history-item .q { font-weight: 700; color: #4a4a8a; margin-bottom: 4px; }
        .history-item .a { color: #666; }
        .history-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.2); backdrop-filter: blur(2px);
            display: none; z-index: 1000;
        }
        .history-overlay.open { display: block; }
    </style>
</head>
<body>
<?php require_once 'nav.php'; ?>

<div class="container">
    <div class="page-head">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <a href="student_dashboard.php" style="text-decoration:none; color:#4a4a8a; font-weight:600; display:inline-block; transition:0.2s;" onmouseover="this.style.color='#b39ddb'; this.style.transform='translateX(-5px)'" onmouseout="this.style.color='#4a4a8a'; this.style.transform='translateX(0)'">← Back to Home</a>
            <button class="btn btn-secondary" onclick="toggleHistory()" style="padding: 0.5rem 1rem; border-radius:12px; font-size: 0.85rem;">🕰️ View History</button>
        </div>
        <h1 style="margin-top:1rem;">Your AI Companion 💬 <span id="aiStatus" style="font-size: 0.7rem; vertical-align: middle; padding: 2px 8px; border-radius: 10px; background: #eee; color: #888;">Checking AI...</span></h1>
        <p>A safe, non-judgmental space to talk about how you're feeling.</p>
    </div>

    <div class="chat-wrap">
        <div class="chat-window" id="chatWindow">
            <div class="msg">
                <div class="avatar bot">🤖</div>
                <div class="bubble bot">Hello 🌿 I'm here for you. How are you feeling today?</div>
            </div>
        </div>
        <div class="typing" id="typing">Mindora is thinking…</div>
        <div class="input-area">
            <button class="icon-btn mic-btn" id="micBtn" title="Voice input">🎤</button>
            <input type="text" id="chatInput" placeholder="Type your message here..." onkeydown="if(event.key==='Enter') sendMessage()">
            <button class="icon-btn send-btn" onclick="sendMessage()" title="Send">➤</button>
        </div>
    </div>
</div>

<!-- History Sidebar -->
<div class="history-overlay" id="historyOverlay" onclick="toggleHistory()"></div>
<div class="history-sidebar" id="historySidebar">
    <div class="history-header">
        <h3 style="color:#4a4a8a;">Chat History</h3>
        <button onclick="toggleHistory()" style="background:none; border:none; font-size:1.5rem; cursor:pointer;">✕</button>
    </div>
    <div class="history-list" id="historyList">
        <p style="color:#aaa; text-align:center; padding:2rem;">Loading history...</p>
    </div>
</div>

<script>
let chatHistory = [];

async function sendMessage() {
    const input = document.getElementById('chatInput');
    const msg   = input.value.trim();
    if (!msg) return;
    input.value = '';

    appendMsg('user', msg);

    const typing = document.getElementById('typing');
    typing.style.display = 'block';

    try {
        const res  = await fetch(`http://${window.location.hostname}:5000/api/chatbot`, {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ 
                message: msg,
                history: chatHistory 
            })
        });
        const data = await res.json();
        typing.style.display = 'none';
        appendMsg('bot', data.response);

        // Update local memory (Limit to last 6 turns to keep context efficient)
        chatHistory.push({ role: 'user', content: msg });
        chatHistory.push({ role: 'assistant', content: data.response });
        if (chatHistory.length > 12) chatHistory = chatHistory.slice(-12);

        // Save conversation to DB
        const formData = new FormData();
        formData.append('action', 'save_chat');
        formData.append('message', msg);
        formData.append('response', data.response);
        await fetch('api_handler.php', { method: 'POST', body: formData });

    } catch {
        typing.style.display = 'none';
        appendMsg('bot', "I'm having trouble connecting right now 🌸 Please make sure the AI server is running on port 5000.");
    }
}

function appendMsg(sender, text) {
    const win = document.getElementById('chatWindow');
    const div = document.createElement('div');
    div.className = 'msg ' + sender;
    div.innerHTML = `
        <div class="avatar ${sender}">${sender === 'bot' ? '🤖' : '👤'}</div>
        <div class="bubble ${sender}">${text}</div>`;
    win.appendChild(div);
    win.scrollTop = win.scrollHeight;
}

// Microphone (Web Speech API)
const micBtn = document.getElementById('micBtn');
if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    const rec = new SR();
    rec.lang = 'en-US';
    rec.onresult = e => {
        document.getElementById('chatInput').value = e.results[0][0].transcript;
        micBtn.innerText = '🎤';
    };
    rec.onend = () => { micBtn.innerText = '🎤'; };
    micBtn.addEventListener('click', () => { rec.start(); micBtn.innerText = '🔴'; });
} else {
    micBtn.title = 'Voice input not supported in this browser';
    micBtn.style.opacity = '0.4';
}
async function checkAIHealth() {
    const statusEl = document.getElementById('aiStatus');
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
        statusEl.innerText = "○ AI Offline (Check Firewall)";
        statusEl.style.background = "#ffdce0";
        statusEl.style.color = "#c62828";
    }
}
checkAIHealth();
setInterval(checkAIHealth, 60000);

// History Logic
function toggleHistory() {
    const sidebar = document.getElementById('historySidebar');
    const overlay = document.getElementById('historyOverlay');
    const isOpen = sidebar.classList.contains('open');
    
    if (!isOpen) {
        fetchHistory();
    }
    
    sidebar.classList.toggle('open');
    overlay.classList.toggle('open');
}

async function fetchHistory() {
    const list = document.getElementById('historyList');
    try {
        const res = await fetch('api_handler.php?action=get_chat_history');
        const data = await res.json();
        
        if (data.status === 'success' && data.data.length > 0) {
            list.innerHTML = data.data.map(item => `
                <div class="history-item">
                    <div class="q">You: ${item.message}</div>
                    <div class="a">Mindora: ${item.response}</div>
                    <small style="display:block; margin-top:5px; color:#bbb; font-size:0.7rem;">${new Date(item.timestamp).toLocaleDateString()}</small>
                </div>
            `).join('');
        } else {
            list.innerHTML = '<p style="color:#aaa; text-align:center; padding:2rem;">No chat history found.</p>';
        }
    } catch {
        list.innerHTML = '<p style="color:#e57373; text-align:center; padding:2rem;">Error loading history.</p>';
    }
}
</script>
</body>
</html>
