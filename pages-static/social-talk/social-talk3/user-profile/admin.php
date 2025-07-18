<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | SocialNet</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1877f2;
            --secondary-color: #42b883;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --dark-color: #2c3e50;
            --light-bg: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }

        .sidebar {
            min-height: 100vh;
            background-color: var(--dark-color);
            color: white;
            width: 250px;
            transition: all 0.3s;
            position: fixed;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.2);
        }

        .sidebar ul.components {
            padding: 20px 0;
        }

        .sidebar ul li a {
            padding: 12px 20px;
            font-size: 1em;
            display: block;
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s;
        }

        .sidebar ul li a:hover {
            color: white;
            background-color: rgba(0, 0, 0, 0.2);
            text-decoration: none;
        }

        .sidebar ul li.active > a {
            color: white;
            background-color: var(--primary-color);
        }

        .sidebar ul li a i {
            margin-right: 10px;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s;
        }

        .navbar {
            padding: 15px 10px;
            background: #fff;
            border: none;
            border-radius: 0;
            margin-bottom: 40px;
            box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
        }

        .navbar-btn {
            box-shadow: none;
            outline: none !important;
            border: none;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0 !important;
        }

        .stat-card {
            border-left: 4px solid;
            border-radius: 8px;
        }

        .stat-card.primary {
            border-left-color: var(--primary-color);
        }

        .stat-card.success {
            border-left-color: var(--success-color);
        }

        .stat-card.warning {
            border-left-color: var(--warning-color);
        }

        .stat-card.danger {
            border-left-color: var(--danger-color);
        }

        .stat-card .stat-icon {
            font-size: 2rem;
            opacity: 0.5;
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--dark-color);
        }

        .badge-admin {
            background-color: var(--danger-color);
        }

        .badge-user {
            background-color: var(--primary-color);
        }

        .badge-banned {
            background-color: var(--warning-color);
        }

        .badge-deleted {
            background-color: var(--dark-color);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .action-btn {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .content {
                margin-left: 0;
            }
            .content.active {
                margin-left: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h3>SocialNet Admin</h3>
            </div>

            <ul class="list-unstyled components">
                <li class="active">
                    <a href="#dashboard" data-section="dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="#users" data-section="users">
                        <i class="fas fa-users"></i>
                        User Management
                    </a>
                </li>
                <li>
                    <a href="#posts" data-section="posts">
                        <i class="fas fa-newspaper"></i>
                        Post Management
                    </a>
                </li>
                <li>
                    <a href="#reports" data-section="reports">
                        <i class="fas fa-flag"></i>
                        Reports
                        <span class="badge bg-danger float-end">5</span>
                    </a>
                </li>
                <li>
                    <a href="#analytics" data-section="analytics">
                        <i class="fas fa-chart-line"></i>
                        Analytics
                    </a>
                </li>
                <li>
                    <a href="#settings" data-section="settings">
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
                                <li><a class="dropdown-item" href="#">View all</a></li>
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
                                <li><a class="dropdown-item" href="#" id="logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Dashboard Section -->
            <div id="dashboard-section">
                <h2 class="mb-4">Dashboard Overview</h2>
                
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted">Total Users</h6>
                                        <h3>1,254</h3>
                                        <span class="text-success"><i class="fas fa-arrow-up"></i> 12.5%</span> from last week
                                    </div>
                                    <div class="stat-icon text-primary">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted">Active Posts</h6>
                                        <h3>4,782</h3>
                                        <span class="text-success"><i class="fas fa-arrow-up"></i> 8.3%</span> from last week
                                    </div>
                                    <div class="stat-icon text-success">
                                        <i class="fas fa-newspaper"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted">Pending Reports</h6>
                                        <h3>23</h3>
                                        <span class="text-danger"><i class="fas fa-arrow-down"></i> 4.2%</span> from last week
                                    </div>
                                    <div class="stat-icon text-warning">
                                        <i class="fas fa-flag"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted">Banned Users</h6>
                                        <h3>42</h3>
                                        <span class="text-success"><i class="fas fa-arrow-up"></i> 2.1%</span> from last week
                                    </div>
                                    <div class="stat-icon text-danger">
                                        <i class="fas fa-user-slash"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>User Activity</span>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="activityDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            Last 7 Days
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="activityDropdown">
                                            <li><a class="dropdown-item" href="#">Today</a></li>
                                            <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                                            <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                                            <li><a class="dropdown-item" href="#">This Year</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="activityChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <span>User Distribution</span>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="userDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Recent Activity</span>
                                    <button class="btn btn-sm btn-primary">View All</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Activity</th>
                                                <th>Time</th>
                                                <th>IP Address</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <img src="https://images.unsplash.com/photo-1494790108755-2616b612b820?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                                    Sarah Johnson
                                                </td>
                                                <td>Created a new post</td>
                                                <td>5 minutes ago</td>
                                                <td>192.168.1.1</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">View</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                                    Mike Chen
                                                </td>
                                                <td>Reported a post</td>
                                                <td>12 minutes ago</td>
                                                <td>172.16.0.5</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">Review</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                                    Emma Wilson
                                                </td>
                                                <td>Updated profile</td>
                                                <td>25 minutes ago</td>
                                                <td>10.0.0.3</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">View</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                                    Alex Rodriguez
                                                </td>
                                                <td>Sent friend request</td>
                                                <td>1 hour ago</td>
                                                <td>192.168.1.15</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">View</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Management Section -->
            <div id="users-section" class="d-none">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>User Management</h2>
                    <div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus me-2"></i>Add User
                        </button>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search users...">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    <div class="dropdown me-2">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-filter me-1"></i>Filter
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                                            <li><a class="dropdown-item" href="#">All Users</a></li>
                                            <li><a class="dropdown-item" href="#">Active</a></li>
                                            <li><a class="dropdown-item" href="#">Banned</a></li>
                                            <li><a class="dropdown-item" href="#">Admins</a></li>
                                        </ul>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-sort me-1"></i>Sort
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdown">
                                            <li><a class="dropdown-item" href="#">Newest First</a></li>
                                            <li><a class="dropdown-item" href="#">Oldest First</a></li>
                                            <li><a class="dropdown-item" href="#">A-Z</a></li>
                                            <li><a class="dropdown-item" href="#">Z-A</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                            John Doe
                                        </td>
                                        <td>john.doe@example.com</td>
                                        <td><span class="badge badge-admin">Admin</span></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td>Jan 15, 2023</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="Edit" onclick="editUser('1')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger me-1 action-btn" title="Ban" onclick="banUser('1')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary action-btn" title="Delete" onclick="deleteUser('1')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <img src="https://images.unsplash.com/photo-1494790108755-2616b612b820?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                            Sarah Johnson
                                        </td>
                                        <td>sarah.j@example.com</td>
                                        <td><span class="badge badge-user">User</span></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td>Feb 3, 2023</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="Edit" onclick="editUser('2')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger me-1 action-btn" title="Ban" onclick="banUser('2')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary action-btn" title="Delete" onclick="deleteUser('2')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                            Mike Chen
                                        </td>
                                        <td>mike.chen@example.com</td>
                                        <td><span class="badge badge-user">User</span></td>
                                        <td><span class="badge badge-banned">Banned</span></td>
                                        <td>Mar 10, 2023</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="Edit" onclick="editUser('3')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success me-1 action-btn" title="Unban" onclick="unbanUser('3')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary action-btn" title="Delete" onclick="deleteUser('3')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                            Emma Wilson
                                        </td>
                                        <td>emma.w@example.com</td>
                                        <td><span class="badge badge-user">User</span></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td>Apr 5, 2023</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="Edit" onclick="editUser('4')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger me-1 action-btn" title="Ban" onclick="banUser('4')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary action-btn" title="Delete" onclick="deleteUser('4')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                            Alex Rodriguez
                                        </td>
                                        <td>alex.r@example.com</td>
                                        <td><span class="badge badge-deleted">Deleted</span></td>
                                        <td><span class="badge bg-secondary">Inactive</span></td>
                                        <td>May 12, 2023</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="Edit" onclick="editUser('5')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success me-1 action-btn" title="Restore" onclick="restoreUser('5')">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary action-btn" title="Delete" onclick="deleteUser('5')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Post Management Section -->
            <div id="posts-section" class="d-none">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Post Management</h2>
                    <div>
                        <button class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Export
                        </button>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search posts...">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    <div class="dropdown me-2">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="postFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-filter me-1"></i>Filter
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="postFilterDropdown">
                                            <li><a class="dropdown-item" href="#">All Posts</a></li>
                                            <li><a class="dropdown-item" href="#">Published</a></li>
                                            <li><a class="dropdown-item" href="#">Reported</a></li>
                                            <li><a class="dropdown-item" href="#">Deleted</a></li>
                                        </ul>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="postSortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-sort me-1"></i>Sort
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="postSortDropdown">
                                            <li><a class="dropdown-item" href="#">Newest First</a></li>
                                            <li><a class="dropdown-item" href="#">Oldest First</a></li>
                                            <li><a class="dropdown-item" href="#">Most Liked</a></li>
                                            <li><a class="dropdown-item" href="#">Most Commented</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Post</th>
                                        <th>Author</th>
                                        <th>Likes</th>
                                        <th>Comments</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Just finished an amazing hiking trip!</td>
                                        <td>
                                            <img src="https://images.unsplash.com/photo-1494790108755-2616b612b820?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                            Sarah Johnson
                                        </td>
                                        <td>24</td>
                                        <td>8</td>
                                        <td><span class="badge bg-success">Published</span></td>
                                        <td>2 hours ago</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="View" onclick="viewPost('1')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger action-btn" title="Delete" onclick="deletePost('1')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Excited to announce that I just got accepted into...</td>
                                        <td>
                                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                            Mike Chen
                                        </td>
                                        <td>15</td>
                                        <td>12</td>
                                        <td><span class="badge bg-success">Published</span></td>
                                        <td>4 hours ago</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="View" onclick="viewPost('2')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger action-btn" title="Delete" onclick="deletePost('2')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Check out this amazing deal I found!</td>
                                        <td>
                                            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                            Emma Wilson
                                        </td>
                                        <td>32</td>
                                        <td>5</td>
                                        <td><span class="badge bg-warning">Reported</span></td>
                                        <td>1 day ago</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="View" onclick="viewPost('3')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger action-btn" title="Delete" onclick="deletePost('3')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>This is an inappropriate post that was removed</td>
                                        <td>
                                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                            Alex Rodriguez
                                        </td>
                                        <td>2</td>
                                        <td>0</td>
                                        <td><span class="badge bg-danger">Deleted</span></td>
                                        <td>2 days ago</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="View" onclick="viewPost('4')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success action-btn" title="Restore" onclick="restorePost('4')">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Reports Section -->
            <div id="reports-section" class="d-none">
                <h2 class="mb-4">Reports Management</h2>
                
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary active">All (15)</button>
                                <button type="button" class="btn btn-outline-primary">Pending (5)</button>
                                <button type="button" class="btn btn-outline-primary">Resolved (10)</button>
                            </div>
                            <button class="btn btn-primary">
                                <i class="fas fa-download me-2"></i>Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Report ID</th>
                                        <th>Reported Content</th>
                                        <th>Reported By</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Reported At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>#REP-1001</td>
                                        <td>Post: "Check out this deal..."</td>
                                        <td>
                                            <img src="https://images.unsplash.com/photo-1494790108755-2616b612b820?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                            Sarah Johnson
                                        </td>
                                        <td>Spam</td>
                                        <td><span class="badge bg-warning">Pending</span></td>
                                        <td>30 minutes ago</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="Review" onclick="reviewReport('1001')">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success me-1 action-btn" title="Approve" onclick="approveReport('1001')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger action-btn" title="Reject" onclick="rejectReport('1001')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>#REP-1002</td>
                                        <td>User: Mike Chen</td>
                                        <td>
                                            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                            Emma Wilson
                                        </td>
                                        <td>Inappropriate behavior</td>
                                        <td><span class="badge bg-warning">Pending</span></td>
                                        <td>1 hour ago</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="Review" onclick="reviewReport('1002')">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success me-1 action-btn" title="Approve" onclick="approveReport('1002')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger action-btn" title="Reject" onclick="rejectReport('1002')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>#REP-1003</td>
                                        <td>Post: "This is an inappropriate post..."</td>
                                        <td>
                                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                            Alex Rodriguez
                                        </td>
                                        <td>Offensive content</td>
                                        <td><span class="badge bg-success">Resolved</span></td>
                                        <td>Yesterday</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="Review" onclick="reviewReport('1003')">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary action-btn" title="Reopen" onclick="reopenReport('1003')">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>#REP-1004</td>
                                        <td>Comment on Post #123</td>
                                        <td>
                                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                            John Doe
                                        </td>
                                        <td>Hate speech</td>
                                        <td><span class="badge bg-warning">Pending</span></td>
                                        <td>2 hours ago</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="Review" onclick="reviewReport('1004')">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success me-1 action-btn" title="Approve" onclick="approveReport('1004')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger action-btn" title="Reject" onclick="rejectReport('1004')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>#REP-1005</td>
                                        <td>User: Anonymous</td>
                                        <td>
                                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=40&h=40&fit=crop&crop=face" class="user-avatar me-2">
                                            Mike Chen
                                        </td>
                                        <td>Harassment</td>
                                        <td><span class="badge bg-success">Resolved</span></td>
                                        <td>2 days ago</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1 action-btn" title="Review" onclick="reviewReport('1005')">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary action-btn" title="Reopen" onclick="reopenReport('1005')">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Section -->
            <div id="analytics-section" class="d-none">
                <h2 class="mb-4">Analytics</h2>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <span>Engagement Metrics</span>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="engagementChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <span>Content Types</span>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="contentTypeChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Section -->
            <div id="settings-section" class="d-none">
                <h2 class="mb-4">Settings</h2>
                <div class="card">
                    <div class="card-header">
                        <span>Platform Settings</span>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <label for="siteName" class="form-label">Site Name</label>
                                <input type="text" class="form-control" id="siteName" value="SocialNet">
                            </div>
                            <div class="mb-3">
                                <label for="maxPostLength" class="form-label">Maximum Post Length</label>
                                <input type="number" class="form-control" id="maxPostLength" value="280">
                            </div>
                            <div class="mb-3">
                                <label for="privacyPolicy" class="form-label">Privacy Policy URL</label>
                                <input type="url" class="form-control" id="privacyPolicy" value="https://socialnet.com/privacy">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Add User Modal -->
            <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addUserForm">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-select" id="role" required>
                                        <option value="user">User</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Create User</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Sidebar Toggle
        document.getElementById('sidebarCollapse').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.content').classList.toggle('active');
        });

        // Section Navigation
        document.querySelectorAll('.sidebar ul li a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const section = this.getAttribute('data-section');

                // Update active sidebar link
                document.querySelectorAll('.sidebar li').forEach(item => item.classList.remove('active'));
                this.parentElement.classList.add('active');

                // Show/hide sections
                document.querySelectorAll('.content > div').forEach(sectionDiv => {
                    sectionDiv.classList.add('d-none');
                });
                document.getElementById(`${section}-section`).classList.remove('d-none');
            });
        });

        // User Actions
        function editUser(userId) {
            alert(`Editing user ID: ${userId}`);
            // Future: Open a modal with user details
        }

        function banUser(userId) {
            if (confirm(`Ban user ID ${userId}?`)) {
                alert(`User ID ${userId} banned`);
                // Future: Send AJAX request to update users.status = 'banned'
            }
        }

        function unbanUser(userId) {
            if (confirm(`Unban user ID ${userId}?`)) {
                alert(`User ID ${userId} unbanned`);
                // Future: Send AJAX request to update users.status = 'active'
            }
        }

        function deleteUser(userId) {
            if (confirm(`Delete user ID ${userId}?`)) {
                alert(`User ID ${userId} deleted`);
                // Future: Send AJAX request to update users.status = 'deleted'
            }
        }

        function restoreUser(userId) {
            if (confirm(`Restore user ID ${userId}?`)) {
                alert(`User ID ${userId} restored`);
                // Future: Send AJAX request to update users.status = 'active'
            }
        }

        // Post Actions
        function viewPost(postId) {
            alert(`Viewing post ID: ${postId}`);
            // Future: Redirect to post view or open modal
        }

        function deletePost(postId) {
            if (confirm(`Delete post ID ${postId}?`)) {
                alert(`Post ID ${postId} deleted`);
                // Future: Send AJAX request to delete post
            }
        }

        function restorePost(postId) {
            if (confirm(`Restore post ID ${postId}?`)) {
                alert(`Post ID ${postId} restored`);
                // Future: Send AJAX request to restore post
            }
        }

        // Report Actions
        function reviewReport(reportId) {
            alert(`Reviewing report ID: ${reportId}`);
            // Future: Open a modal or redirect to a detailed report review page
        }

        function approveReport(reportId) {
            if (confirm(`Approve report ID ${reportId} and take action?`)) {
                alert(`Report ID ${reportId} approved`);
                // Future: Send AJAX request to update report status and remove reported content
            }
        }

        function rejectReport(reportId) {
            if (confirm(`Reject report ID ${reportId}?`)) {
                alert(`Report ID ${reportId} rejected`);
                // Future: Send AJAX request to update report status
            }
        }

        function reopenReport(reportId) {
            if (confirm(`Reopen report ID ${reportId}?`)) {
                alert(`Report ID ${reportId} reopened`);
                // Future: Send AJAX request to update report status
            }
        }

        // Add User Form Submission
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const role = document.getElementById('role').value;

            alert(`Creating user: ${username} (${email}) with role ${role}`);
            // Future: Send AJAX request to create user
            // Example: fetch('add_user.php', { method: 'POST', body: JSON.stringify({ username, email, password, role }) })

            // Clear form and close modal
            this.reset();
            const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
            modal.hide();
        });

        // User Activity Chart
        const activityChart = new Chart(document.getElementById('activityChart'), {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Active Users',
                    data: [120, 190, 300, 250, 400, 320, 350],
                    borderColor: '#1877f2',
                    backgroundColor: 'rgba(24, 119, 242, 0.2)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Posts Created',
                    data: [80, 100, 150, 120, 180, 140, 160],
                    borderColor: '#42b883',
                    backgroundColor: 'rgba(66, 184, 131, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // User Distribution Chart
        const userDistributionChart = new Chart(document.getElementById('userDistributionChart'), {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Banned', 'Deleted', 'Inactive'],
                datasets: [{
                    data: [70, 10, 5, 15],
                    backgroundColor: ['#1877f2', '#dc3545', '#6c757d', '#ffc107']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Engagement Metrics Chart
        const engagementChart = new Chart(document.getElementById('engagementChart'), {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Likes',
                    data: [200, 300, 450, 400, 500, 420, 380],
                    backgroundColor: '#1877f2'
                }, {
                    label: 'Comments',
                    data: [100, 150, 200, 180, 220, 190, 170],
                    backgroundColor: '#42b883'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Content Type Chart
        const contentTypeChart = new Chart(document.getElementById('contentTypeChart'), {
            type: 'pie',
            data: {
                labels: ['Text', 'Images', 'Videos', 'Links'],
                datasets: [{
                    data: [50, 30, 10, 10],
                    backgroundColor: ['#1877f2', '#42b883', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Logout Handler
        document.getElementById('logout').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to log out?')) {
                alert('Logging out...');
                // Future: Clear session and redirect to login page
                window.location.href = 'index.html';
            }
        });
    </script>
</body>
</html>