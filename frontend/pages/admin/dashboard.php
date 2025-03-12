<?php
session_start(); // Start the session
// Check if user is logged in, otherwise redirect to login page
include_once '../../../backend-php/database.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login.html");
    exit();
}

$adminName = $_SESSION['admin_name'];
$sql = "SELECT COUNT(*) AS pending_users FROM users WHERE status = 'pending'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$pendingUsersCount = $row['pending_users'];

// Fetch total number of users
$queryTotalUsers = "SELECT COUNT(*) AS total_users FROM users";
$resultTotalUsers = $conn->query($queryTotalUsers);
$totalUsers = ($resultTotalUsers->num_rows > 0) ? $resultTotalUsers->fetch_assoc()['total_users'] : 0;

// Fetch total number of students
$queryStudents = "SELECT COUNT(*) AS total_students FROM users WHERE type = 'student'";
$resultStudents = $conn->query($queryStudents);
$totalStudents = ($resultStudents->num_rows > 0) ? $resultStudents->fetch_assoc()['total_students'] : 0;

// Fetch total number of librarians
$queryLibrarians = "SELECT COUNT(*) AS total_librarians FROM users WHERE type = 'librarian'";
$resultLibrarians = $conn->query($queryLibrarians);
$totalLibrarians = ($resultLibrarians->num_rows > 0) ? $resultLibrarians->fetch_assoc()['total_librarians'] : 0;

// Fetch total number of teachers
$queryTeachers = "SELECT COUNT(*) AS total_teachers FROM users WHERE type = 'teacher'";
$resultTeachers = $conn->query($queryTeachers);
$totalTeachers = ($resultTeachers->num_rows > 0) ? $resultTeachers->fetch_assoc()['total_teachers'] : 0;

// Fetch total number of library branches
$queryBranches = "SELECT COUNT(*) AS total_branches FROM library_branches";
$resultBranches = $conn->query($queryBranches);
$totalBranches = ($resultBranches->num_rows > 0) ? $resultBranches->fetch_assoc()['total_branches'] : 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --light-bg: #f8f9fa;
            --dark-text: #2b2d42;
            --light-text: #8d99ae;
            --card-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            --hover-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            --border-radius: 20px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-bg);
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 1.5rem;
            transition: all 0.3s ease;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
        }

        .sidebar .navbar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            padding: 1rem 0;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.8rem 1rem;
            margin: 0.2rem 0;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: white;
            color: var(--primary-color);
            font-weight: 600;
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 280px;
            flex-grow: 1;
            padding: 2rem;
            background: var(--light-bg);
        }

        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            padding: 2.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2.5rem;
            box-shadow: var(--card-shadow);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(100px, -150px);
        }

        .welcome-section h1 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 2.2rem;
            color: white;
        }

        .welcome-section p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            margin: 0;
        }

        .stats-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.25rem;
            height: 100%;
            transition: all 0.3s ease;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }

        .stats-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        .stats-card h3 {
            color: var(--dark-text);
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stats-card .number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
            line-height: 1;
        }

        .stats-card .icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 2rem;
            color: rgba(67, 97, 238, 0.1);
            transition: all 0.3s ease;
        }

        .stats-card:hover .icon {
            transform: scale(1.1);
            color: rgba(67, 97, 238, 0.15);
        }

        .stats-card .details {
            color: var(--light-text);
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stats-card .details i {
            color: var(--primary-color);
        }

        .action-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.25rem;
            height: 100%;
            transition: all 0.3s ease;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }

        .action-card h3 {
            color: var(--dark-text);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .action-card p {
            color: var(--light-text);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .action-button {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .generate-report-btn {
            background: linear-gradient(135deg, var(--success-color), #3db8e0);
            color: white;
        }

        .generate-report-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 201, 240, 0.3);
        }

        .file-upload-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .file-upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        .custom-file-upload {
            border: 2px dashed var(--light-text);
            border-radius: 12px;
            padding: 1.25rem;
            text-align: center;
            cursor: pointer;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            background: rgba(67, 97, 238, 0.02);
        }

        .custom-file-upload:hover {
            border-color: var(--primary-color);
            background: rgba(67, 97, 238, 0.05);
        }

        .custom-file-upload i {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .custom-file-upload div {
            color: var(--dark-text);
            font-weight: 500;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 1rem 0.5rem;
            }

            .sidebar .navbar-brand {
                font-size: 1.2rem;
                padding: 0.5rem;
            }

            .sidebar .nav-link span {
                display: none;
            }

            .main-content {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .stats-card .number {
                font-size: 2rem;
            }
        }

        /* Add security card specific styles */
        .security-card {
            padding: 1.25rem !important;
        }

        .security-icon-wrapper {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .security-icon-wrapper i {
            font-size: 1.2rem;
            color: white;
        }

        .auth-method-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.8rem;
            background: var(--light-bg);
            border-radius: 10px;
            font-size: 0.8rem;
            color: var(--dark-text);
            font-weight: 500;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .auth-method-badge:hover {
            transform: translateY(-2px);
            box-shadow: var(--card-shadow);
        }

        .auth-method-badge i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .security-card .d-flex {
                justify-content: center !important;
                text-align: center;
            }

            .auth-methods {
                margin: 1rem 0;
                display: flex;
                flex-direction: column;
                align-items: stretch;
                width: 100%;
            }

            .auth-method-badge {
                margin: 0.25rem 0 !important;
                justify-content: center;
            }

            .security-card .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="navbar-brand">📚 LMS</div>
        <div class="navbar-nav">
            <a class="nav-link active" href="#"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a class="nav-link" href="settings.php">
                <i class="fas fa-user-check"></i>
                <span>Approve Users</span>
                <?php if ($pendingUsersCount > 0): ?>
                    <span class="badge badge-danger"><?php echo $pendingUsersCount; ?></span>
                <?php endif; ?>
            </a>
            <a class="nav-link" href="view_users.php"><i class="fas fa-users"></i><span>View Users</span></a>
            <a class="nav-link" href="add_user.php"><i class="fas fa-user-plus"></i><span>Add User</span></a>
          
            <a class="nav-link" href="view_branch.php"><i class="fas fa-building"></i><span>View Branches</span></a>
            <a class="nav-link" href="add_branch.php"><i class="fas fa-plus-circle"></i><span>Add Branch</span></a>
           
            <a class="nav-link" href="assign_librarian.html"><i class="fas fa-user-tie"></i><span>Assign Librarian</span></a>
            <a class="nav-link" href="admin_dashboard.php"><i class="fas fa-user-shield"></i><span>Assign Role</span></a>
            <a class="nav-link" href="action_log.php"><i class="fas fa-history"></i><span>Librarian Actions</span></a>
            <a class="nav-link" href="../../login.html"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="welcome-section">
            <h1>Welcome back, <?php echo htmlspecialchars($adminName); ?>! 👋</h1>
            <p>Here's what's happening in your library system today.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4 mb-4">
                <div class="stats-card">
                    <i class="fas fa-users icon"></i>
                    <h3><i class="fas fa-users"></i> Total Users</h3>
                    <div class="number"><?php echo $totalUsers; ?></div>
                    <div class="details">
                        <span><i class="fas fa-graduation-cap"></i> <?php echo $totalStudents; ?> Students</span>
                        <span><i class="fas fa-chalkboard-teacher"></i> <?php echo $totalTeachers; ?> Teachers</span>
                        <span><i class="fas fa-book-reader"></i> <?php echo $totalLibrarians; ?> Librarians</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="stats-card">
                    <i class="fas fa-building icon"></i>
                    <h3><i class="fas fa-building"></i> Library Branches</h3>
                    <div class="number"><?php echo $totalBranches; ?></div>
                    <div class="details">
                        <span><i class="fas fa-map-marker-alt"></i> Active locations</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="stats-card">
                    <i class="fas fa-clock icon"></i>
                    <h3><i class="fas fa-user-clock"></i> Pending Approvals</h3>
                    <div class="number"><?php echo $pendingUsersCount; ?></div>
                    <div class="details">
                        <span><i class="fas fa-hourglass-half"></i> Waiting for review</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="stats-card security-card">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center mb-3 mb-md-0">
                            <div class="security-icon-wrapper">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="mb-1" style="margin-bottom: 0.25rem !important;">Two-Factor Authentication</h3>
                                <p class="text-muted mb-0" style="font-size: 0.9rem;">Enhance your account security</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-wrap">
                            <div class="auth-methods mr-3">
                                <span class="auth-method-badge">
                                    <i class="fas fa-envelope"></i> Email
                                </span>
                                <span class="auth-method-badge ml-2">
                                    <i class="fas fa-qrcode"></i> Auth App
                                </span>
                            </div>
                            <a href="../two_factor_settings.php" class="btn btn-primary">Configure 2FA</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 g-4">
            <div class="col-md-6 mb-4">
                <div class="action-card">
                    <h3><i class="fas fa-chart-bar"></i> Generate Report</h3>
                    <p>Generate comprehensive system reports with detailed analytics and insights.</p>
                    <button class="action-button generate-report-btn" onclick="window.location.href='generate_report.php';">
                        <i class="fas fa-file-alt"></i> Generate Report
                    </button>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="action-card">
                    <h3><i class="fas fa-cloud-upload-alt"></i> Upload Report</h3>
                    <p>Upload and share system reports with other administrators.</p>
                    <form action="send_report.php" method="POST" enctype="multipart/form-data">
                        <label class="custom-file-upload">
                            <input type="file" name="reportFile" style="display: none;" required>
                            <i class="fas fa-cloud-upload-alt fa-2x"></i>
                            <div>Click or drag file to upload</div>
                        </label>
                        <button type="submit" class="action-button file-upload-btn">
                            <i class="fas fa-paper-plane"></i> Send Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // File upload preview
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            let fileName = e.target.files[0].name;
            let label = this.closest('.custom-file-upload');
            label.innerHTML = `<i class="fas fa-file-alt fa-2x mb-2"></i><div>${fileName}</div>`;
        });

        // Add hover effect to sidebar links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('mouseenter', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'translateX(5px)';
                }
            });
            link.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });
    </script>
</body>
</html>