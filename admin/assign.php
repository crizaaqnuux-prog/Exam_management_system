<?php
require '../db.php';
require '../auth.php';
checkAdmin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign'])) {
    $exam_id = $_POST['exam_id'];
    $user_id = $_POST['user_id'];

    // Check if already assigned
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM assignments WHERE exam_id = ? AND user_id = ?");
    $stmt->execute([$exam_id, $user_id]);
    if ($stmt->fetchColumn() > 0) {
        $message = "Error: This invigilator is already assigned to this exam.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO assignments (exam_id, user_id) VALUES (?, ?)");
            $stmt->execute([$exam_id, $user_id]);
            $message = "Invigilator assigned successfully!";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Handle Unassign
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM assignments WHERE id = ?")->execute([$id]);
    $message = "Assignment removed.";
}

// Fetch Exams and Users for Dropdowns
$exams = $pdo->query("SELECT * FROM exams WHERE exam_date >= CURDATE() ORDER BY exam_date ASC")->fetchAll(PDO::FETCH_ASSOC);
$invigilators = $pdo->query("SELECT * FROM users WHERE role = 'invigilator' ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Assignments
$query = "
    SELECT a.id, e.course_code, e.course_name, e.exam_date, e.start_time, u.full_name 
    FROM assignments a
    JOIN exams e ON a.exam_id = e.id
    JOIN users u ON a.user_id = u.id
    ORDER BY e.exam_date DESC
";
$assignments = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Invigilators</title>
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
                <a href="assign.php" class="active"><i class="fas fa-user-check"></i> <span>Assignments</span></a>
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
                <h1>Assign Invigilators</h1>
                <p>Manage and link invigilators to scheduled exams.</p>
            </div>

        <?php if ($message): ?>
            <div class="stat-card <?php echo strpos($message, 'Error') !== false ? 'warning' : 'primary'; ?>" style="margin-bottom: 2rem; padding: 1rem;">
                <div class="stat-icon">
                    <i class="fas <?php echo strpos($message, 'Error') !== false ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-title"><?php echo strpos($message, 'Error') !== false ? 'Action Required' : 'Success'; ?></div>
                    <div class="stat-value" style="font-size: 1rem;"><?php echo htmlspecialchars($message); ?></div>
                </div>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 350px 1fr; gap: 2.5rem; align-items: start;">
            <!-- Assignment Form -->
            <div class="content-card" style="position: sticky; top: 100px;">
                <div class="card-header">
                    <h2><i class="fas fa-link" style="color: var(--primary-color);"></i> Link Officer</h2>
                </div>
                <form method="POST" action="" style="padding: 2rem;">
                    <input type="hidden" name="assign" value="1">
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 600; font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">Select Examination Session</label>
                        <div style="position: relative;">
                            <select name="exam_id" required style="padding-left: 2.5rem; appearance: none;">
                                <option value="">Choose an exam...</option>
                                <?php foreach ($exams as $exam): ?>
                                    <option value="<?php echo $exam['id']; ?>">
                                        <?php echo htmlspecialchars($exam['course_code'] . ' - ' . date('M d', strtotime($exam['exam_date']))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-calendar-day" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-light); pointer-events: none;"></i>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label style="font-weight: 600; font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">Select Invigilator</label>
                        <div style="position: relative;">
                            <select name="user_id" required style="padding-left: 2.5rem; appearance: none;">
                                <option value="">Choose an officer...</option>
                                <?php foreach ($invigilators as $invigilator): ?>
                                    <option value="<?php echo $invigilator['id']; ?>">
                                        <?php echo htmlspecialchars($invigilator['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-user-tie" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-light); pointer-events: none;"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn" style="width: 100%; padding: 1rem; font-weight: 600; letter-spacing: 0.02em; box-shadow: var(--shadow-glow);">
                        <i class="fas fa-plus-circle" style="margin-right: 0.5rem;"></i> Finalize Assignment
                    </button>
                    <p style="text-align: center; font-size: 0.75rem; color: var(--text-light); margin-top: 1rem;">
                        <i class="fas fa-info-circle"></i> Assignments are updated in real-time.
                    </p>
                </form>
            </div>

            <!-- Assignments List -->
            <div class="content-card">
                <div class="card-header" style="justify-content: space-between;">
                    <h2><i class="fas fa-list-check" style="color: var(--secondary-color);"></i> Active Assignments</h2>
                    <span style="font-size: 0.75rem; font-weight: 600; background: #f1f5f9; padding: 4px 12px; border-radius: 99px; color: var(--text-muted);">
                        <?php echo count($assignments); ?> Records Found
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Exam Details</th>
                                <th>Assigned Officer</th>
                                <th>Timeline</th>
                                <th style="text-align: right;">Management</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $a): 
                                // Color logic based on course
                                $colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
                                $color = $colors[ord($a['course_code'][0]) % count($colors)];
                                
                                // Initials
                                $words = explode(" ", $a['full_name']);
                                $initials = "";
                                foreach ($words as $w) $initials .= strtoupper($w[0]);
                                if(strlen($initials) > 2) $initials = substr($initials, 0, 2);
                            ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 38px; height: 38px; border-radius: 8px; background: <?php echo $color; ?>10; color: <?php echo $color; ?>; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.75rem; border: 1px solid <?php echo $color; ?>20;">
                                            <?php echo substr($a['course_code'], 0, 2); ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: #1e293b; font-size: 0.9rem;"><?php echo htmlspecialchars($a['course_code']); ?></div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($a['course_name']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 700; border: 1px solid #dbeafe;">
                                            <?php echo $initials; ?>
                                        </div>
                                        <span style="font-weight: 600; color: #334155; font-size: 0.85rem;"><?php echo htmlspecialchars($a['full_name']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <div style="font-size: 0.8rem; font-weight: 700; color: #475569;">
                                            <i class="far fa-calendar-check" style="color: var(--secondary-color); margin-right: 4px;"></i>
                                            <?php echo date('M d, Y', strtotime($a['exam_date'])); ?>
                                        </div>
                                        <div class="time-badge" style="width: fit-content; font-size: 0.7rem; padding: 2px 8px;">
                                            <i class="far fa-clock"></i> <?php echo date('H:i A', strtotime($a['start_time'])); ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <a href="assign.php?delete=<?php echo $a['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to remove this assignment?')"
                                       style="color: #cbd5e1; transition: all 0.2s ease; font-size: 1rem; padding: 8px; border-radius: 8px; display: inline-flex;"
                                       onmouseover="this.style.color='#ef4444'; this.style.background='#fef2f2';"
                                       onmouseout="this.style.color='#cbd5e1'; this.style.background='transparent';"
                                       title="Remove Assignment">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($assignments)): ?>
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <div style="padding: 3rem 0;">
                                        <i class="fas fa-user-slash" style="font-size: 3rem; opacity: 0.1; display: block; margin-bottom: 1rem;"></i>
                                        No active assignments recorded yet.
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
</div>
</body>
</html>
