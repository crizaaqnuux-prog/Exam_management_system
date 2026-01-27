<?php
require '../db.php';
require '../auth.php';
checkInvigilator();

$user_id = $_SESSION['user_id'];

// Fetch assignments
$query = "
    SELECT a.id as assignment_id, e.id as exam_id, e.course_code, e.course_name, e.exam_date, e.start_time, e.end_time, e.location,
    (SELECT COUNT(*) FROM reports r WHERE r.exam_id = e.id AND r.user_id = ?) as is_submitted
    FROM assignments a
    JOIN exams e ON a.exam_id = e.id
    WHERE a.user_id = ?
    ORDER BY e.exam_date ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id, $user_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invigilator Dashboard</title>
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
                <a href="dashboard.php" style="color: var(--primary-color);"><i class="fas fa-home"></i> Dashboard</a>
                <div style="display: flex; align-items: center; gap: 1rem; margin-left: 1rem; padding-left: 1rem; border-left: 1px solid #E5E7EB;">
                    <span style="font-size: 0.9rem; color: #4B5563; font-weight: 500;">
                        <i class="fas fa-user-circle" style="color: var(--primary-color);"></i> 
                        <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Invigilator'; ?>
                    </span>
                    <a href="../logout.php" style="color: #EF4444;" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1>My Exam Schedule</h1>
        
        <div class="dashboard-grid" style="grid-template-columns: 1fr;">
            <div class="card">
                <h2><i class="fas fa-calendar-alt"></i> Upcoming Assignments</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $a): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($a['course_code']); ?></strong><br>
                                    <span style="color: #6B7280; font-size: 0.9em;"><?php echo htmlspecialchars($a['course_name']); ?></span>
                                </td>
                                <td><i class="fas fa-calendar-day" style="color: #9CA3AF;"></i> <?php echo htmlspecialchars($a['exam_date']); ?></td>
                                <td><i class="fas fa-clock" style="color: #9CA3AF;"></i> <?php echo htmlspecialchars($a['start_time'] . ' - ' . $a['end_time']); ?></td>
                                <td><i class="fas fa-map-marker-alt" style="color: #9CA3AF;"></i> <?php echo htmlspecialchars($a['location']); ?></td>
                                <td>
                                    <?php if ($a['is_submitted']): ?>
                                        <span style="background-color: #D1FAE5; color: #065F46; padding: 2px 8px; border-radius: 9999px; font-weight: 600; font-size: 0.85em;">Completed</span>
                                    <?php else: ?>
                                        <span style="background-color: #FEF3C7; color: #B45309; padding: 2px 8px; border-radius: 9999px; font-weight: 600; font-size: 0.85em;">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$a['is_submitted']): ?>
                                        <a href="submit.php?exam_id=<?php echo $a['exam_id']; ?>" class="btn" style="padding: 0.5rem 1rem; font-size: 0.8em;"><i class="fas fa-pen"></i> Submit Report</a>
                                    <?php else: ?>
                                        <span style="color: #9CA3AF; font-size: 0.9em;"><i class="fas fa-check"></i> Submitted</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($assignments)) echo "<tr><td colspan='6' style='text-align:center; color:#999;'>No exams assigned to you yet.</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
