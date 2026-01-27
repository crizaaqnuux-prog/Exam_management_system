<?php
require '../db.php';
require '../auth.php';
checkAdmin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $role = 'invigilator';
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);

    if (!empty($username) && !empty($password) && !empty($full_name)) {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $message = "Error: Username already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role, full_name) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $hashed_password, $role, $full_name]);
                $message = "Invigilator added successfully!";
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
            }
        }
    } else {
        $message = "Error: All fields are required.";
    }
}

// Fetch all invigilators
$users = $pdo->query("SELECT * FROM users WHERE role = 'invigilator' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Invigilators</title>
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
                <a href="users.php" style="color: var(--primary-color);"><i class="fas fa-users"></i> Invigilators</a>
                <a href="assign.php"><i class="fas fa-user-check"></i> Assignments</a>
                <a href="reports.php"><i class="fas fa-file-alt"></i> Reports</a>
                <a href="../logout.php" style="color: #EF4444;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1>Manage Invigilators</h1>
        
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
            <h2><i class="fas fa-user-plus" style="color: var(--primary-color);"></i> Add New Invigilator</h2>
            <form method="POST" action="">
                <div class="dashboard-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" required placeholder="John Doe">
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required placeholder="johndoe">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                </div>
                <button type="submit" class="btn"><i class="fas fa-save" style="margin-right: 0.5rem;"></i> Add Invigilator</button>
            </form>
        </div>

        <div class="card">
            <h2><i class="fas fa-users"></i> Invigilator List</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><i class="fas fa-user-circle" style="color: #9CA3AF; margin-right: 0.5rem;"></i> <?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)) echo "<tr><td colspan='4' style='text-align:center; color:#999;'>No invigilators found.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
