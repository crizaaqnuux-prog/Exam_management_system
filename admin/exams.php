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
                <i class="fas fa-shield-alt" style="color: var(--primary-color); margin-right: 8px;"></i> 
                Admin Portal
            </div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="exams.php" style="color: var(--primary-color);"><i class="fas fa-calendar-alt"></i> Exams</a>
                <a href="users.php"><i class="fas fa-users"></i> Invigilators</a>
                <a href="assign.php"><i class="fas fa-user-check"></i> Assignments</a>
                <a href="reports.php"><i class="fas fa-file-alt"></i> Reports</a>
                <a href="../logout.php" style="color: #EF4444;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1>Manage Exams</h1>
        
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

        <div class="card">
            <h2><i class="fas fa-plus-circle" style="color: var(--primary-color);"></i> Create New Exam</h2>
            <form method="POST" action="">
                <div class="dashboard-grid">
                    <div class="form-group">
                        <label>Course Code</label>
                        <input type="text" name="course_code" required placeholder="e.g. CS101">
                    </div>
                    <div class="form-group">
                        <label>Course Name</label>
                        <input type="text" name="course_name" required placeholder="e.g. Intro to CS">
                    </div>
                </div>
                <div class="dashboard-grid">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="exam_date" required>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" required placeholder="e.g. Room 305">
                    </div>
                </div>
                <div class="dashboard-grid">
                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="time" name="start_time" required>
                    </div>
                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" name="end_time" required>
                    </div>
                </div>
                <button type="submit" class="btn"><i class="fas fa-save" style="margin-right: 0.5rem;"></i> Create Exam</button>
            </form>
        </div>

        <div class="card">
            <h2><i class="fas fa-list"></i> Exam List</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Course Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exams as $exam): ?>
                        <tr>
                            <td><?php echo $exam['id']; ?></td>
                            <td><span style="font-weight: 600; color: var(--primary-color);"><?php echo htmlspecialchars($exam['course_code']); ?></span></td>
                            <td><?php echo htmlspecialchars($exam['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($exam['exam_date']); ?></td>
                            <td><?php echo htmlspecialchars($exam['start_time'] . ' - ' . $exam['end_time']); ?></td>
                            <td><i class="fas fa-map-marker-alt" style="color: #9CA3AF;"></i> <?php echo htmlspecialchars($exam['location']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($exams)) echo "<tr><td colspan='6' style='text-align:center; color:#999;'>No exams found.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
