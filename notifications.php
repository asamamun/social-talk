<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'includes/header1.php';
include_once 'includes/db.php';

?>
    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="sidebar">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Notifications</h4>
                        <div>
                            <button class="btn btn-link text-decoration-none mark-all-read me-2" onclick="markAllAsRead()" aria-label="Mark all notifications as read">Mark All as Read</button>
                            <button class="btn btn-link text-decoration-none clear-all text-danger" onclick="clearAllNotifications()" aria-label="Clear all notifications">Clear All</button>
                        </div>
                    </div>
                    <div id="notificationsContainer" aria-live="polite">
                        <div class="notification-item unread" onclick="handleNotificationClick('post', '123', this)">
                            <div class="d-flex align-items-center">
                                <img src="https://images.unsplash.com/photo-1494790108755-2616b612b820?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="Profile picture of Sarah Johnson">
                                <div>
                                    <strong>Sarah Johnson</strong> liked your post
                                    <div class="text-muted small">2 minutes ago</div>
                                </div>
                            </div>
                        </div>
                        <div class="notification-item unread" onclick="handleNotificationClick('friend-request', 'Mike Chen', this)">
                            <div class="d-flex align-items-center">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="Profile picture of Mike Chen">
                                <div>
                                    <strong>Mike Chen</strong> sent you a friend request
                                    <div class="text-muted small">5 minutes ago</div>
                                    <div class="mt-2">
                                        <button class="btn btn-primary btn-sm me-2" onclick="acceptFriendRequest(this)" aria-label="Accept friend request from Mike Chen">Accept</button>
                                        <button class="btn btn-secondary btn-sm" onclick="declineFriendRequest(this)" aria-label="Decline friend request from Mike Chen">Decline</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="notification-item" onclick="handleNotificationClick('post', '456', this)">
                            <div class="d-flex align-items-center">
                                <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="Profile picture of Emma Wilson">
                                <div>
                                    <strong>Emma Wilson</strong> commented on your post
                                    <div class="text-muted small">1 hour ago</div>
                                </div>
                            </div>
                        </div>
                        <div class="notification-item" onclick="handleNotificationClick('post', '789', this)">
                            <div class="d-flex align-items-center">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="Profile picture of Alex Rodriguez">
                                <div>
                                    <strong>Alex Rodriguez</strong> shared your post
                                    <div class="text-muted small">Yesterday</div>
                                </div>
                            </div>
                        </div>
                        <div class="notification-item" onclick="handleNotificationClick('profile', 'Lisa Park', this)">
                            <div class="d-flex align-items-center">
                                <img src="https://images.unsplash.com/photo-1544725176-7c40e5a71c5e?w=50&h=50&fit=crop&crop=face" class="profile-pic me-3" alt="Profile picture of Lisa Park">
                                <div>
                                    <strong>Lisa Park</strong> updated their profile picture
                                    <div class="text-muted small">2 days ago</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="noNotificationsMessage" class="text-center text-muted hidden" tabindex="0">
                        <p>No new notifications.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php
include_once 'includes/footer1.php';
?>