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
$current_user_id = $_SESSION['user_id'];

// Get current user data
$db->where("user_id", $current_user_id);
$user = $db->getOne("users");

// Get user profile data
$db->where("user_id", $current_user_id);
$user_profile = $db->getOne("user_profile");

// Merge user data
$current_user = array_merge($user, $user_profile ?: []);

// Set default profile picture if not exists
if (empty($current_user['profile_picture'])) {
    $current_user['profile_picture'] = 'assets/default-avatar.png';
}

// Get friend count
$friend_count = $db->rawQueryOne("
    SELECT COUNT(*) as count FROM friendships 
    WHERE (user1_id = ? OR user2_id = ?) 
    AND status = 'accepted'
", [$current_user_id, $current_user_id])['count'];

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
", [$current_user_id, $current_user_id, $current_user_id]);

include_once 'includes/header1.php';
?>

<!-- Friends Section -->
<div class="container mt-4">
    <div class="friend-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><?= htmlspecialchars($user['username']) ?>'s Friends (<?= $friend_count ?>)</h4>
            <div>
                <a href="find-friend.php" class="btn btn-primary me-2">
                    <i class="fas fa-user-plus me-2"></i>Find Friends
                </a>
                <a href="user-profile.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                </a>
            </div>
        </div>
        
        <?php if (empty($friends)): ?>
            <div class="alert alert-info">
                You don't have any friends yet. <a href="find-friend.php">Find friends</a> to get started!
            </div>
        <?php else: ?>
            <div class="friend-grid">
                <?php foreach ($friends as $friend): ?>
                    <div class="friend-card position-relative">
                        <img src="<?= htmlspecialchars($friend['profile_picture'] ?? 'assets/default-avatar.png') ?>" 
                             alt="<?= htmlspecialchars($friend['username']) ?>"
                             class="img-fluid rounded-circle">
                             
                        <?php if ($friend['is_online']): ?>
                            <div class="online-status"></div>
                        <?php endif; ?>
                        
                        <h6><?= htmlspecialchars($friend['username']) ?></h6>
                        
                        <div class="d-flex justify-content-center gap-2">
                            <a href="user-profile.php?user_id=<?= $friend['user_id'] ?>" 
                               class="btn btn-sm btn-outline-primary">
                               <i class="fas fa-user me-1"></i> Profile
                            </a>
                            <a href="messages.php?to=<?= $friend['user_id'] ?>" 
                               class="btn btn-sm btn-success">
                               <i class="fas fa-comment me-1"></i> Message
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($friend_count > 12): ?>
                <div class="text-center mt-4">
                    <a href="friends-list.php" class="btn btn-primary">
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