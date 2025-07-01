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
            <div class="friend-grid">
                <?php foreach ($friends as $friend): ?>
                    <div class="friend-card position-relative">
                       <a href="user-profile.php?user_id=<?= htmlspecialchars($friend['user_id']); ?>" style="text-decoration: none;"> <img src="<?= htmlspecialchars($friend['profile_picture'] ?? 'assets/default-avatar.png') ?>" 
                             alt="<?= htmlspecialchars($friend['username']) ?>"
                             class="img-fluid rounded-circle">
                        </a>
                             
                        <?php if ($friend['is_online']): ?>
                            <div class="online-status"></div>
                        <?php endif; ?>
                        
                        <h6> <a href="user-profile.php?user_id=<?= htmlspecialchars($friend['user_id']); ?>" style="text-decoration: none;"><?= htmlspecialchars($friend['username']) ?>
                        </a>
                    </h6>
                        
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