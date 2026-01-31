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
                <div class="logo-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <span>Admin Portal</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
                <a href="exams.php"><i class="fas fa-calendar-alt"></i> <span>Exams</span></a>
                <a href="users.php" class="active"><i class="fas fa-users"></i> <span>Invigilators</span></a>
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
                <h1>Manage Invigilators</h1>
                <p>Register and manage staff authorized for invigilation.</p>
            </div>
        
        <?php if ($message): ?>
            <div class="stat-card <?php echo strpos($message, 'Error') !== false ? 'warning' : 'primary'; ?>" style="margin-bottom: 2rem; padding: 1rem;">
                <div class="stat-icon">
                    <i class="fas <?php echo strpos($message, 'Error') !== false ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-title"><?php echo strpos($message, 'Error') !== false ? 'Notice' : 'Success'; ?></div>
                    <div class="stat-value" style="font-size: 1rem;"><?php echo htmlspecialchars($message); ?></div>
                </div>
            </div>
        <?php endif; ?>

        <div class="content-card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h2><i class="fas fa-user-plus"></i> Add New Invigilator</h2>
            </div>
            <form method="POST" action="" style="padding: 2rem;">
                <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> Full Name</label>
                        <input type="text" name="full_name" required placeholder="Enter staff full name">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user-tag"></i> Username</label>
                        <input type="text" name="username" required placeholder="Choose a username">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-key"></i> Initial Password</label>
                        <input type="password" name="password" required placeholder="Create a secure password">
                    </div>
                </div>
                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn"><i class="fas fa-save" style="margin-right: 0.5rem;"></i> Register Invigilator</button>
                </div>
            </form>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2><i class="fas fa-users"></i> Authorized Invigilators</h2>
            </div>
            <div class="table-responsive">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Invigilator Details</th>
                            <th>Username</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div class="user-avatar" style="width: 40px; height: 40px; background: #f1f5f9; color: var(--primary-color);">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                        <span style="font-size: 0.8rem; color: var(--text-muted);">Staff Member</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code style="background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-family: 'Outfit', sans-serif; font-weight: 500;"><?php echo htmlspecialchars($user['username']); ?></code>
                            </td>
                            <td>
                                <span style="font-size: 0.9rem; color: var(--text-muted);">
                                    <i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                </span>
                            </td>
                            <td>
                                <span class="time-badge" style="background: #ecfdf5; color: #059669; border: 1px solid #d1fae5;">
                                    <i class="fas fa-check-circle"></i> Active
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="4" class="empty-state">No invigilators found in the system.</td>
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
