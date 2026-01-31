<?php
require '../db.php';
require '../auth.php';
checkAdmin();

// 1. Most active courses
$course_stats = $pdo->query("SELECT course_code, COUNT(*) as exam_count FROM exams GROUP BY course_code ORDER BY exam_count DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// 2. Incident Analytics
$total_reports = $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
$incidents_count = $pdo->query("SELECT COUNT(*) FROM reports WHERE incidents IS NOT NULL AND incidents != '' AND LOWER(incidents) != 'none'")->fetchColumn();
$incident_rate = ($total_reports > 0) ? round(($incidents_count / $total_reports) * 100, 1) : 0;

// 3. Invigilator Productivity (top 5 by assignments)
$invigilator_stats = $pdo->query("
    SELECT u.full_name, COUNT(a.id) as assignment_count 
    FROM users u 
    JOIN assignments a ON u.id = a.user_id 
    WHERE u.role = 'invigilator' 
    GROUP BY u.id 
    ORDER BY assignment_count DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// 4. Attendance Trends by Course
$attendance_stats = $pdo->query("
    SELECT e.course_code, AVG(r.attendance_count) as avg_attendance 
    FROM exams e 
    JOIN reports r ON e.id = r.exam_id 
    GROUP BY e.course_code 
    ORDER BY avg_attendance DESC
")->fetchAll(PDO::FETCH_ASSOC);

// 5. Monthly Exam Volume
$monthly_volume = $pdo->query("SELECT DATE_FORMAT(exam_date, '%M %Y') as month, COUNT(*) as count FROM exams GROUP BY month ORDER BY MIN(exam_date) DESC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Intelligence | Analytics Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --chart-primary: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --chart-secondary: linear-gradient(135deg, #10b981 0%, #3b82f6 100%);
            --chart-warning: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
        }

        .analytics-container {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .card-full { grid-column: span 12; }
        .card-half { grid-column: span 6; }
        .card-third { grid-column: span 4; }

        .viz-card {
            background: white;
            border-radius: 1.25rem;
            padding: 2rem;
            box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(226, 232, 240, 0.8);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .viz-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.15);
        }

        .viz-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .viz-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .viz-title i {
            width: 32px;
            height: 32px;
            background: #f1f5f9;
            color: var(--primary-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        /* Modern Bar Chart Styles */
        .chart-container {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .bar-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .bar-label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
        }

        .bar-label {
            font-weight: 600;
            color: #475569;
        }

        .bar-value {
            font-weight: 700;
            color: #1e293b;
        }

        .bar-track {
            height: 10px;
            background: #f1f5f9;
            border-radius: 99px;
            overflow: hidden;
            position: relative;
        }

        .bar-progress {
            height: 100%;
            border-radius: 99px;
            width: 0;
            transition: width 1s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes scanner {
            0% { transform: translateY(-100%); opacity: 0; }
            50% { opacity: 0.5; }
            100% { transform: translateY(300%); opacity: 0; }
        }

        @keyframes ai-glow {
            0% { box-shadow: 0 0 15px rgba(99, 102, 241, 0.2); }
            50% { box-shadow: 0 0 30px rgba(99, 102, 241, 0.4); }
            100% { box-shadow: 0 0 15px rgba(99, 102, 241, 0.2); }
        }

        .intelligence-summary {
            background: linear-gradient(160deg, #0f172a 0%, #1e1b4b 100%);
            color: white;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(99, 102, 241, 0.2) !important;
            animation: ai-glow 4s infinite ease-in-out;
        }

        .intelligence-summary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary-light), transparent);
            animation: scanner 3s infinite linear;
            z-index: 1;
        }

        .insight-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.15rem;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(4px);
            border-radius: 14px;
            margin-bottom: 0.85rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }

        .insight-pill:hover {
            background: rgba(255, 255, 255, 0.07);
            border-color: rgba(99, 102, 241, 0.3);
            transform: translateX(5px);
        }

        .insight-tag {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            opacity: 0.6;
        }

        .insight-status {
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 0.8rem;
        }

        @media (max-width: 1024px) {
            .card-half, .card-third { grid-column: span 12; }
        }
    </style>
</head>
<body onload="animateBars()">
    <header>
        <nav>
            <div class="logo">
                <div class="logo-icon"><i class="fas fa-shield-alt"></i></div>
                <span>Admin Portal</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
                <a href="exams.php"><i class="fas fa-calendar-alt"></i> <span>Exams</span></a>
                <a href="users.php"><i class="fas fa-users"></i> <span>Invigilators</span></a>
                <a href="assign.php"><i class="fas fa-user-check"></i> <span>Assignments</span></a>
                <a href="reports.php"><i class="fas fa-file-alt"></i> <span>Reports</span></a>
                <a href="analytics.php" class="active"><i class="fas fa-chart-line"></i> <span>Analytics</span></a>
            </div>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar"><i class="fas fa-user"></i></div>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                </div>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </nav>
    </header>

    <div class="main-content">
        <div class="container">
            <div class="welcome-section">
                <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                    <div>
                        <h1>System Intelligence</h1>
                        <p>Exploring trends, performance metrics, and operational health.</p>
                    </div>
                    <div class="time-badge" style="background: white; border: 1px solid var(--border-color);">
                        <i class="far fa-clock"></i> Live Data Access
                    </div>
                </div>
            </div>

            <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); margin-bottom: 2rem;">
                <div class="stat-card primary">
                    <div class="stat-icon"><i class="fas fa-shield-virus"></i></div>
                    <div class="stat-details">
                        <div class="stat-title">Incident Rate</div>
                        <div class="stat-value"><?php echo $incident_rate; ?>%</div>
                    </div>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon"><i class="fas fa-book-reader"></i></div>
                    <div class="stat-details">
                        <div class="stat-title">Total Modules</div>
                        <div class="stat-value"><?php echo count($attendance_stats); ?></div>
                    </div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                    <div class="stat-details">
                        <div class="stat-title">Avg. Attendance</div>
                        <div class="stat-value">
                            <?php 
                                $all_avg = array_column($attendance_stats, 'avg_attendance');
                                echo !empty($all_avg) ? round(array_sum($all_avg) / count($all_avg), 1) : 0;
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="analytics-container">
                <!-- Course Concentration -->
                <div class="viz-card card-half" style="background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.03), transparent 70%);">
                    <div class="viz-header">
                        <div class="viz-title">
                            <i class="fas fa-layer-group" style="background: #fef2f2; color: #ef4444;"></i>
                            Course Distribution
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Popular Modules</span>
                    </div>
                    <div class="chart-container" style="gap: 1.5rem;">
                        <?php if (empty($course_stats)): ?>
                            <div style="text-align:center; padding: 3rem;">
                                <i class="fas fa-box-open" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 1rem; display: block;"></i>
                                <p style="color: var(--text-muted);">No census data retrieved</p>
                            </div>
                        <?php else: 
                            $max = $course_stats[0]['exam_count'];
                            $colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
                            foreach($course_stats as $index => $s): 
                                $color = $colors[$index % count($colors)];
                        ?>
                            <div class="bar-group" style="position: relative;">
                                <div class="bar-label-row">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 24px; height: 24px; border-radius: 6px; background: <?php echo $color; ?>15; color: <?php echo $color; ?>; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 800; border: 1px solid <?php echo $color; ?>30;">
                                            <?php echo substr($s['course_code'], 0, 1); ?>
                                        </div>
                                        <span class="bar-label"><?php echo htmlspecialchars($s['course_code']); ?></span>
                                    </div>
                                    <span class="bar-value" style="color: <?php echo $color; ?>;"><?php echo $s['exam_count']; ?> <span style="font-size: 0.7rem; opacity: 0.6; font-weight: 500;">Sessions</span></span>
                                </div>
                                <div class="bar-track" style="height: 12px; background: #f8fafc; border: 1px solid #f1f5f9;">
                                    <div class="bar-progress" 
                                         data-width="<?php echo ($s['exam_count']/$max)*100; ?>%" 
                                         style="background: <?php echo $color; ?>; box-shadow: 0 0 10px <?php echo $color; ?>40;">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Invigilator Load -->
                <div class="viz-card card-half" style="background: radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.03), transparent 70%);">
                    <div class="viz-header">
                        <div class="viz-title">
                            <i class="fas fa-user-check" style="background: #ecfdf5; color: #10b981;"></i>
                            Staff Utilization
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Resource Allocation</span>
                    </div>
                    <div class="chart-container" style="gap: 1.5rem;">
                        <?php if (empty($invigilator_stats)): ?>
                            <div style="text-align:center; padding: 3rem;">
                                <i class="fas fa-users-slash" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 1rem; display: block;"></i>
                                <p style="color: var(--text-muted);">Allocation data unavailable</p>
                            </div>
                        <?php else: 
                            $max = $invigilator_stats[0]['assignment_count'];
                            foreach($invigilator_stats as $s): 
                                // Generate initials
                                $words = explode(" ", $s['full_name']);
                                $initials = "";
                                foreach ($words as $w) $initials .= strtoupper($w[0]);
                                if(strlen($initials) > 2) $initials = substr($initials, 0, 2);
                        ?>
                            <div class="bar-group">
                                <div class="bar-label-row">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 28px; height: 28px; border-radius: 50%; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 700; border: 1px solid #dbeafe;">
                                            <?php echo $initials; ?>
                                        </div>
                                        <span class="bar-label" style="font-weight: 600;"><?php echo htmlspecialchars($s['full_name']); ?></span>
                                    </div>
                                    <span class="bar-value" style="color: #0369a1;"><?php echo $s['assignment_count']; ?> <span style="font-size: 0.7rem; opacity: 0.6; font-weight: 500;">Duties</span></span>
                                </div>
                                <div class="bar-track" style="height: 12px; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 4px;">
                                    <div class="bar-progress" 
                                         data-width="<?php echo ($s['assignment_count']/$max)*100; ?>%" 
                                         style="background: linear-gradient(90deg, #3b82f6, #06b6d4); box-shadow: 0 0 10px rgba(6, 182, 212, 0.3); border-radius: 4px;">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Monthly Activity -->
                <div class="viz-card card-third" style="background: radial-gradient(circle at top right, rgba(245, 158, 11, 0.03), transparent 70%);">
                    <div class="viz-header">
                        <div class="viz-title">
                            <i class="fas fa-wave-square" style="background: #fffbeb; color: #f59e0b;"></i>
                            Seasonal Flow
                        </div>
                        <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Volume Trends</span>
                    </div>
                    <div class="chart-container" style="gap: 1.25rem;">
                         <?php if (empty($monthly_volume)): ?>
                            <div style="text-align:center; padding: 2rem;">
                                <i class="fas fa-calendar-xmark" style="font-size: 2.5rem; color: #e2e8f0; margin-bottom: 1rem; display: block;"></i>
                                <p style="color: var(--text-muted); font-size: 0.85rem;">No seasonal data</p>
                            </div>
                        <?php else: 
                            $max = max(array_column($monthly_volume, 'count'));
                            foreach($monthly_volume as $s): 
                        ?>
                            <div class="bar-group">
                                <div class="bar-label-row">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="far fa-calendar-alt" style="font-size: 0.75rem; color: #f59e0b;"></i>
                                        <span class="bar-label" style="font-size: 0.8rem; font-weight: 600;"><?php echo htmlspecialchars($s['month']); ?></span>
                                    </div>
                                    <span class="bar-value" style="font-weight: 700; color: #b45309;"><?php echo $s['count']; ?></span>
                                </div>
                                <div class="bar-track" style="height: 8px; border-radius: 99px; background: #fffbeb;">
                                    <div class="bar-progress" 
                                         data-width="<?php echo ($s['count']/$max)*100; ?>%" 
                                         style="background: linear-gradient(90deg, #f59e0b, #fbbf24); border-radius: 99px;">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Intelligence Summary -->
                <div class="viz-card card-third intelligence-summary" style="border: none; box-shadow: var(--shadow-lg);">
                    <div class="viz-header" style="margin-bottom: 1.5rem; position: relative; z-index: 2;">
                        <div class="viz-title" style="color: white; gap: 0.75rem;">
                            <div style="position: relative;">
                                <i class="fas fa-brain-circuit" style="font-size: 1.2rem; color: var(--primary-light);"></i>
                                <span style="position: absolute; top: -2px; right: -2px; width: 6px; height: 6px; background: #4ade80; border-radius: 50%; box-shadow: 0 0 8px #4ade80;"></span>
                            </div>
                            <span>Neural Core Insights</span>
                        </div>
                        <div class="timeline-tag" style="background: rgba(99, 102, 241, 0.2); color: var(--primary-light); padding: 4px 10px; border-radius: 99px; font-size: 0.6rem; border: 1px solid rgba(99, 102, 241, 0.3);">
                            <i class="fas fa-microchip" style="margin-right: 4px;"></i> Live Processing
                        </div>
                    </div>

                    <div style="position: relative; z-index: 2;">
                        <div class="insight-pill">
                            <div>
                                <div class="insight-tag" style="color: rgba(255,255,255,0.5); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.05em;">SYSTEM HEALTH</div>
                                <div style="font-size: 0.95rem; font-weight: 600; margin-top: 4px; color: #fff;">Operational Integrity</div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span class="insight-status" style="background: rgba(74, 222, 128, 0.15); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.2); box-shadow: 0 0 10px rgba(74, 222, 128, 0.1);">Verified</span>
                            </div>
                        </div>

                        <div class="insight-pill">
                            <div>
                                <div class="insight-tag" style="color: rgba(255,255,255,0.5); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.05em;">RESOURCE ENGINE</div>
                                <div style="font-size: 0.95rem; font-weight: 600; margin-top: 4px; color: #fff;">Allocation Balance</div>
                            </div>
                            <span class="insight-status" style="background: rgba(96, 165, 250, 0.15); color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.2);">Optimal</span>
                        </div>

                        <div class="insight-pill" style="margin-bottom: 0;">
                            <div>
                                <div class="insight-tag" style="color: rgba(255,255,255,0.5); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.05em;">SECURITY VECTOR</div>
                                <div style="font-size: 0.95rem; font-weight: 600; margin-top: 4px; color: #fff;">Incident Threshold</div>
                            </div>
                            <span class="insight-status" style="background: rgba(251, 191, 36, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.2);">Safe</span>
                        </div>

                        <div style="margin-top: 1.5rem; padding: 1.25rem; background: rgba(99, 102, 241, 0.08); border-radius: 12px; border: 1px solid rgba(99, 102, 241, 0.15); position: relative;">
                            <div style="position: absolute; top: -10px; left: 20px; background: #1e1b4b; padding: 0 10px; font-size: 0.6rem; font-weight: 800; color: var(--primary-light); text-transform: uppercase; letter-spacing: 0.1em;">Smart Recommendation</div>
                            <p style="font-size: 0.8rem; opacity: 0.9; line-height: 1.6; color: #cbd5e1; font-style: italic;">
                                "Neural analysis complete. Optimized staff rotation identified for peak AM slots. Recommend immediate implementation to maintain 100% integrity."
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Attendance Benchmarks -->
                <div class="viz-card card-third" style="background: radial-gradient(circle at bottom right, rgba(99, 102, 241, 0.03), transparent 70%);">
                    <div class="viz-header">
                        <div class="viz-title">
                            <i class="fas fa-users-viewfinder" style="background: #eef2ff; color: var(--primary-color);"></i>
                            Attendance Peak
                        </div>
                        <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Top Performers</span>
                    </div>
                    <div class="chart-container" style="gap: 1rem;">
                        <?php 
                        $top_att = array_slice($attendance_stats, 0, 3);
                        foreach($top_att as $index => $s): 
                        ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: #fff; border: 1px solid #f1f5f9; border-radius: 16px; transition: all 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='var(--shadow-md)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 44px; height: 44px; border-radius: 12px; background: #f8fafc; display:flex; align-items:center; justify-content:center; border: 1px solid #e2e8f0; font-weight:800; color:var(--primary-color); font-size: 0.9rem;">
                                    <?php echo substr($s['course_code'], 0, 2); ?>
                                </div>
                                <div>
                                    <div style="font-size: 0.9rem; font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($s['course_code']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">Avg. Attendance</div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 1.1rem; font-weight: 800; color: var(--primary-color);"><?php echo round($s['avg_attendance']); ?></div>
                                <div style="font-size: 0.65rem; color: var(--text-light); text-transform: uppercase; font-weight: 600;">Pax</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top: auto; padding-top: 1.5rem;">
                         <button class="btn" style="width: 100%; padding: 1rem; background: var(--primary-color); border: none; font-size: 0.9rem; border-radius: 12px;">
                            <i class="fas fa-file-pdf" style="margin-right: 8px;"></i> Export Analytics Pack
                         </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function animateBars() {
            const bars = document.querySelectorAll('.bar-progress');
            setTimeout(() => {
                bars.forEach(bar => {
                    bar.style.width = bar.getAttribute('data-width');
                });
            }, 300);
        }
    </script>
</body>
</html>
