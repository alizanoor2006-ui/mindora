<?php
session_start();
require_once 'config.php';

// Redirect already logged-in users
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit();
}

$error = '';

// Auto-fix: Ensure is_blocked column exists
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'is_blocked'");
if ($check && $check->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN is_blocked BOOLEAN DEFAULT FALSE");
}

// Auto-fix: Ensure gender column exists
$check_gender = $conn->query("SHOW COLUMNS FROM users LIKE 'gender'");
if ($check_gender && $check_gender->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN gender ENUM('male', 'female', 'other') DEFAULT 'female'");
}

// Auto-fix: Ensure expression_type column exists in journals
$check_exp = $conn->query("SHOW COLUMNS FROM journals LIKE 'expression_type'");
if ($check_exp && $check_exp->num_rows === 0) {
    $conn->query("ALTER TABLE journals ADD COLUMN expression_type ENUM('expressed', 'unexpressed') DEFAULT 'unexpressed'");
}

// Auto-fix: Ensure topic column exists in insights_content
$check_topic = $conn->query("SHOW COLUMNS FROM insights_content LIKE 'topic'");
if ($check_topic && $check_topic->num_rows === 0) {
    $conn->query("ALTER TABLE insights_content ADD COLUMN topic ENUM('Healing', 'Self Growth', 'Grounding', 'Anxiety', 'Productivity', 'Relaxation') DEFAULT 'Self Growth'");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password_input = $_POST['password'];
    $role = $_POST['role'];

    $sql = "SELECT id, name, password_hash, role, is_blocked FROM users WHERE email='$email' AND role='$role'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($user['is_blocked']) {
            $error = "Access denied. Your account is temporarily suspended. 🚫";
        } elseif (password_verify($password_input, $user['password_hash'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role']      = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found or role mismatch.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindora | Login</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #ede7f6, #e3f2fd);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; color: #4a4a8a; margin-bottom: 1.5rem; font-size: 1.8rem; }
        .error-msg { color: #e57373; text-align: center; margin-bottom: 1rem; font-size: 0.9rem; }
        .success-msg { color: #66bb6a; text-align: center; margin-bottom: 1rem; font-size: 0.9rem; font-weight: 600; }
        .form-group { margin-bottom: 1.2rem; }
        label { display: block; margin-bottom: 0.4rem; color: #5c5c8a; font-size: 0.9rem; font-weight: 600; }
        input, select {
            width: 100%; padding: 0.75rem 1rem;
            border: 2px solid #e0d7f5;
            border-radius: 12px; font-size: 1rem;
            background: rgba(255,255,255,0.8);
            outline: none; transition: border 0.2s;
        }
        input:focus, select:focus { border-color: #b39ddb; }
        .btn {
            width: 100%; padding: 0.85rem;
            background: linear-gradient(135deg, #a8d5a2, #b39ddb);
            color: white; border: none; border-radius: 12px;
            font-size: 1.1rem; font-weight: 700;
            cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 0.5rem;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
        .signup-link { text-align: center; margin-top: 1.2rem; color: #666; font-size: 0.9rem; }
        .signup-link a { color: #7c6ab5; font-weight: 600; text-decoration: none; }
        .signup-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2>Welcome Back 🌿</h2>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['registered'])): ?>
            <div class="success-msg">Registration successful! Please login.</div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="student">🎓 Student</option>
                    <option value="admin">👨‍💼 Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn">Login 🌸</button>
        </form>

        <div class="signup-link">
            <p>Not registered? <a href="register.php">Sign Up</a></p>
        </div>
    </div>
</body>
</html>
