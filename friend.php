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

// Determine which user profile to show
$viewing_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $_SESSION['user_id'];
$is_own_profile = ($viewing_user_id === $_SESSION['user_id']);

// Get current logged-in user data
$db->where("user_id", $_SESSION['user_id']);
$current_user = $db->getOne("users");

// Get current user profile data
$db->where("user_id", $_SESSION['user_id']);
$current_user_profile = $db->getOne("user_profile");

// Merge current user data with profile data
$current_user = array_merge($current_user, $current_user_profile ?: []);

// Set default profile picture for current user
if (empty($current_user['profile_picture'])) {
    $current_user['profile_picture'] = 'assets/default-avatar.png';
}

// Get the profile user data (the user whose profile we're viewing)
if ($is_own_profile) {
    $profile_user = $current_user;
} else {
    $db->join("user_profile up", "up.user_id = u.user_id", "LEFT");
    $db->where("u.user_id", $viewing_user_id);
    $profile_user = $db->getOne("users u");

    if (!$profile_user) {
        header("Location: error.php?message=User+not+found");
        exit;
    }

    // Set default images for target user
    if (empty($profile_user['profile_picture'])) {
        $profile_user['profile_picture'] = 'assets/default-avatar.png';
    }
}

// Get friend count
$friend_count = $db->rawQueryOne("
    SELECT COUNT(*) as count FROM friendships 
    WHERE (user1_id = ? OR user2_id = ?) 
    AND status = 'accepted'
", [$viewing_user_id, $viewing_user_id])['count'];

// Get friends list with profile data
$friends = $db->rawQuery("
    SELECT 
        u.user_id,
        u.username,
        up.profile_picture,
        s.is_online,
        s.last_active
    FROM friendships f
    JOIN users u ON 
        (f.user1_id = u.user_id OR f.user2_id = u.user_id) 
        AND u.user_id != ?
    LEFT JOIN user_profile up ON u.user_id = up.user_id
    LEFT JOIN sessions s ON u.user_id = s.user_id
    WHERE 
        (f.user1_id = ? OR f.user2_id = ?) 
        AND f.status = 'accepted'
    ORDER BY s.is_online DESC, u.username ASC
    LIMIT 12
", [$viewing_user_id, $viewing_user_id, $viewing_user_id]);

function timeAgo($datetime)
{
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time / 60) . ' minutes ago';
    if ($time < 86400) return floor($time / 3600) . ' hours ago';
    if ($time < 2592000) return floor($time / 86400) . ' days ago';
    if ($time < 31536000) return floor($time / 2592000) . ' months ago';
    return floor($time / 31536000) . ' years ago';
}

include_once 'includes/header1.php';
?>

<!-- Friends Section -->
<div class="container mt-4">
    <div class="friend-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><a href="user-profile.php?user_id=<?= htmlspecialchars($profile_user['user_id']); ?>" style="text-decoration: none;"><?= htmlspecialchars($profile_user['username']) ?></a>'s Friends (<?= $friend_count ?>)</h4>
            <div>
                <?php if ($is_own_profile): ?>
                    <a href="find-friend.php" class="btn btn-primary me-2">
                        <i class="fas fa-user-plus me-2"></i>Find Friends
                    </a>
                <?php endif; ?>
                <a href="user-profile.php?user_id=<?= $viewing_user_id ?>" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                </a>
            </div>
        </div>

        <?php if (empty($friends)): ?>
            <div class="alert alert-info">
                <?= $is_own_profile ? 'You don\'t have any friends yet.' : htmlspecialchars($profile_user['username']) . ' has no friends yet.' ?>
                <?php if ($is_own_profile): ?>
                    <a href="find-friend.php">Find friends</a> to get started!
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($friends as $friend): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card friend-card h-100">
                            <div class="card-body d-flex align-items-center">
                                <a href="user-profile.php?user_id=<?= htmlspecialchars($friend['user_id']); ?>">
                                    <img src="<?= htmlspecialchars($friend['profile_picture'] ?? 'assets/default-avatar.png') ?>"
                                        alt="<?= htmlspecialchars($friend['username']) ?>" class="profile-img me-3">
                                </a>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">
                                        <a href="user-profile.php?user_id=<?= htmlspecialchars($friend['user_id']); ?>" style="text-decoration: none;">
                                            <?= htmlspecialchars($friend['username']) ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <?php if ($friend['is_online']): ?>
                                            <span class="text-success"><i class="fas fa-circle"></i> Online</span>
                                        <?php else: ?>
                                            Last active: <?= timeAgo($friend['last_active']); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="user-profile.php?user_id=<?= htmlspecialchars($friend['user_id']); ?>">Profile</a></li>
                                        <li><a class="dropdown-item" href="messages.php?user_id=<?= htmlspecialchars($friend['user_id']); ?>">Message</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="event.stopPropagation(); event.preventDefault(); socialTalk.unfriend(<?= htmlspecialchars($friend['user_id']); ?>);">Unfriend</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($friend_count > 12): ?>
                <div class="text-center mt-4">
                    <a href="friends-list.php?user_id=<?= $viewing_user_id ?>" class="btn btn-primary">
                        <i class="fas fa-users me-2"></i>View All Friends
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
include_once 'includes/footer1.php';
?>