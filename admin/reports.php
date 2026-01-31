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
                <div class="logo-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <span>Admin Portal</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
                <a href="exams.php"><i class="fas fa-calendar-alt"></i> <span>Exams</span></a>
                <a href="users.php"><i class="fas fa-users"></i> <span>Invigilators</span></a>
                <a href="assign.php"><i class="fas fa-user-check"></i> <span>Assignments</span></a>
                <a href="reports.php" class="active"><i class="fas fa-file-alt"></i> <span>Reports</span></a>
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
                <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                    <div>
                        <h1>Intelligence <span class="text-gradient">Reports</span></h1>
                        <p>Aggregated feedback and incident analysis from the field.</p>
                    </div>
                    <div style="display: flex; gap: 0.75rem;">
                         <button class="btn btn-sm" style="background: white; color: var(--text-main); border: 1px solid var(--border-color);"><i class="fas fa-filter"></i> Filter</button>
                         <button class="btn btn-sm" style="background: var(--secondary-color);"><i class="fas fa-file-export"></i> Export CSV</button>
                    </div>
                </div>
            </div>

            <?php
            $total_reports = count($reports);
            $critical_count = 0;
            $student_sum = 0;
            foreach ($reports as $r) {
                $student_sum += $r['attendance_count'];
                if (!empty($r['incidents']) && strtolower(trim($r['incidents'])) !== 'none') {
                    $critical_count++;
                }
            }
            ?>

            <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-bottom: 2.5rem;">
                <div class="stat-card primary">
                    <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
                    <div class="stat-details">
                        <div class="stat-title">Submissions</div>
                        <div class="stat-value"><?php echo $total_reports; ?></div>
                        <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 4px;">Total reports finalized</div>
                    </div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon"><i class="fas fa-shield-alert"></i></div>
                    <div class="stat-details">
                        <div class="stat-title">Anomalies</div>
                        <div class="stat-value"><?php echo $critical_count; ?></div>
                        <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 4px;">Incident flags raised</div>
                    </div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon"><i class="fas fa-user-group"></i></div>
                    <div class="stat-details">
                        <div class="stat-title">Total Attendance</div>
                        <div class="stat-value"><?php echo number_format($student_sum); ?></div>
                        <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 4px;">Across all sessions</div>
                    </div>
                </div>
            </div>
        
            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fas fa-list-ul"></i> Archive Registry</h2>
                </div>
                
                <div class="table-responsive">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Subject Exam</th>
                                <th>Officer in Charge</th>
                                <th>Census</th>
                                <th>Operational Remarks</th>
                                <th>Logged Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $r): 
                                $has_incident = !empty($r['incidents']) && strtolower(trim($r['incidents'])) !== 'none';
                            ?>
                            <tr style="<?php echo $has_incident ? 'background: rgba(239, 68, 68, 0.02);' : ''; ?>">
                                <td>
                                    <div class="course-info">
                                        <span class="course-code"><?php echo htmlspecialchars($r['course_code']); ?></span>
                                        <span class="course-name" style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($r['course_name']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div class="user-avatar" style="width: 32px; height: 32px; background: #f1f5f9; color: var(--primary-color);">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <span style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($r['full_name']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="font-weight: 700; color: var(--text-main);"><?php echo $r['attendance_count']; ?></span>
                                        <span style="font-size: 0.75rem; color: var(--text-light); text-transform: uppercase;">Pax</span>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($has_incident): ?>
                                        <div style="background: #fef2f2; border: 1px solid #fee2e2; padding: 0.75rem; border-radius: 8px; color: #b91c1c; font-size: 0.85rem; line-height: 1.5;">
                                            <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 4px; text-transform: uppercase; font-size: 0.7rem;">
                                                <i class="fas fa-circle-exclamation"></i> Critical Incident
                                            </div>
                                            <?php echo nl2br(htmlspecialchars($r['incidents'])); ?>
                                        </div>
                                    <?php else: ?>
                                        <div style="color: var(--secondary-color); font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 6px;">
                                            <i class="fas fa-circle-check" style="font-size: 0.7rem;"></i> Normal Operation
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-main);"><?php echo date('M d, Y', strtotime($r['submission_time'])); ?></span>
                                        <span style="font-size: 0.75rem; color: var(--text-light);"><?php echo date('H:i', strtotime($r['submission_time'])); ?></span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($reports)): ?>
                            <tr>
                                <td colspan="5" class="empty-state">No registry entries found.</td>
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
