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
                <i class="fas fa-shield-alt" style="color: var(--primary-color); margin-right: 8px;"></i> 
                Admin Portal
            </div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="exams.php"><i class="fas fa-calendar-alt"></i> Exams</a>
                <a href="users.php"><i class="fas fa-users"></i> Invigilators</a>
                <a href="assign.php" style="color: var(--primary-color);"><i class="fas fa-user-check"></i> Assignments</a>
                <a href="reports.php"><i class="fas fa-file-alt"></i> Reports</a>
                <a href="../logout.php" style="color: #EF4444;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1>Assign Invigilators</h1>

        <?php if ($message): ?>
            <div class="card" style="background-color: <?php echo strpos($message, 'Error') !== false ? '#FEE2E2' : '#D1FAE5'; ?>; color: <?php echo strpos($message, 'Error') !== false ? '#B91C1C' : '#065F46'; ?>; padding: 1rem; margin-bottom: 2rem;">
                <?php if(strpos($message, 'Error') !== false): ?>
                    <i class="fas fa-exclamation-circle"></i>
                <?php else: ?>
                    <i class="fas fa-check-circle"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <div class="card">
                <h2><i class="fas fa-link" style="color: var(--primary-color);"></i> New Assignment</h2>
                <form method="POST" action="">
                    <input type="hidden" name="assign" value="1">
                    <div class="form-group">
                        <label>Select Exam</label>
                        <div style="position: relative;">
                            <select name="exam_id" required style="padding-left: 2.5rem;">
                                <option value="">-- Select Exam --</option>
                                <?php foreach ($exams as $exam): ?>
                                    <option value="<?php echo $exam['id']; ?>">
                                        <?php echo htmlspecialchars($exam['course_code'] . ' - ' . $exam['exam_date']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-calendar" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9CA3AF;"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Select Invigilator</label>
                        <div style="position: relative;">
                            <select name="user_id" required style="padding-left: 2.5rem;">
                                <option value="">-- Select Invigilator --</option>
                                <?php foreach ($invigilators as $user): ?>
                                    <option value="<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9CA3AF;"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn"><i class="fas fa-check" style="margin-right: 0.5rem;"></i> Assign</button>
                </form>
            </div>

            <div class="card" style="grid-column: span 2;">
                <h2>Current Assignments</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Exam</th>
                                <th>Date & Time</th>
                                <th>Invigilator</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $a): ?>
                            <tr>
                                <td><span style="font-weight: 600; color: var(--primary-color);"><?php echo htmlspecialchars($a['course_code'] . ' - ' . $a['course_name']); ?></span></td>
                                <td><i class="fas fa-clock" style="color: #9CA3AF;"></i> <?php echo htmlspecialchars($a['exam_date'] . ' ' . $a['start_time']); ?></td>
                                <td><?php echo htmlspecialchars($a['full_name']); ?></td>
                                <td>
                                    <a href="assign.php?delete=<?php echo $a['id']; ?>" style="color: #EF4444;" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Remove</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
