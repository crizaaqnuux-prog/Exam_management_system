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
            padding: 8rem 0;
            background: linear-gradient(to bottom, #ffffff, #f1f5f9);
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, rgba(99, 102, 241, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            color: #1e293b;
        }

        .text-gradient {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--text-muted);
            margin-bottom: 3rem;
            line-height: 1.8;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .hero-actions .btn {
            padding: 1rem 2.5rem;
            font-size: 1rem;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 5rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            padding: 3rem 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: #eff6ff;
            color: var(--primary-color);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 1.5rem;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--text-main);
        }

        .feature-card p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <span>Invigilation System</span>
            </div>
            <div class="nav-links">
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> <span>Login</span></a>
                <a href="register.php" class="active"><i class="fas fa-user-plus"></i> <span>Get Started</span></a>
            </div>
        </nav>
    </header>

    <div class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Exam Invigilation <br><span class="text-gradient">Made Simple</span></h1>
                <p>Streamline exam scheduling, invigilator assignments, and reporting with our modern management platform. Built for efficiency and security.</p>
                <div class="hero-actions">
                    <a href="login.php" class="btn">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="register.php" class="btn btn-secondary">
                        <i class="fas fa-user-plus"></i> Register Now
                    </a>
                </div>
            </div>
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
