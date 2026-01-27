<?php
require '../db.php';
require '../auth.php';
checkAdmin();

// Fetch stats
$invigilators_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'invigilator'")->fetchColumn();
$exams_count = $pdo->query("SELECT COUNT(*) FROM exams")->fetchColumn();
$upcoming_exams = $pdo->query("SELECT COUNT(*) FROM exams WHERE exam_date >= CURDATE()")->fetchColumn();
$reports_count = $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();

// Fetch recent exams
$stmt = $pdo->query("SELECT * FROM exams ORDER BY exam_date DESC LIMIT 5");
$recent_exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    body {
    background: linear-gradient(to right, #10ff58ff, #f51b1bff);
}

    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <i class="fas fa-shield-alt" style="color: var(--primary-color); margin-right: 8px;"></i> 
                Admin Portal
            </div>
            <div class="nav-links">
                <a href="dashboard.php" style="color: var(--primary-color);"><i class="fas fa-home"></i> Dashboard</a>
                <a href="exams.php"><i class="fas fa-calendar-alt"></i> Exams</a>
                <a href="users.php"><i class="fas fa-users"></i> Invigilators</a>
                <a href="assign.php"><i class="fas fa-user-check"></i> Assignments</a>
                <a href="reports.php"><i class="fas fa-file-alt"></i> Reports</a>
                <div style="display: flex; align-items: center; gap: 1rem; margin-left: 1rem; padding-left: 1rem; border-left: 1px solid #E5E7EB;">
                    <span style="font-size: 0.9rem; color: #4B5563; font-weight: 500;">
                        <i class="fas fa-user-circle" style="color: var(--primary-color);"></i> 
                        <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Admin'; ?>
                    </span>
                    <a href="../logout.php" style="color: #EF4444;" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </div>
        </nav>
    </header>
    
    <div class="container">
        <h1>Dashboard Overview</h1>
        
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-title"><i class="fas fa-users" style="margin-right:0.5rem;"></i> Total Invigilators</div>
                <div class="stat-value"><?php echo $invigilators_count; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-title"><i class="fas fa-calendar-check" style="margin-right:0.5rem;"></i> Total Exams</div>
                <div class="stat-value"><?php echo $exams_count; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-title"><i class="fas fa-clock" style="margin-right:0.5rem;"></i> Upcoming Exams</div>
                <div class="stat-value"><?php echo $upcoming_exams; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-title"><i class="fas fa-clipboard-check" style="margin-right:0.5rem;"></i> Reports Submitted</div>
                <div class="stat-value"><?php echo $reports_count; ?></div>
            </div>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2><i class="fas fa-history" style="color: #64748B; margin-right: 0.5rem;"></i> Recent Exams</h2>
                <a href="exams.php" class="btn"><i class="fas fa-plus" style="margin-right: 0.5rem;"></i> Manage Exams</a>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_exams as $exam): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($exam['course_code'] . ' - ' . $exam['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($exam['exam_date']); ?></td>
                            <td><?php echo htmlspecialchars($exam['start_time'] . ' - ' . $exam['end_time']); ?></td>
                            <td><?php echo htmlspecialchars($exam['location']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent_exams)) echo "<tr><td colspan='4' style='text-align:center; color:#999;'>No exams found.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
