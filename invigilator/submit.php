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
                <i class="fas fa-clipboard-user" style="color: var(--primary-color); margin-right: 8px;"></i> 
                Invigilator Portal
            </div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="../logout.php" style="color: #EF4444;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1>Submit Exam Report</h1>

        <?php if ($message): ?>
            <div class="card" style="background-color: #D1FAE5; color: #065F46; text-align: center;">
                <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 1rem;"></i><br>
                <?php echo htmlspecialchars($message); ?>
                <br>Redirecting to dashboard...
            </div>
        <?php else: ?>

        <div class="card">
            <h2><?php echo htmlspecialchars($exam['course_code']); ?> <span style="font-weight: 400; color: #6B7280; font-size: 0.8em;"><?php echo htmlspecialchars($exam['course_name']); ?></span></h2>
            <div style="background-color: #F8FAFC; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid #E2E8F0; display: flex; gap: 2rem;">
                <div><i class="fas fa-calendar-day" style="color: #9CA3AF;"></i> <strong>Date:</strong> <?php echo htmlspecialchars($exam['exam_date']); ?></div>
                <div><i class="fas fa-clock" style="color: #9CA3AF;"></i> <strong>Time:</strong> <?php echo htmlspecialchars($exam['start_time']); ?></div>
                <div><i class="fas fa-map-marker-alt" style="color: #9CA3AF;"></i> <strong>Location:</strong> <?php echo htmlspecialchars($exam['location']); ?></div>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Number of Students Present</label>
                    <div style="position: relative;">
                        <input type="number" name="attendance_count" required min="0" style="padding-left: 2.5rem;">
                        <i class="fas fa-users" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9CA3AF;"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label>Incidents / Remarks</label>
                    <textarea name="incidents" rows="5" placeholder="Record any incidents or write 'None'." required></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn"><i class="fas fa-paper-plane" style="margin-right:0.5rem;"></i> Submit Final Report</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
