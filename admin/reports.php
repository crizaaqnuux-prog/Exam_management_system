<?php
require '../db.php';
require '../auth.php';
checkAdmin();

// Fetch Reports
$query = "
    SELECT r.*, e.course_code, e.course_name, e.exam_date, u.full_name
    FROM reports r
    JOIN exams e ON r.exam_id = e.id
    JOIN users u ON r.user_id = u.id
    ORDER BY r.submission_time DESC
";
$reports = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Reports</title>
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
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="exams.php"><i class="fas fa-calendar-alt"></i> Exams</a>
                <a href="users.php"><i class="fas fa-users"></i> Invigilators</a>
                <a href="assign.php"><i class="fas fa-user-check"></i> Assignments</a>
                <a href="reports.php" style="color: var(--primary-color);"><i class="fas fa-file-alt"></i> Reports</a>
                <a href="../logout.php" style="color: #EF4444;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1>Exam Reports</h1>
        
        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Invigilator</th>
                            <th>Attendance</th>
                            <th>Incidents</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $r): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($r['course_code']); ?></strong><br>
                                <span style="font-size: 0.85em; color: gray;"><i class="fas fa-calendar-day"></i> <?php echo htmlspecialchars($r['exam_date']); ?></span>
                            </td>
                            <td><i class="fas fa-user-circle" style="color: #9CA3AF;"></i> <?php echo htmlspecialchars($r['full_name']); ?></td>
                            <td>
                                <span style="background-color: #F3F4F6; padding: 2px 8px; border-radius: 9999px; font-weight: 600;">
                                    <?php echo htmlspecialchars($r['attendance_count']); ?>
                                </span>
                            </td>
                            <td><?php echo nl2br(htmlspecialchars($r['incidents'])); ?></td>
                            <td><?php echo htmlspecialchars($r['submission_time']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reports)) echo "<tr><td colspan='5' style='text-align:center; color:#999;'>No reports submitted yet.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
