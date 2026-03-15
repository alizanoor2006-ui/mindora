<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name           = $conn->real_escape_string($_POST['name']);
    $email          = $conn->real_escape_string($_POST['email']);
    $password_input = $_POST['password'];
    $role           = $_POST['role'];

    $password_hash = password_hash($password_input, PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (name, email, password_hash, role) VALUES ('$name', '$email', '$password_hash', '$role')";

    if ($conn->query($sql)) {
        header("Location: login.php?registered=1");
        exit();
    } else {
        $error = "Registration failed. Email might already exist.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindora | Register</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #ede7f6, #e3f2fd);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .auth-card {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(20px);
            border-radius: 24px; padding: 2.5rem;
            width: 100%; max-width: 440px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; color: #4a4a8a; margin-bottom: 1.5rem; font-size: 1.8rem; }
        .error-msg { color: #e57373; text-align: center; margin-bottom: 1rem; font-size: 0.9rem; }
        .form-group { margin-bottom: 1.2rem; }
        label { display: block; margin-bottom: 0.4rem; color: #5c5c8a; font-size: 0.9rem; font-weight: 600; }
        input, select {
            width: 100%; padding: 0.75rem 1rem;
            border: 2px solid #e0d7f5; border-radius: 12px;
            font-size: 1rem; background: rgba(255,255,255,0.8);
            outline: none; transition: border 0.2s;
        }
        input:focus, select:focus { border-color: #b39ddb; }
        .btn {
            width: 100%; padding: 0.85rem;
            background: linear-gradient(135deg, #a8d5a2, #b39ddb);
            color: white; border: none; border-radius: 12px;
            font-size: 1.1rem; font-weight: 700;
            cursor: pointer; transition: transform 0.2s;
            margin-top: 0.5rem;
        }
        .btn:hover { transform: translateY(-2px); }
        .signin-link { text-align: center; margin-top: 1.2rem; color: #666; font-size: 0.9rem; }
        .signin-link a { color: #7c6ab5; font-weight: 600; text-decoration: none; }
        .signin-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2>Join Mindora 🌸</h2>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Role</label>
                <select name="role" id="roleSelect" required>
                    <option value="student">🎓 Student</option>
                    <option value="admin">👨‍💼 Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Your full name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label>Create Password</label>
                <input type="password" name="password" placeholder="Min 8 characters" required>
            </div>
            <button type="submit" class="btn">Register 🌿</button>
        </form>

        <div class="signin-link">
            <p>Already registered? <a href="login.php">Sign In</a></p>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('role')) {
            document.getElementById('roleSelect').value = urlParams.get('role');
        }
    </script>
</body>
</html>
