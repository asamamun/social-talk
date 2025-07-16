<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Social_Talk</title>
    <link rel="icon" type="image/x-icon" href="<?= settings()['root'] ?>assets/logo/logo1.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="../assets/js/admin.js" defer></script>

</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
            <h3><a class="navbar-brand pulse" href="<?= settings()['adminpage'] ?>index.php">
                <img src="<?= settings()['root'] ?>assets/logo/logo.png" alt="Social-Talk Logo" style="height: 40px; width: 120px;">
            </a>
                 Admin</h3>
            </div>

            <ul class="list-unstyled components">
                <li class="active">
                    <a href="index.php" data-section="dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="users-section.php" data-section="users">
                        <i class="fas fa-users"></i>
                        User Management
                    </a>
                </li>
                <li>
                    <a href="posts-section.php" data-section="posts">
                        <i class="fas fa-newspaper"></i>
                        Post Management
                    </a>
                </li>
                <li>
                    <a href="reports-section.php" data-section="reports">
                        <i class="fas fa-flag"></i>
                        Reports
                        <span class="badge bg-danger float-end">5</span>
                    </a>
                </li>
                <li>
                    <a href="analytics-section.php" data-section="analytics">
                        <i class="fas fa-chart-line"></i>
                        Analytics
                    </a>
                </li>
                <li>
                    <a href="settings-section.php" data-section="settings">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div class="content">
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-primary">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="ms-auto d-flex align-items-center">
                        <div class="dropdown me-3">
                            <button class="btn btn-light dropdown-toggle" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <span class="badge bg-danger">5</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                                <li><h6 class="dropdown-header">Notifications</h6></li>
                                <li><a class="dropdown-item" href="#">New report submitted</a></li>
                                <li><a class="dropdown-item" href="#">User registration</a></li>
                                <li><a class="dropdown-item" href="#">System update available</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= settings()['adminpage'] ?>all-user.php">View all</a></li>
                            </ul>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                Admin User
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= settings()['homepage'] ?>logout.php" id="logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            