<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindora | <?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar {
            position: fixed;
            top: 0; left: -250px;
            width: 250px; height: 100vh;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-hover);
            transition: var(--transition-smooth);
            z-index: 1000;
            padding: 2rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .sidebar.open {
            left: 0;
        }
        .sidebar-toggle {
            position: fixed;
            top: 1rem; left: 1rem;
            background: white; border: none;
            width: 45px; height: 45px;
            border-radius: 50%;
            box-shadow: var(--shadow-soft);
            font-size: 1.5rem; cursor: pointer;
            z-index: 1001;
            color: var(--clr-text-main);
            display: flex; justify-content: center; align-items: center;
        }
        .sidebar a {
            padding: 1rem;
            border-radius: 12px;
            font-weight: 500;
        }
        .sidebar a:hover, .sidebar a.active {
            background: var(--clr-pastel-green);
            color: var(--clr-text-main);
        }
        .main-content {
            margin-left: 0;
            padding: 5rem 2rem 2rem;
            transition: var(--transition-smooth);
            max-width: 1400px;
            margin: 0 auto;
        }
        @media(min-width: 1024px) {
            .sidebar { left: 0; }
            .sidebar-toggle { display: none; }
            .main-content { margin-left: 250px; max-width: calc(100% - 250px); padding-top: 3rem; }
        }
    </style>
</head>
<body>
    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
    <div class="sidebar" id="sidebar">
        <h2 style="padding-left: 1rem; margin-bottom: 2rem; color: var(--clr-lavender-dark);">Mindora 🌿</h2>
        <?php if($_SESSION['role'] === 'student'): ?>
            <a href="dashboard.php" class="<?php echo ($pageTitle == 'Home') ? 'active' : ''; ?>">Home</a>
            <a href="activity.php" class="<?php echo ($pageTitle == 'Activity') ? 'active' : ''; ?>">Activity</a>
            <a href="insights.php" class="<?php echo ($pageTitle == 'Insights') ? 'active' : ''; ?>">Insights</a>
            <a href="journaling.php" class="<?php echo ($pageTitle == 'Journaling') ? 'active' : ''; ?>">Journaling</a>
            <a href="chatbot.php" class="<?php echo ($pageTitle == 'Chatbot') ? 'active' : ''; ?>">AI Chatbot</a>
            <a href="settings.php" class="<?php echo ($pageTitle == 'Settings') ? 'active' : ''; ?>">Settings</a>
        <?php else: ?>
            <a href="dashboard.php" class="<?php echo ($pageTitle == 'Admin') ? 'active' : ''; ?>">Dashboard</a>
            <a href="content.php" class="<?php echo ($pageTitle == 'Content') ? 'active' : ''; ?>">Manage Content</a>
        <?php endif; ?>
        <a href="../auth/logout.php" style="margin-top: auto; color: var(--clr-error);">Logout</a>
    </div>
    <div class="main-content" id="main-content">
