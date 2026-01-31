<?php
require '../db.php';
require '../auth.php';
checkInvigilator();

if (!isset($_GET['exam_id'])) {
    header("Location: dashboard.php");
    exit;
}

$exam_id = $_GET['exam_id'];
$user_id = $_SESSION['user_id'];
$message = '';

// Check if assigned and not submitted
$stmt = $pdo->prepare("SELECT COUNT(*) FROM assignments WHERE exam_id = ? AND user_id = ?");
$stmt->execute([$exam_id, $user_id]);
if ($stmt->fetchColumn() == 0) {
    die("Access Denied: You are not assigned to this exam.");
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE exam_id = ? AND user_id = ?");
$stmt->execute([$exam_id, $user_id]);
if ($stmt->fetchColumn() > 0) {
    die("Report already submitted.");
}

// Fetch Exam Details
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $attendance_count = $_POST['attendance_count'];
    $incidents = $_POST['incidents'];

    try {
        $stmt = $pdo->prepare("INSERT INTO reports (exam_id, user_id, attendance_count, incidents) VALUES (?, ?, ?, ?)");
        $stmt->execute([$exam_id, $user_id, $attendance_count, $incidents]);
        
        $message = "Report submitted successfully!";
        // Redirect after short delay or show success link
        header("Refresh: 2; url=dashboard.php");
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Report</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-clipboard-user"></i>
                </div>
                <span>Invigilator Portal</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
            </div>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="user-name"><?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Invigilator'; ?></span>
                </div>
                <a href="../logout.php" class="logout-btn" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </nav>
    </header>

    <div class="main-content">
        <div class="container">
            <div class="welcome-section">
                <h1>Submit Exam Report</h1>
                <p>Complete the examination feedback form below.</p>
            </div>

        <?php if ($message): ?>
            <div class="content-card" style="text-align: center; padding: 4rem 2rem;">
                <div style="width: 80px; height: 80px; background: #ecfdf5; color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; font-size: 2.5rem;">
                    <i class="fas fa-check"></i>
                </div>
                <h1 style="margin-bottom: 1rem;">Report Submitted!</h1>
                <p style="color: var(--text-muted); font-size: 1.1rem;"><?php echo htmlspecialchars($message); ?></p>
                <p style="margin-top: 2rem; font-size: 0.9rem; color: var(--text-light);">Redirecting to your dashboard in a moment...</p>
            </div>
        <?php else: ?>

        <div class="content-card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header" style="flex-direction: column; align-items: flex-start; gap: 0.5rem;">
                <h2 style="font-size: 1.5rem; color: var(--text-main);">
                    <?php echo htmlspecialchars($exam['course_code']); ?> 
                    <span style="font-weight: 400; color: var(--text-muted); margin-left: 0.5rem;"><?php echo htmlspecialchars($exam['course_name']); ?></span>
                </h2>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.5rem;">
                    <span class="time-badge"><i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($exam['exam_date'])); ?></span>
                    <span class="time-badge"><i class="far fa-clock"></i> <?php echo date('H:i', strtotime($exam['start_time'])); ?></span>
                    <span class="time-badge" style="background: #fff1f2; color: #e11d48; border-color: #fecdd3;"><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($exam['location']); ?></span>
                </div>
            </div>

            <form method="POST" action="" style="padding: 2.5rem;">
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label style="font-weight: 600; margin-bottom: 0.75rem; display: block;">
                        <i class="fas fa-users-viewfinder" style="margin-right: 0.5rem; color: var(--primary-color);"></i>
                        Final Student Attendance
                    </label>
                    <div style="position: relative;">
                        <input type="number" name="attendance_count" required min="0" placeholder="0" style="padding: 1rem 1rem 1rem 3rem; font-size: 1.1rem; font-weight: 600;">
                        <span style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.1rem;">#</span>
                    </div>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Enter the total number of students who physically attended the exam.</p>
                </div>

                <div class="form-group" style="margin-bottom: 2.5rem;">
                    <label style="font-weight: 600; margin-bottom: 0.75rem; display: block;">
                        <i class="fas fa-comment-medical" style="margin-right: 0.5rem; color: #ef4444;"></i>
                        Incidents & Professional Remarks
                    </label>
                    <textarea name="incidents" rows="6" placeholder="Document any irregularities, missing papers, or special cases. If everything went smoothly, simply write 'None'." required style="padding: 1.25rem; line-height: 1.6;"></textarea>
                </div>
                
                <div style="display: flex; gap: 1.5rem; border-top: 1px solid var(--border-color); padding-top: 2rem;">
                    <button type="submit" class="btn" style="padding: 1rem 2rem;">
                        <i class="fas fa-paper-plane" style="margin-right: 0.75rem;"></i> Finalize & Submit Report
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary" style="padding: 1rem 2rem; background: transparent; border: 1px solid var(--border-color); color: var(--text-main);">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
