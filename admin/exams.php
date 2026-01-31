<?php
require '../db.php';
require '../auth.php';
checkAdmin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];
    $exam_date = $_POST['exam_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $location = $_POST['location'];

    try {
        $stmt = $pdo->prepare("INSERT INTO exams (course_code, course_name, exam_date, start_time, end_time, location) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$course_code, $course_name, $exam_date, $start_time, $end_time, $location]);
        $message = "Exam created successfully!";
    } catch (PDOException $e) {
        $message = "Error creating exam: " . $e->getMessage();
    }
}

// Fetch all exams
$exams = $pdo->query("SELECT * FROM exams ORDER BY exam_date DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Exams</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <span>Admin Portal</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
                <a href="exams.php" class="active"><i class="fas fa-calendar-alt"></i> <span>Exams</span></a>
                <a href="users.php"><i class="fas fa-users"></i> <span>Invigilators</span></a>
                <a href="assign.php"><i class="fas fa-user-check"></i> <span>Assignments</span></a>
                <a href="reports.php"><i class="fas fa-file-alt"></i> <span>Reports</span></a>
                <a href="analytics.php"><i class="fas fa-chart-line"></i> <span>Analytics</span></a>
            </div>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="user-name"><?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Admin'; ?></span>
                </div>
                <a href="../logout.php" class="logout-btn" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </nav>
    </header>

    <div class="main-content">
        <div class="container">
            <div class="welcome-section">
                <h1>Manage Exams</h1>
                <p>Create and monitor examination sessions.</p>
            </div>
        
        <?php if ($message): ?>
            <div class="stat-card <?php echo strpos($message, 'Error') !== false ? 'warning' : 'primary'; ?>" style="margin-bottom: 2rem; padding: 1rem;">
                <div class="stat-icon">
                    <i class="fas <?php echo strpos($message, 'Error') !== false ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-title"><?php echo strpos($message, 'Error') !== false ? 'Notice' : 'Success'; ?></div>
                    <div class="stat-value" style="font-size: 1rem;"><?php echo htmlspecialchars($message); ?></div>
                </div>
            </div>
        <?php endif; ?>

        <div class="content-card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h2><i class="fas fa-plus-circle"></i> Create New Exam</h2>
            </div>
            <form method="POST" action="" style="padding: 2rem;">
                <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                    <div class="form-group">
                        <label><i class="fas fa-hashtag"></i> Course Code</label>
                        <input type="text" name="course_code" required placeholder="e.g. CS101">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-book"></i> Course Name</label>
                        <input type="text" name="course_name" required placeholder="e.g. Intro to Computer Science">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Date</label>
                        <input type="date" name="exam_date" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Location</label>
                        <input type="text" name="location" required placeholder="e.g. Main Hall, Room 302">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Start Time</label>
                        <input type="time" name="start_time" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-hourglass-end"></i> End Time</label>
                        <input type="time" name="end_time" required>
                    </div>
                </div>
                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn"><i class="fas fa-save"></i> Create Examination Session</button>
                </div>
            </form>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> Scheduled Examinations</h2>
            </div>
            <div class="table-responsive">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Exam Details</th>
                            <th>Schedule</th>
                            <th>Venue</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exams as $exam): ?>
                        <tr>
                            <td>
                                <div class="course-info">
                                    <span class="course-code"><?php echo htmlspecialchars($exam['course_code']); ?></span>
                                    <span class="course-name"><?php echo htmlspecialchars($exam['course_name']); ?></span>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                    <span style="font-weight: 500; font-size: 0.9rem;"><i class="far fa-calendar-alt" style="color: var(--primary-color);"></i> <?php echo date('M d, Y', strtotime($exam['exam_date'])); ?></span>
                                    <span class="time-badge">
                                        <i class="far fa-clock"></i> <?php echo date('H:i', strtotime($exam['start_time'])) . ' - ' . date('H:i', strtotime($exam['end_time'])); ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 0.9rem; color: var(--text-main);">
                                    <i class="fas fa-location-dot" style="color: #ef4444;"></i> <?php echo htmlspecialchars($exam['location']); ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">
                                    <?php echo date('M d, Y', strtotime($exam['created_at'])); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($exams)): ?>
                        <tr>
                            <td colspan="4" class="empty-state">No examination sessions found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
