<?php
session_start();
require_once 'config.php';

// Guard: admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Auto-fix: Add is_blocked column if missing
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'is_blocked'");
if ($check->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN is_blocked BOOLEAN DEFAULT FALSE");
}

// Handle Actions
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // 1. Block/Unblock
    if ($_POST['action'] === 'toggle_block') {
        $uid = intval($_POST['user_id']);
        $status = intval($_POST['status']);
        $stmt = $conn->prepare("UPDATE users SET is_blocked = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $uid);
        $stmt->execute();
        $msg = $status ? "User blocked 🚫" : "User unblocked ✅";
    }

    // 2. Manage Resources (Moved from Dashboard)
    if ($_POST['action'] === 'add_content') {
        $title = $_POST['title']; $cat = $_POST['category']; $url = $_POST['url']; $topic = $_POST['topic'];
        $stmt = $conn->prepare("INSERT INTO insights_content (title, category, url, topic) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $cat, $url, $topic);
        $stmt->execute();
        $msg = "Resource published ✨";
    }
    if ($_POST['action'] === 'delete_content') {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM insights_content WHERE id = $id");
        $msg = "Resource removed";
    }

    // 3. Export CSV
    if ($_POST['action'] === 'export_csv') {
        $table = $_POST['table'];
        $filename = "mindora_export_" . $table . "_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if ($table === 'mood') {
            fputcsv($output, ['User ID', 'Mood', 'Date']);
            $res = $conn->query("SELECT user_id, mood, date FROM mood_data");
        } elseif ($table === 'stress') {
            fputcsv($output, ['User ID', 'Type', 'Score', 'Level', 'Date']);
            $res = $conn->query("SELECT user_id, type, score, stress_level, date FROM quiz_results");
        } elseif ($table === 'journals') {
            // PRIVACY: Only export sentiment scores and labels, NOT the content
            fputcsv($output, ['User ID', 'Sentiment Score', 'Sentiment Label', 'Date']);
            $res = $conn->query("SELECT user_id, sentiment_score, sentiment_label, date FROM journals");
        }
        
        while($row = $res->fetch_assoc()) fputcsv($output, $row);
        fclose($output);
        exit();
    }
}

// Fetch Data
$students = $conn->query("SELECT id, name, email, is_blocked FROM users WHERE role = 'student' ORDER BY name ASC");
$resources = $conn->query("SELECT * FROM insights_content ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindora | Admin Controls</title>
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
        .container { padding: 2rem; max-width: 1200px; margin: 0 auto; }
        
        .page-head { margin-bottom: 2rem; }
        .page-head h1 { font-size: 1.8rem; color: #4a4a8a; }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 1.5rem; }
        .card {
            background: rgba(255,255,255,0.7); backdrop-filter: blur(16px);
            border-radius: 20px; padding: 1.5rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08); margin-bottom: 1.5rem;
        }
        .card h3 { color: #4a4a8a; margin-bottom: 1.2rem; display: flex; align-items: center; gap: 0.5rem; }

        table { width: 100%; border-collapse: collapse; margin-top: 0.5rem; }
        th, td { text-align: left; padding: 0.75rem; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        th { color: #888; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }

        .btn {
            display: inline-block; padding: 0.6rem 1.2rem;
            border-radius: 50px; font-size: 0.85rem; font-weight: 700;
            cursor: pointer; border: none; transition: all 0.2s; text-decoration: none;
        }
        .btn-primary { background: linear-gradient(135deg,#a8d5a2,#b39ddb); color: white; }
        .btn-outline { border: 2px solid #b39ddb; color: #b39ddb; background: transparent; }
        .btn-danger  { background: #ffebee; color: #c62828; }
        .btn-block   { background: #333; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }

        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.4rem; color: #4a4a8a; font-weight: 600; font-size: 0.9rem; }
        .form-group input, .form-group select {
            width: 100%; padding: 0.6rem 0.8rem; border-radius: 10px;
            border: 2px solid #e0d7f5; outline: none; background: rgba(255,255,255,0.5);
        }

        .export-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
        .export-btn { 
            background: #fff; border: 2px solid #e0d7f5; padding: 1rem; border-radius: 15px;
            text-align: center; cursor: pointer; transition: 0.2s;
        }
        .export-btn:hover { border-color: #b39ddb; background: #fdfbff; }
        .export-btn div { font-size: 1.5rem; margin-bottom: 0.3rem; }
        .export-btn span { font-size: 0.8rem; font-weight: 700; color: #4a4a8a; }

        .toast {
            position: fixed; bottom: 2rem; right: 2rem; background: #4a4a8a; color: white;
            padding: 1rem 2rem; border-radius: 50px; box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            animation: slideUp 0.3s ease-out;
        }
        @keyframes slideUp { from { transform: translateY(100%); opacity:0; } to { transform: translateY(0); opacity:1; } }
    </style>
</head>
<body>
<nav>
    <div class="nav-brand">Mind<span>ora</span> 🌿 <small style="font-size:0.75rem; border:1px solid #7c6ab5; color:#7c6ab5; padding:2px 8px; border-radius:6px; margin-left:6px;">Controls</small></div>
    <div>
        <a href="dashboard.php" style="text-decoration:none; color:#4a4a8a; font-weight:600; margin-right:20px;">📊 Dashboard</a>
        <a href="admin_controls.php" style="text-decoration:none; color:#b39ddb; font-weight:800; margin-right:20px;">⚙️ Controls</a>
        <a href="logout.php" style="text-decoration:none; color:#e57373; font-weight:600;">🚪 Logout</a>
    </div>
</nav>

<div class="container">
    <div class="page-head">
        <h1>Advanced Admin Controls</h1>
        <p>Manage users, resources, and generate campus reports.</p>
    </div>

    <?php if($msg): ?><div class="toast"><?= $msg ?></div><?php endif; ?>

    <div class="grid">
        <!-- User Management -->
        <div class="card" style="grid-column: span 2;">
            <h3>👥 Student Management</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($u = $students->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <?php if($u['is_blocked']): ?>
                                <span style="color:#c62828; font-weight:700;">🚫 Blocked</span>
                            <?php else: ?>
                                <span style="color:#66bb6a; font-weight:700;">✅ Active</span>
                            <?php endif; ?>
                        </td>
                        <td style="display:flex; gap:0.5rem;">
                            <a href="user_activity.php?id=<?= $u['id'] ?>" class="btn btn-outline">Wellness Metrics</a>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="action" value="toggle_block">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="status" value="<?= $u['is_blocked'] ? 0 : 1 ?>">
                                <button type="submit" class="btn <?= $u['is_blocked'] ? 'btn-primary' : 'btn-block' ?>">
                                    <?= $u['is_blocked'] ? 'Unblock' : 'Block' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Export Reports -->
        <div class="card">
            <h3>📥 Data Export (CSV)</h3>
            <p style="font-size:0.85rem; color:#888; margin-bottom:1.5rem;">Privacy Note: Journal content is excluded from exports. Only sentiment labels are included.</p>
            <div class="export-grid">
                <form method="POST" action="admin_controls.php">
                    <input type="hidden" name="action" value="export_csv">
                    <input type="hidden" name="table" value="mood">
                    <button type="submit" class="export-btn" style="width:100%;"><div>📉</div><span>Mood History</span></button>
                </form>
                <form method="POST" action="admin_controls.php">
                    <input type="hidden" name="action" value="export_csv">
                    <input type="hidden" name="table" value="stress">
                    <button type="submit" class="export-btn" style="width:100%;"><div>⚡</div><span>Stress Scores</span></button>
                </form>
                <form method="POST" action="admin_controls.php">
                    <input type="hidden" name="action" value="export_csv">
                    <input type="hidden" name="table" value="journals">
                    <button type="submit" class="export-btn" style="width:100%;"><div>🧠</div><span>Sentiment</span></button>
                </form>
            </div>
        </div>

        <!-- Resource Manager (Moved here) -->
        <div class="card">
            <h3>📄 Resource Manager</h3>
            <form method="POST" style="margin-bottom:1.5rem;">
                <input type="hidden" name="action" value="add_content">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required placeholder="Quick Stress Reliever">
                </div>
                <div class="form-group">
                    <select name="category">
                        <option value="Article">Article</option>
                        <option value="Video">Video (YouTube)</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="url" name="url" required placeholder="https://...">
                </div>
                <div class="form-group">
                    <select name="topic" required>
                        <option value="" disabled selected>Select Topic</option>
                        <option value="Healing">Healing</option>
                        <option value="Self Growth">Self Growth</option>
                        <option value="Grounding">Grounding</option>
                        <option value="Anxiety">Anxiety</option>
                        <option value="Productivity">Productivity</option>
                        <option value="Relaxation">Relaxation</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Publish Resource</button>
            </form>
            <div style="max-height: 200px; overflow-y: auto;">
                <table>
                    <tbody>
                        <?php while($r = $resources->fetch_assoc()): ?>
                        <tr>
                            <td style="font-size:0.8rem;">
                                <strong><?= htmlspecialchars($r['title']) ?></strong><br>
                                <small style="color:#888;"><?= htmlspecialchars($r['topic']) ?> | <?= htmlspecialchars($r['category']) ?></small>
                            </td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Delete?')">
                                    <input type="hidden" name="action" value="delete_content">
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <button type="submit" class="btn btn-danger" style="padding:4px 8px;">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-hide toast
    setTimeout(() => {
        let toast = document.querySelector('.toast');
        if(toast) toast.style.display = 'none';
    }, 3000);
</script>
</body>
</html>
