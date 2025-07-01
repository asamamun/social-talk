<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';

if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$db = new MysqliDb();
$current_user_id = (int)$_SESSION['user_id'];

// Handle AJAX search request
if (isset($_GET['search']) && isset($_GET['q'])) {
    $query = trim($_GET['q']);
    
    // Search users (username, first name, last name)
    $db->where("(username LIKE ? OR user_id IN (
        SELECT user_id FROM user_profile 
        WHERE first_name LIKE ? OR last_name LIKE ?
    ))", ["%$query%", "%$query%", "%$query%"]);
    $db->where("user_id != ?", [$current_user_id]);
    $users = $db->get('users', 10, ['user_id', 'username']);
    
    // Search posts (content)
    $db->where("content LIKE ?", ["%$query%"]);
    $db->where("user_id = ? OR visibility = 'public'", [$current_user_id]);
    $posts = $db->get('posts', 5, ['post_id', 'user_id', 'content']);
    
    ob_start(); // Start output buffering
    
    if (empty($users) && empty($posts)) {
        echo '<div class="alert alert-info">No results found</div>';
    } else {
        if (!empty($users)) {
            echo '<h6 class="mb-3">People</h6>';
            echo '<div class="list-group mb-4">';
            foreach ($users as $user) {
                // Get profile picture
                $db->where('user_id', $user['user_id']);
                $profile = $db->getOne('user_profile', ['profile_picture', 'first_name', 'last_name']);
                
                $profile_pic = $profile['profile_picture'] ?? 'assets/default-avatar.png';
                $name = htmlspecialchars(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
                
                echo '<a href="user-profile.php?user_id='.$user['user_id'].'" class="list-group-item list-group-item-action">';
                echo '<div class="d-flex align-items-center">';
                echo '<img src="'.$profile_pic.'" class="rounded-circle me-3" width="40" height="40" alt="'.$name.'">';
                echo '<div>';
                echo '<h6 class="mb-0">'.htmlspecialchars($user['username']).'</h6>';
                echo '<small class="text-muted">'.$name.'</small>';
                echo '</div>';
                echo '</div>';
                echo '</a>';
            }
            echo '</div>';
        }
        
        if (!empty($posts)) {
            echo '<h6 class="mb-3">Posts</h6>';
            echo '<div class="list-group">';
            foreach ($posts as $post) {
                // Get user info for the post
                $db->where('user_id', $post['user_id']);
                $user = $db->getOne('users', ['username']);
                
                $db->where('user_id', $post['user_id']);
                $profile = $db->getOne('user_profile', ['profile_picture']);
                $profile_pic = $profile['profile_picture'] ?? 'assets/default-avatar.png';
                
                // Truncate content for preview
                $content = strlen($post['content']) > 100 
                    ? substr($post['content'], 0, 100) . '...' 
                    : $post['content'];
                
                echo '<a href="post.php?post_id='.$post['post_id'].'" class="list-group-item list-group-item-action">';
                echo '<div class="d-flex align-items-center mb-2">';
                echo '<img src="'.$profile_pic.'" class="rounded-circle me-2" width="30" height="30" alt="'.htmlspecialchars($user['username']).'">';
                echo '<small class="text-muted">'.htmlspecialchars($user['username']).'</small>';
                echo '</div>';
                echo '<p class="mb-0">'.htmlspecialchars($content).'</p>';
                echo '</a>';
            }
            echo '</div>';
        }
    }
    
    $output = ob_get_clean(); // Get the buffered output
    die($output); // Return the search results
}

// Rest of your existing code for fetching user data...
$db->where("user_id", $current_user_id);
$current_user = $db->getOne("users");
if (!$current_user) {
    error_log("Header1 - User not found: user_id=$current_user_id");
    header("Location: error.php?message=User+not+found");
    exit;
}

// Fetch user profile data
$db->where("user_id", $current_user_id);
$current_user_profile = $db->getOne("user_profile");
$current_user = array_merge($current_user, $current_user_profile ?: []);
$current_user['profile_picture'] = $current_user['profile_picture'] ?? 'assets/default-avatar.png';

// Fetch unread message count
$unread_messages = $db->rawQueryValue("
    SELECT COUNT(*) 
    FROM messages 
    WHERE receiver_id = ? AND is_read = 0
", [$current_user_id])[0];

// Fetch unread notification count
$unread_notifications = $db->rawQueryValue("
    SELECT COUNT(*) 
    FROM notifications 
    WHERE user_id = ? AND is_read = 0
", [$current_user_id])[0];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social-Talk - Connect with Friends</title>
    <link rel="icon" type="image/x-icon" href="assets/logo/logo1.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style1.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?= settings()['root'] ?>assets/css/lightbox.min.css">
    <style>
        /* Search modal styles */
        #searchResultsModal .modal-dialog {
            max-width: 500px;
        }

        #searchResultsModal .modal-body {
            max-height: 60vh;
            overflow-y: auto;
        }

        #searchResultsModal .list-group-item {
            border-left: 0;
            border-right: 0;
        }

        #searchResultsModal .list-group-item:first-child {
            border-top: 0;
        }

        #searchResultsModal .list-group-item:last-child {
            border-bottom: 0;
        }
        
        /* Search input group */
        .search-input-group {
            width: 250px;
        }
        
        @media (max-width: 992px) {
            .search-input-group {
                width: 200px;
            }
        }
        
        @media (max-width: 768px) {
            .search-input-group {
                width: 150px;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top" aria-label="Main navigation">
        <div class="container">
            <a class="navbar-brand pulse" href="index.php">
                <img src="assets/logo/logo.png" alt="Social-Talk Logo" style="height: 50px; width: 120px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                    <!-- Search Bar -->
                    <div class="nav-item me-3">
                        <div class="input-group search-input-group">
                            <input type="search" id="searchFriends" class="form-control" placeholder="Search..." aria-label="Search">
                            <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Chat Trigger -->
                    <div class="nav-item me-3">
                        <a class="nav-link position-relative" href="messages.php">
                            <i class="fas fa-comments fa-lg"></i>
                            <?php if ($unread_messages > 0): ?>
                                <span class="badge bg-primary rounded-pill position-absolute" style="top: -5px; right: -10px;">
                                    <?= $unread_messages ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </div>

                    <!-- Notifications Dropdown -->
                    <div class="nav-item dropdown me-3">
                        <a class="nav-link position-relative" href="notifications.php">
                            <i class="fas fa-bell fa-lg"></i>
                            <?php if ($unread_notifications > 0): ?>
                                <span class="badge bg-primary rounded-pill position-absolute" style="top: -5px; right: -10px;">
                                    <?= $unread_notifications ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="nav-item dropdown">
                        <a class="nav-link" href="user-profile.php" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User profile">
                            <img src="<?= htmlspecialchars($current_user['profile_picture']) ?>" class="profile-pic" alt="<?= htmlspecialchars($current_user['username']) ?>"  style="text-decoration: none;">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="user-profile.php?user_id=<?= $current_user_id ?>">
                                <i class="fas fa-user me-2"></i><?= htmlspecialchars($current_user['username']) ?>'s Profile
                            </a>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <a class="dropdown-item" href="admin/index.php">
                                    <i class="fas fa-user-shield me-2"></i>Admin Dashboard
                                </a>
                            <?php endif; ?>
                            <a class="dropdown-item" href="setting.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Search Results Modal -->
    <div class="modal fade" id="searchResultsModal" tabindex="-1" aria-labelledby="searchResultsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchResultsModalLabel">Search Results</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="searchResultsBody">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (isset($_SESSION['message'])) {
        //show message in sweetalert and unset msg
        echo '<script>Swal.fire({
            position: "top-end",
            icon: "success",
            title: "' . $_SESSION['message'] . '",
            showConfirmButton: false,
            timer: 1500
        });</script>';
        unset($_SESSION['message']);
    }
    ?>

    <!-- Your page content here -->

    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchFriends');
        const searchButton = document.getElementById('searchButton');
        const searchResultsModal = new bootstrap.Modal(document.getElementById('searchResultsModal'));
        let searchTimeout;

        function fetchSearchResults(query) {
            // Show loading spinner
            document.getElementById('searchResultsBody').innerHTML = `
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>`;
            
            // Show modal when searching
            searchResultsModal.show();
            
            fetch('index.php?search=1&q=' + encodeURIComponent(query))
                .then(response => response.text())
                .then(data => {
                    document.getElementById('searchResultsBody').innerHTML = data;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('searchResultsBody').innerHTML = `
                        <div class="alert alert-danger">Error loading search results</div>`;
                });
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) { // Only search if at least 2 characters
                searchTimeout = setTimeout(() => {
                    fetchSearchResults(query);
                }, 300); // 300ms debounce
            } else if (query.length === 0) {
                // Clear results if search is empty
                document.getElementById('searchResultsBody').innerHTML = '';
            }
        });

        searchButton.addEventListener('click', function() {
            const query = searchInput.value.trim();
            if (query.length >= 2) {
                fetchSearchResults(query);
            }
        });

        // Also search when pressing Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = searchInput.value.trim();
                if (query.length >= 2) {
                    fetchSearchResults(query);
                }
            }
        });
    });
    </script>
