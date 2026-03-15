<?php
session_start();
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password_input = $_POST['password'];
    $role = $_POST['role'];

    $sql = "SELECT id, name, password_hash, role FROM users WHERE email='$email' AND role='$role'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password_input, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../student/dashboard.php");
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .auth-card {
            width: 100%;
            max-width: 450px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="glass-card auth-card">
            <h2 class="text-center mb-4">Welcome Back 🌿</h2>
            
            <?php if(isset($error)): ?>
                <div style="color: var(--clr-error); text-align: center; margin-bottom: 1rem;"><?= $error ?></div>
            <?php endif; ?>
            <?php if(isset($_GET['registered'])): ?>
                <div style="color: var(--clr-success); text-align: center; margin-bottom: 1rem; font-weight: bold;">Registration successful, please login.</div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block mb-4">Login</button>
            </form>
            
            <div class="text-center">
                <p>Not registered? <a href="register.php">Sign Up</a></p>
            </div>
        </div>
    </div>
</body>
</html>
