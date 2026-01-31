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
                <a href="dashboard.php" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a>
                <a href="exams.php"><i class="fas fa-calendar-alt"></i> <span>Exams</span></a>
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
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Dashboard <span class="text-gradient">Overview</span></h1>
                        <p style="font-size: 1.1rem; opacity: 0.8;">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>. Here's a snapshot of the system today.</p>
                    </div>
                    <div style="text-align: right;">
                        <div class="time-badge" style="background: white; border: 1px solid var(--border-color); font-size: 1rem; padding: 0.75rem 1.25rem;">
                            <i class="far fa-calendar-alt" style="color: var(--primary-color);"></i> <?php echo date('l, M d, Y'); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid" style="margin-bottom: 2rem;">
                <div class="stat-card primary">
                    <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
                    <div class="stat-details">
                        <div class="stat-title">Invigilators</div>
                        <div class="stat-value"><?php echo $invigilators_count; ?></div>
                        <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 4px;">Active staff members</div>
                    </div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon"><i class="fas fa-file-signature"></i></div>
                    <div class="stat-details">
                        <div class="stat-title">Sessions</div>
                        <div class="stat-value"><?php echo $exams_count; ?></div>
                        <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 4px;">Total exams logged</div>
                    </div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="stat-details">
                        <div class="stat-title">Upcoming</div>
                        <div class="stat-value"><?php echo $upcoming_exams; ?></div>
                        <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 4px;">Pending examinations</div>
                    </div>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                    <div class="stat-details">
                        <div class="stat-title">Reports</div>
                        <div class="stat-value"><?php echo $reports_count; ?></div>
                        <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 4px;">Final submissions</div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                <!-- Recent Activity List -->
                <div class="content-card">
                    <div class="card-header">
                        <h2><i class="fas fa-history" style="color: var(--primary-color);"></i> Recently Added Exams</h2>
                        <a href="exams.php" class="btn btn-sm" style="background: #eff6ff; color: var(--primary-color); border: none; box-shadow: none;">View All Sessions</a>
                    </div>
                    
                    <div class="activity-feed">
                        <?php if (empty($recent_exams)): ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-plus" style="font-size: 2.5rem; display: block; margin-bottom: 1rem; opacity: 0.2;"></i>
                                No examination sessions recorded.
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_exams as $exam): 
                                $is_new = (strtotime($exam['created_at']) > strtotime('-24 hours'));
                                $exam_time = strtotime($exam['exam_date']);
                                $today = strtotime(date('Y-m-d'));
                                $diff = ($exam_time - $today) / (60 * 60 * 24);
                                
                                $timeline_text = $diff == 0 ? 'Today' : ($diff == 1 ? 'Tomorrow' : ($diff > 0 ? "In $diff Days" : 'Completed'));
                                $timeline_color = $diff <= 1 ? '#ef4444' : 'var(--text-light)';

                                $colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
                                $color = $colors[ord($exam['course_code'][0]) % count($colors)];
                            ?>
                            <div class="activity-item" style="border-left-color: <?php echo $color; ?>;">
                                <div class="activity-content">
                                    <div class="activity-icon" style="background: <?php echo $color; ?>15; color: <?php echo $color; ?>; border-color: <?php echo $color; ?>30;">
                                        <?php echo substr($exam['course_code'], 0, 2); ?>
                                    </div>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2px;">
                                            <span style="font-weight: 700; color: #1e293b; font-size: 0.95rem;"><?php echo htmlspecialchars($exam['course_code']); ?></span>
                                            <?php if ($is_new): ?>
                                                <span class="pulse-badge" style="background: #ef4444; color: white; font-size: 0.6rem; padding: 2px 6px; border-radius: 4px; font-weight: 800; text-transform: uppercase;">New</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span class="timeline-tag" style="color: <?php echo $timeline_color; ?>;"><?php echo $timeline_text; ?></span>
                                            <span style="color: var(--text-light); font-size: 0.7rem;">•</span>
                                            <span style="font-size: 0.75rem; font-weight: 600; color: #64748b;"><i class="far fa-clock"></i> <?php echo date('H:i A', strtotime($exam['start_time'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="activity-meta">
                                    <div class="date-block" style="border-color: <?php echo $color; ?>20;">
                                        <span class="date-block-month" style="color: <?php echo $color; ?>;"><?php echo date('M', strtotime($exam['exam_date'])); ?></span>
                                        <span class="date-block-day"><?php echo date('d', strtotime($exam['exam_date'])); ?></span>
                                    </div>
                                    
                                    <div class="venue-tag" style="background: white; border-style: dashed;">
                                        <i class="fas fa-location-dot" style="color: <?php echo $color; ?>; font-size: 0.8rem;"></i>
                                        <span style="font-size: 0.75rem; font-weight: 700; color: #334155;"><?php echo htmlspecialchars($exam['location']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions Panel -->
                <div class="content-card" style="background: #f8fafc; border-color: #e2e8f0;">
                    <div class="card-header">
                        <h2 style="font-size: 1.1rem;"><i class="fas fa-bolt" style="color: #f59e0b;"></i> Quick Actions</h2>
                    </div>
                    <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                        <a href="assign.php" class="btn" style="width: 100%; text-align: left; display: flex; align-items: center; justify-content: space-between; padding: 1.25rem;">
                            <span>Assign Staff</span>
                            <i class="fas fa-user-plus"></i>
                        </a>
                        <a href="reports.php" class="btn" style="width: 100%; background: white; color: var(--text-main); border: 1px solid var(--border-color); text-align: left; display: flex; align-items: center; justify-content: space-between; padding: 1.25rem; box-shadow: var(--shadow-sm);">
                            <span>Review Incident Reports</span>
                            <i class="fas fa-file-contract" style="color: var(--secondary-color);"></i>
                        </a>
                        <a href="analytics.php" class="btn" style="width: 100%; background: var(--secondary-color); text-align: left; display: flex; align-items: center; justify-content: space-between; padding: 1.25rem;">
                            <span>View Full Analytics</span>
                            <i class="fas fa-chart-pie"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
