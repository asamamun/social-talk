<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social-Talk - Connect with Friends</title>
    <link rel="icon" type="image/x-icon" href="../assets/logo/logo1.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
   <link rel="stylesheet" href="../assets/css/style1.css">
   
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top" aria-label="Main navigation">
        <div class="container">
            <a class="navbar-brand pulse" href="../timeline.php">
                <img src="../assets/logo/logo.png" alt="Social-Talk Logo" style="height: 50px; width: 120px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                    <!-- Search Bar -->
                    <div class="nav-item me-3">
                        <label for="searchFriends" class="visually-hidden">Search friends</label>
                        <input type="search" id="searchFriends" class="form-control" placeholder="Search friends..." aria-label="Search friends">
                    </div>
                    <!-- Chat Trigger -->
                    <div class="nav-item me-3">
                        <a class="nav-link" href="../messages.php" data-bs-toggle="modal" data-bs-target="#chatModal">
                            <i class="fas fa-comments fa-lg" aria-label="Open chat"></i>
                        </a>
                    </div>
                    <!-- Notifications Dropdown -->
                    <div class="nav-item dropdown me-3">
                        <a class="nav-link position-relative" href="../notifications.php" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications, 3 unread">
                            <i class="fas fa-bell fa-lg"></i>
                            <span class="notification-badge badge bg-danger position-absolute pulse" style="top: -8px; right: -8px;">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" style="width: 350px;">
                            <h6 class="dropdown-header">Notifications</h6>
                            <div class="notification-item unread">
                                <div class="d-flex align-items-center">
                                    <img src="https://images.unsplash.com/photo-1494790108755-2616b612b820?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="Sarah Johnson profile picture">
                                    <div>
                                        <strong>Sarah Johnson</strong> liked your post
                                        <div class="text-muted small">2 minutes ago</div>
                                    </div>
                                </div>
                            </div>
                            <div class="notification-item unread">
                                <div class="d-flex align-items-center">
                                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="Mike Chen profile picture">
                                    <div>
                                        <strong>Mike Chen</strong> sent you a friend request
                                        <div class="text-muted small">5 minutes ago</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- User Profile Dropdown -->
                    <div class="nav-item dropdown">
                        <a class="nav-link" href="../user-profile/user-profile.php" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User profile">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=50&h=50&fit=crop&crop=face" class="profile-pic" alt="User profile picture">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="../user-profile/user-profile.php" onclick="showProfile()"><i class="fas fa-user me-2"></i>My Profile</a>
                            <a class="dropdown-item" href="../setting.php" onclick="showSettings()"><i class="fas fa-cog me-2"></i>Settings</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" onclick="logout()"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Chat Modal -->
    <div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chatModalTitle">Chat with Friend</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="chat-container" id="chatContainer"></div>
                    <div class="mt-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="messageInput" placeholder="Type your message..." aria-label="Type your message">
                            <button class="btn btn-primary" onclick="socialNet.sendMessage()" aria-label="Send message">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
