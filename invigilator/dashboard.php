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
                <div class="logo-icon">
                    <i class="fas fa-clipboard-user"></i>
                </div>
                <span>Invigilator Portal</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a>
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
                <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                    <div>
                        <h1>My Exam Schedule</h1>
                        <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>. Here are your assigned invigilation duties.</p>
                    </div>
                    <div class="time-badge" style="background: white; border: 1px solid var(--border-color);">
                        <i class="fas fa-user-clock"></i> Duty Roster
                    </div>
                </div>
            </div>
        
            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fas fa-calendar-check"></i> Assigned Examinations</h2>
                </div>
                <div class="table-responsive">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Examination Details</th>
                                <th>Schedule</th>
                                <th>Venue</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $a): ?>
                            <tr>
                                <td>
                                    <div class="course-info">
                                        <span class="course-code text-gradient"><?php echo htmlspecialchars($a['course_code']); ?></span>
                                        <span class="course-name"><?php echo htmlspecialchars($a['course_name']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <span style="font-weight: 600; font-size: 0.95rem; color: var(--text-main);">
                                            <?php echo date('M d, Y', strtotime($a['exam_date'])); ?>
                                        </span>
                                        <span class="time-badge" style="width: fit-content; font-size: 0.75rem;">
                                            <i class="far fa-clock"></i> <?php echo date('H:i', strtotime($a['start_time'])) . ' - ' . date('H:i', strtotime($a['end_time'])); ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 0.9rem; color: var(--text-main);">
                                        <i class="fas fa-location-dot" style="color: #ef4444; margin-right: 4px;"></i> 
                                        <?php echo htmlspecialchars($a['location']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($a['is_submitted']): ?>
                                        <span class="status-pill" style="background: #ecfdf5; color: #059669; border: 1px solid #d1fae5;">
                                            <i class="fas fa-check-circle"></i> Completed
                                        </span>
                                    <?php else: ?>
                                        <span class="status-pill" style="background: #fffbeb; color: #d97706; border: 1px solid #fef3c7;">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$a['is_submitted']): ?>
                                        <a href="submit.php?exam_id=<?php echo $a['exam_id']; ?>" class="btn btn-sm" style="display: flex; align-items: center; gap: 6px; width: fit-content;">
                                            <i class="fas fa-pen-to-square"></i> Submit Report
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">
                                            <i class="fas fa-file-circle-check" style="color: var(--primary-color);"></i> Report Logged
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($assignments)): ?>
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <div style="padding: 3rem;">
                                        <i class="fas fa-calendar-xmark" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 1rem; display: block;"></i>
                                        No exams assigned to you yet.
                                    </div>
                                </td>
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
