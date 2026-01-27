<?php
session_start();
// Only redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    } else {
        header("Location: invigilator/dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Invigilation Management System</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            text-align: center;
            padding: 5rem 1rem;
            background: linear-gradient(135deg, #ffffff 0%, #f3f4f6 100%);
            border-bottom: 1px solid #e5e7eb;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #4f46e5, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.25rem;
            color: #6b7280;
            max-width: 600px;
            margin: 0 auto 2.5rem;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 4rem 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.2s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: #4f46e5;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <i class="fas fa-graduation-cap" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                ExamSystem
            </div>
            <div class="nav-links">
                <a href="login.php">Login</a>
                <a href="register.php" class="btn" style="padding: 0.5rem 1rem; font-size: 0.9rem; box-shadow: none;">Get Started</a>
            </div>
        </nav>
    </header>

    <div class="hero">
        <h1>Exam Invigilation <br>Made Simple</h1>
        <p>Streamline exam scheduling, invigilator assignments, and reporting with our modern management platform.</p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="login.php" class="btn" style="font-size: 1.1rem; padding: 1rem 2rem;">
                <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i> Login
            </a>
            <a href="register.php" class="btn btn-secondary" style="font-size: 1.1rem; padding: 1rem 2rem;">
                <i class="fas fa-user-plus" style="margin-right: 0.5rem;"></i> Register
            </a>
        </div>
    </div>

    <div class="features">
        <div class="feature-card">
            <i class="fas fa-calendar-alt feature-icon"></i>
            <h3>Smart Scheduling</h3>
            <p style="color: #6b7280; margin-top: 0.5rem;">Create and manage exam schedules effortlessly with our intuitive admin dashboard.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-user-check feature-icon"></i>
            <h3>Assignment Tracking</h3>
            <p style="color: #6b7280; margin-top: 0.5rem;">Assign invigilators to exams and track their acceptance and attendance in real-time.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-chart-line feature-icon"></i>
            <h3>Detailed Reporting</h3>
            <p style="color: #6b7280; margin-top: 0.5rem;">Generate comprehensive reports on incidents, attendance, and exam execution.</p>
        </div>
    </div>

    <footer style="text-align: center; padding: 2rem; color: #9ca3af; font-size: 0.875rem; background: #f9fafb; border-top: 1px solid #e5e7eb;">
        &copy; <?php echo date('Y'); ?> Exam Invigilation Management System. All rights reserved.
    </footer>
</body>
</html>
