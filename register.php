<?php
session_start();
require 'db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'invigilator'; // Default role for self-registration

    if (!empty($full_name) && !empty($username) && !empty($password)) {
        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Check if username exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Username already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                try {
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, full_name) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $hashed_password, $role, $full_name]);
                    $message = "Registration successful! <a href='login.php'>Login here</a>";
                } catch (PDOException $e) {
                    $error = "Error: " . $e->getMessage();
                }
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
    <title>Register - Exam Invigilation System</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card" style="max-width: 500px;">
            <h2 class="login-title">Create Account</h2>
            <p style="text-align: center; color: #6B7280; margin-bottom: 2rem;">Join as an Invigilator</p>
            
            <?php if ($error): ?>
                <div class="error-msg"><i class="fas fa-exclamation-circle" style="margin-right: 0.5rem;"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($message): ?>
                <div style="background-color: #D1FAE5; color: #065F46; padding: 1rem; margin-bottom: 1rem; text-align: center; border-radius: 0.5rem;">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($message)): ?>
            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required placeholder="John Doe">
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required placeholder="johndoe">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" required style="padding-right: 2.5rem;">
                        <i class="fas fa-lock" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #9CA3AF;"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div style="position: relative;">
                        <input type="password" id="confirm_password" name="confirm_password" required style="padding-right: 2.5rem;">
                        <i class="fas fa-lock" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #9CA3AF;"></i>
                    </div>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Register Account</button>
            </form>
            <?php endif; ?>
            
            <p style="text-align: center; margin-top: 1.5rem; color: #6B7280; font-size: 0.875rem;">
                Already have an account? <a href="login.php" style="color: var(--primary-color); font-weight: 600;">Sign In</a>
            </p>
        </div>
    </div>
</body>
</html>
