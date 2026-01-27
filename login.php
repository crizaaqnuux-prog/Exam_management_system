<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, role, full_name FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] == 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: invigilator/dashboard.php");
                }
                exit;
            } else {
                // Diagnostic check: Does the user exist at all?
                if (!$user && $username === 'admin') {
                    $error = "Admin account not found. <a href='setup.php'>Click here to run Setup</a>.";
                } else {
                    $error = "Invalid username or password.";
                }
            }
        } catch (PDOException $e) {
            // Check if table missing
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                $error = "Database tables missing. <a href='setup.php'>Click here to initialize database</a>.";
            } else {
                $error = "Database error: " . $e->getMessage();
            }
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Exam Invigilation System</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <h2 class="login-title">Welcome Back</h2>
            <p style="text-align: center; color: #0bf493ff; margin-bottom: 2rem;">Please sign in to your account</p>
            
            <?php if ($error): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle" style="margin-right: 0.5rem;"></i>
                    <?php echo $error; // Allow HTML links ?> 
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9CA3AF;">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" id="username" name="username" required style="padding-left: 2.5rem;" placeholder="Enter your username">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9CA3AF;">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" required style="padding-left: 2.5rem;" placeholder="Enter your password">
                    </div>
                </div>
                <button type="submit" class="btn" style="width: 100%;">
                    Sign In <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </button>
            </form>
            
            <div style="margin-top: 2rem; border-top: 1px solid #10f241ff; padding-top: 1.5rem;">
                <p style="text-align: center; color: #0f5bf3ff; font-size: 0.875rem;">
                </p>
                <p style="text-align: center; margin-top: 0.5rem; color: #ef0836ff; font-size: 0.875rem;">
                    Don't have an account? <a href="register.php" style="color: var(--primary-color); font-weight: 600;">Register</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
