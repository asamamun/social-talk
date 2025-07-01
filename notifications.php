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

// Get all notifications for current user, ordered by newest first
$db->where("user_id", $_SESSION['user_id']);
$db->orderBy("created_at", "DESC");
$notifications = $db->get("notifications");

// Prepare the notifications data with user info
$notificationsWithUserData = [];
foreach ($notifications as $notification) {
    // Get the source user (who triggered the notification)
    $db->where("user_id", $notification['source_id']);
    $sourceUser = $db->getOne("users");
    
    // Get profile info for the source user
    $db->where("user_id", $notification['source_id']);
    $sourceProfile = $db->getOne("user_profile");
    
    $notification['source_user'] = array_merge($sourceUser, $sourceProfile ?: []);
    
    // Set default profile picture if not exists
    if (empty($notification['source_user']['profile_picture'])) {
        $notification['source_user']['profile_picture'] = 'assets/default-avatar.png';
    }
    
    $notificationsWithUserData[] = $notification;
}

include_once 'includes/header1.php';
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
                    <?php if (empty($notificationsWithUserData)): ?>
                        <div id="noNotificationsMessage" class="text-center text-muted" tabindex="0">
                            <p>No new notifications.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notificationsWithUserData as $notification): ?>
                            <?php
                            $notificationClass = $notification['is_read'] ? 'notification-item' : 'notification-item unread';
                            $actionUser = $notification['source_user'];
                            $timeAgo = time_elapsed_string($notification['created_at']);
                            ?>
                            
                            <div class="<?php echo $notificationClass; ?>" 
                                 onclick="handleNotificationClick('<?php echo $notification['type']; ?>', '<?php echo $notification['source_id']; ?>', this)">
                                <div class="d-flex align-items-center">
                                   <a href="user-profile.php?user_id=<?= htmlspecialchars($actionUser['user_id']); ?>" style="text-decoration: none;"> <img src="<?php echo htmlspecialchars($actionUser['profile_picture']); ?>" 
                                         class="profile-pic me-3" 
                                         alt="<?php echo htmlspecialchars($actionUser['username']); ?>"> </a>
                                    <div>
                                        <?php if ($notification['type'] == 'friend_request'): ?>
                                            <strong><a href="user-profile.php?user_id=<?= htmlspecialchars($actionUser['user_id']); ?>" style="text-decoration: none;"><?php echo htmlspecialchars($actionUser['username']); ?> </a></strong> sent you a friend request
                                            <div class="text-muted small"><?php echo $timeAgo; ?></div>
                                            <?php 
                                            // Check if this is the current user who needs to take action
                                            $db->where('user1_id', $notification['source_id']);
                                            $db->where('user2_id', $_SESSION['user_id']);
                                            $db->where('status', 'pending');
                                            $friendRequest = $db->getOne('friendships');
                                            
                                            if ($friendRequest): ?>
                                                <div class="mt-2">
                                                    <button class="btn btn-primary btn-sm me-2" 
                                                            onclick="acceptFriendRequest(<?php echo $notification['source_id']; ?>, this, event)" 
                                                            aria-label="Accept friend request from <?php echo htmlspecialchars($actionUser['username']); ?>">
                                                        Accept
                                                    </button>
                                                    <button class="btn btn-secondary btn-sm" 
                                                            onclick="declineFriendRequest(<?php echo $notification['source_id']; ?>, this, event)" 
                                                            aria-label="Decline friend request from <?php echo htmlspecialchars($actionUser['username']); ?>">
                                                        Decline
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif ($notification['type'] == 'like'): ?>
                                            <strong><a href="user-profile.php?user_id=<?= htmlspecialchars($actionUser['user_id']); ?>" style="text-decoration: none;"><?php echo htmlspecialchars($actionUser['username']); ?>
                                            </a></strong> liked your post
                                            <div class="text-muted small"><?php echo $timeAgo; ?></div>
                                        <?php elseif ($notification['type'] == 'comment'): ?>
                                            <strong><a href="user-profile.php?user_id=<?= htmlspecialchars($actionUser['user_id']); ?>" style="text-decoration: none;"><?php echo htmlspecialchars($actionUser['username']); ?>
                                            </a></strong>commented on your post
                                            <div class="text-muted small"><?php echo $timeAgo; ?></div>
                                        <?php elseif ($notification['type'] == 'message'): ?>
                                           <strong><a href="user-profile.php?user_id=<?= htmlspecialchars($actionUser['user_id']); ?>" style="text-decoration: none;"><?php echo htmlspecialchars($actionUser['username']); ?>
                                            </a></strong> sent you a message
                                            <div class="text-muted small"><?php echo $timeAgo; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markAllAsRead() {
    fetch('mark_notifications_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ markAllRead: true })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove unread class from all notifications
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
        }
    })
    .catch(error => console.error('Error:', error));
}

function clearAllNotifications() {
    if (confirm('Are you sure you want to clear all notifications?')) {
        fetch('clear_notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ clearAll: true })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear the notifications container
                document.getElementById('notificationsContainer').innerHTML = `
                    <div id="noNotificationsMessage" class="text-center text-muted" tabindex="0">
                        <p>No new notifications.</p>
                    </div>
                `;
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function handleNotificationClick(type, sourceId, element) {
    // Mark as read when clicked
    if (element.classList.contains('unread')) {
        fetch('mark_notifications_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ notificationId: sourceId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                element.classList.remove('unread');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Redirect based on notification type
    switch(type) {
        case 'friend_request':
            window.location.href = 'profile.php?id=' + sourceId;
            break;
        case 'like':
        case 'comment':
            // You'll need to store post_id in the notifications table or join with likes/comments table
            window.location.href = 'post.php?id=' + sourceId;
            break;
        case 'message':
            window.location.href = 'messages.php?user=' + sourceId;
            break;
        default:
            window.location.href = 'profile.php?id=' + sourceId;
    }
}

function acceptFriendRequest(userId, button, event) {
    event.stopPropagation(); // Prevent the notification click event
    
    fetch('handle_friend_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            action: 'accept',
            friendId: userId 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the buttons
            const notificationItem = button.closest('.notification-item');
            const buttonsContainer = button.closest('.mt-2');
            buttonsContainer.innerHTML = '<div class="text-success">Friend request accepted</div>';
            
            // Optionally, you can remove the notification after a delay
            setTimeout(() => {
                notificationItem.remove();
                if (document.querySelectorAll('.notification-item').length === 0) {
                    document.getElementById('notificationsContainer').innerHTML = `
                        <div id="noNotificationsMessage" class="text-center text-muted" tabindex="0">
                            <p>No new notifications.</p>
                        </div>
                    `;
                }
            }, 2000);
        }
    })
    .catch(error => console.error('Error:', error));
}

function declineFriendRequest(userId, button, event) {
    event.stopPropagation(); // Prevent the notification click event
    
    fetch('handle_friend_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            action: 'decline',
            friendId: userId 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the notification item
            const notificationItem = button.closest('.notification-item');
            notificationItem.remove();
            
            if (document.querySelectorAll('.notification-item').length === 0) {
                document.getElementById('notificationsContainer').innerHTML = `
                    <div id="noNotificationsMessage" class="text-center text-muted" tabindex="0">
                        <p>No new notifications.</p>
                    </div>
                `;
            }
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

<?php
// Helper function to display time elapsed
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Calculate weeks and remaining days
    $weeks = floor($diff->d / 7);
    $days = $diff->d % 7;

    $time_units = [
        'y' => ['value' => $diff->y, 'name' => 'year'],
        'm' => ['value' => $diff->m, 'name' => 'month'],
        'w' => ['value' => $weeks, 'name' => 'week'],
        'd' => ['value' => $days, 'name' => 'day'],
        'h' => ['value' => $diff->h, 'name' => 'hour'],
        'i' => ['value' => $diff->i, 'name' => 'minute'],
        's' => ['value' => $diff->s, 'name' => 'second']
    ];

    $parts = [];
    foreach ($time_units as $unit) {
        if ($unit['value'] > 0) {
            $parts[] = $unit['value'] . ' ' . $unit['name'] . ($unit['value'] > 1 ? 's' : '');
        }
    }

    if (!$full) {
        $parts = array_slice($parts, 0, 1);
    }

    return $parts ? implode(', ', $parts) . ' ago' : 'just now';
}

include_once 'includes/footer1.php';
?>