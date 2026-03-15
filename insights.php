<?php
session_start();
require_once 'config.php';

// Guard: logged in users only
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Auto-fix: Create insights_content if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS insights_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category ENUM('Article', 'Video') NOT NULL,
    topic ENUM('Healing', 'Self Growth', 'Grounding', 'Anxiety', 'Productivity', 'Relaxation') DEFAULT 'Self Growth',
    url VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Ensure topic column exists if table was created earlier without it
$check_topic = $conn->query("SHOW COLUMNS FROM insights_content LIKE 'topic'");
if ($check_topic->num_rows === 0) {
    $conn->query("ALTER TABLE insights_content ADD COLUMN topic ENUM('Healing', 'Self Growth', 'Grounding', 'Anxiety', 'Productivity', 'Relaxation') DEFAULT 'Self Growth'");
}

// Fetch dynamic content
$stmt = $conn->prepare("SELECT * FROM insights_content ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$dynamicContentCount = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindora | Insights</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #e8f5e9, #ede7f6, #e3f2fd); min-height: 100vh; }
        .container { padding: 2rem; max-width: 1100px; margin: 0 auto; }
        .page-head { margin-bottom: 1.5rem; }
        .page-head h1 { font-size: 1.8rem; color: #4a4a8a; }
        .page-head p  { color: #888; margin-top: 0.3rem; }
        
        .search-bar {
            background: rgba(255,255,255,0.7); backdrop-filter: blur(16px);
            border-radius: 50px; padding: 0.9rem 1.5rem;
            display: flex; align-items: center; gap: 0.75rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07); margin-bottom: 1.5rem;
        }
        .search-bar input { flex:1; border:none; background:transparent; font-size:1rem; outline:none; color:#4a4a8a; }
        .search-bar span  { font-size: 1.4rem; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; }
        .card {
            background: rgba(255,255,255,0.7); backdrop-filter: blur(16px);
            border-radius: 20px; padding: 1.5rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            display: flex; flex-direction: column;
        }
        .card .tag { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
        .card h3  { color: #4a4a8a; margin-bottom: 1rem; }
        .card p   { color: #777; font-size: 0.9rem; line-height: 1.6; margin-bottom: 1rem; }
        
        .btn {
            display: inline-block; padding: 0.55rem 1.3rem;
            border-radius: 50px; font-size: 0.9rem; font-weight: 700;
            cursor: pointer; border: none; text-decoration: none;
            transition: transform 0.2s; margin-top: auto; text-align: center;
        }
        .btn-primary  { background: linear-gradient(135deg,#a8d5a2,#b39ddb); color: white; }
        .btn-secondary{ background: rgba(255,255,255,0.7); color: #7c6ab5; border: 2px solid #e0d7f5; }
        .btn:hover { transform: translateY(-2px); }
        
        .video-thumb {
            background: rgba(0,0,0,0.06); height: 140px; border-radius: 14px;
            display: flex; justify-content: center; align-items: center;
            font-size: 3rem; cursor: pointer; margin-bottom: 1rem;
            transition: background 0.2s;
        }
        .video-thumb:hover { background: rgba(0,0,0,0.1); }

        /* Category Buttons */
        .cat-btn {
            padding: 0.6rem 1.2rem; border-radius: 50px; background: white;
            border: 2px solid #e0d7f5; color: #4a4a8a; font-weight: 600;
            cursor: pointer; transition: 0.2s; white-space: nowrap;
            font-size: 0.9rem;
        }
        .cat-btn:hover { border-color: #b39ddb; color: #b39ddb; }
        .cat-btn.active { background: #b39ddb; border-color: #b39ddb; color: white; }
    </style>
</head>
<body>
<?php require_once 'nav.php'; ?>

<div class="container">
    <div class="page-head">
        <a href="student_dashboard.php" style="text-decoration:none; color:#4a4a8a; font-weight:600; display:inline-block; margin-bottom:1rem; transition:0.2s;" onmouseover="this.style.color='#b39ddb'; this.style.transform='translateX(-5px)'" onmouseout="this.style.color='#4a4a8a'; this.style.transform='translateX(0)'">← Back to Home</a>
        <h1>Resources &amp; Insights 🔎</h1>
        <p>Curated content to support your mental wellness journey.</p>
    </div>

    <!-- AI Wisdom Section -->
    <div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, rgba(168, 213, 162, 0.1), rgba(179, 157, 219, 0.1)); border: 1px solid rgba(179, 157, 219, 0.3);">
        <div style="display:flex; align-items:center; gap:1rem;">
            <div style="font-size:2rem;">✨</div>
            <div>
                <h4 style="color:#4a4a8a; margin-bottom:0.2rem;">Daily AI Wisdom</h4>
                <p id="ai-wisdom-text" style="font-style:italic; color:#5c5c8a; font-size:1.05rem;">"Loading your daily dose of peace..."</p>
            </div>
        </div>
    </div>

    <!-- AI Personalized Recommendations Section -->
    <div id="aiRecommendationsSection" style="margin-bottom: 2.5rem; display:none;">
        <h2 style="color: #4a4a8a; margin-bottom: 1.2rem; display: flex; align-items: center; gap: 10px;">
            AI Curated for You 🤖 <span style="font-size: 0.7rem; background: #e0d7f5; padding: 4px 10px; border-radius: 20px; color: #7c6ab5; font-weight: 800;">PERSONALIZED</span>
        </h2>
        <div class="grid" id="aiRecommendationsGrid">
            <!-- AI cards will be injected here -->
        </div>
    </div>

    <div class="search-bar">
        <span>🔍</span>
        <input type="text" id="searchInput" placeholder="Search resources..." oninput="filterCards()">
    </div>

    <!-- Category Filters -->
    <div style="display:flex; gap:0.6rem; overflow-x:auto; padding-bottom:1.5rem; scrollbar-width:none;">
        <button class="cat-btn active" onclick="setCategory('all', this)">All</button>
        <?php 
        $topics = ['Healing', 'Self Growth', 'Grounding', 'Anxiety', 'Productivity', 'Relaxation'];
        foreach($topics as $t): ?>
            <button class="cat-btn" onclick="setCategory('<?= $t ?>', this)"><?= $t ?></button>
        <?php endforeach; ?>
    </div>

    <div class="grid" id="cardsGrid">
        <!-- Persistent Hotline Card -->
        <div class="card" style="border: 2px solid #b39ddb; background: rgba(179, 157, 219, 0.05);">
            <div class="tag" style="color:#b39ddb;">📞 Immediate Support</div>
            <h3>Student Help Hotline</h3>
            <p>If you're feeling overwhelmed and need someone to talk to right now, help is just a call away.</p>
            <a href="tel:1800599019" class="btn btn-primary">Call iCall India 📞</a>
        </div>

        <!-- Dynamic Content from Database -->
        <?php if ($dynamicContentCount === 0): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: #888;">
                <p>No new resources posted yet. Check back soon! 🌸</p>
            </div>
        <?php else: ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <?php $topic = $row['topic'] ?? 'Self Growth'; ?>
                <div class="card" data-topic="<?= htmlspecialchars($topic) ?>">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <?php if ($row['category'] === 'Video'): ?>
                            <div class="tag" style="color:#a8d5a2;">▶️ Video</div>
                        <?php else: ?>
                            <div class="tag" style="color:#64b5f6;">📄 Article</div>
                        <?php endif; ?>
                        <span style="font-size:0.7rem; background:rgba(0,0,0,0.05); padding:2px 8px; border-radius:4px; color:#666; font-weight:700;"><?= htmlspecialchars($topic) ?></span>
                    </div>
                    
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    
                    <?php if ($row['category'] === 'Video'): ?>
                        <div class="video-thumb" onclick="window.open('<?= htmlspecialchars($row['url']) ?>', '_blank')">▶️</div>
                        <a href="<?= htmlspecialchars($row['url']) ?>" target="_blank" class="btn btn-secondary">Watch Video</a>
                    <?php else: ?>
                        <p>Learn more about wellness in this helpful guide.</p>
                        <a href="<?= htmlspecialchars($row['url']) ?>" target="_blank" class="btn btn-secondary">Read Article →</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<script>
let currentCategory = 'all';

function setCategory(cat, el) {
    currentCategory = cat;
    document.querySelectorAll('.cat-btn').forEach(btn => btn.classList.remove('active'));
    el.classList.add('active');
    filterCards();
}

function filterCards() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#cardsGrid .card').forEach(card => {
        const text = card.innerText.toLowerCase();
        const cat = card.getAttribute('data-topic') || 'all';
        
        const matchesSearch = text.includes(q);
        const matchesCat = (currentCategory === 'all' || cat === currentCategory);
        
        card.style.display = (matchesSearch && matchesCat) ? 'flex' : 'none';
    });
}
async function fetchQuickTip() {
    try {
        const res = await fetch(`http://${window.location.hostname}:5000/api/quick_tip`);
        const data = await res.json();
        document.getElementById('ai-wisdom-text').innerText = data.tip;
    } catch (e) {
        document.getElementById('ai-wisdom-text').innerText = "Take a deep breath. You're doing better than you think. 🌿";
    }
}

async function fetchAIRecommendations() {
    const section = document.getElementById('aiRecommendationsSection');
    const grid = document.getElementById('aiRecommendationsGrid');
    
    try {
        // 1. Get comprehensive user data
        const dataRes = await fetch('api_handler.php?action=get_comprehensive_data');
        const userData = await dataRes.json();
        
        if (userData.status !== 'success') throw new Error("Could not fetch user context");

        // 2. Clear grid and show section
        grid.innerHTML = '<div style="grid-column: 1/-1; text-align:center; padding: 2rem;">Generating your personalized journey... ✨</div>';
        section.style.display = 'block';

        // 3. Get AI suggestions
        const aiRes = await fetch(`http://${window.location.hostname}:5000/api/curated_insights`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(userData.data)
        });
        const suggestions = await aiRes.json();
        
        // 4. Render
        if (suggestions.length === 0) {
            section.style.display = 'none';
            return;
        }

        grid.innerHTML = '';
        suggestions.forEach(item => {
            // Find the full resource from the available list to get the URL
            const fullRes = userData.data.available_resources.find(r => r.id == item.id) || item;
            
            const card = document.createElement('div');
            card.className = 'card';
            card.style.border = '1px solid rgba(179, 157, 219, 0.4)';
            card.style.background = 'rgba(255,255,255,0.9)';
            
            const typeLabel = fullRes.category === 'Video' ? '▶️ AI SELECTED VIDEO' : '📄 AI SELECTED ARTICLE';
            const color = fullRes.category === 'Video' ? '#a8d5a2' : '#64b5f6';
            
            // Re-fetch URL from table if not present (it's in userData.data.available_resources now)
            // Wait, I need to make sure URL is in available_resources from PHP
            const url = fullRes.url || "#";

            card.innerHTML = `
                <div class="tag" style="color:${color};">${typeLabel}</div>
                <h3 style="font-size:1.1rem;">${fullRes.title}</h3>
                <p style="font-size:0.85rem; color:#666;">This was selected by Mindora based on your latest activity.</p>
                <a href="${url}" target="_blank" class="btn btn-primary" style="margin-top:auto; font-size:0.8rem;">Open Library Resource</a>
            `;
            grid.appendChild(card);
        });

    } catch (err) {
        console.error("AI Rec Error:", err);
        section.style.display = 'none';
    }
}

fetchQuickTip();
fetchAIRecommendations();
</script>
</body>
</html>
