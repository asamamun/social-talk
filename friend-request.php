<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    header("Location: admin/");
    exit;
}

$db = new MysqliDb();
$db->where("user_id", $_SESSION['user_id']);
$user = $db->getOne("users");

// Get current user profile data
$db->where("user_id", $_SESSION['user_id']);
$current_user_profile = $db->getOne("user_profile");

// Merge user data with profile data
$current_user = array_merge($user, $current_user_profile ?: []);

// Set default profile picture if not exists
if (empty($current_user['profile_picture'])) {
    $current_user['profile_picture'] = 'assets/default-avatar.png';
}

include_once 'includes/header1.php';


?>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="sidebar">
                    <h4 class="mb-4">Friend Requests</h4>
                    <div id="friendRequestsContainer">
                        <div class="friend-request-card">
                            <div class="d-flex align-items-center">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=60&h=60&fit=crop&crop=face" class="profile-pic me-3" style="width: 60px; height: 60px;" alt="Profile picture of Alex Rodriguez">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Alex Rodriguez</h6>
                                    <p class="text-muted mb-0">2 mutual friends</p>
                                    <small class="text-muted">Sent 2 hours ago</small>
                                </div>
                                <div>
                                    <button class="btn btn-primary btn-sm me-2" onclick="acceptFriendRequest(this)" aria-label="Accept friend request from Alex Rodriguez">Accept</button>
                                    <button class="btn btn-secondary btn-sm" onclick="declineFriendRequest(this)" aria-label="Decline friend request from Alex Rodriguez">Decline</button>
                                </div>
                            </div>
                        </div>
                        <div class="friend-request-card">
                            <div class="d-flex align-items-center">
                                <img src="https://images.unsplash.com/photo-1544725176-7c40e5a71c5e?w=60&h=60&fit=crop&crop=face" class="profile-pic me-3" style="width: 60px; height: 60px;" alt="Profile picture of Lisa Park">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Lisa Park</h6>
                                    <p class="text-muted mb-0">5 mutual friends</p>
                                    <small class="text-muted">Sent yesterday</small>
                                </div>
                                <div>
                                    <button class="btn btn-primary btn-sm me-2" onclick="acceptFriendRequest(this)" aria-label="Accept friend request from Lisa Park">Accept</button>
                                    <button class="btn btn-secondary btn-sm" onclick="declineFriendRequest(this)" aria-label="Decline friend request from Lisa Park">Decline</button>
                                </div>
                            </div>
                        </div>
                        <div class="friend-request-card">
                            <div class="d-flex align-items-center">
                                <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=60&h=60&fit=crop&crop=face" class="profile-pic me-3" style="width: 60px; height: 60px;" alt="Profile picture of Emma Wilson">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Emma Wilson</h6>
                                    <p class="text-muted mb-0">3 mutual friends</p>
                                    <small class="text-muted">Sent 3 days ago</small>
                                </div>
                                <div>
                                    <button class="btn btn-primary btn-sm me-2" onclick="acceptFriendRequest(this)" aria-label="Accept friend request from Emma Wilson">Accept</button>
                                    <button class="btn btn-secondary btn-sm" onclick="declineFriendRequest(this)" aria-label="Decline friend request from Emma Wilson">Decline</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Message when no requests are available -->
                    <div id="noRequestsMessage" class="text-center text-muted hidden">
                        <p>No pending friend requests.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php
include_once 'includes/footer1.php';
?>