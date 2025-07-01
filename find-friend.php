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

// Handle search functionality
$search_query = '';
$suggested_friends = [];
$search_results = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
    
    if (!empty($search_query)) {
        // Search for users by username
        $db->where('username', '%' . $search_query . '%', 'LIKE');
        $db->where('user_id', $current_user_id, '!=');
        $search_results = $db->get('users', 10);
        
        // Get profile pictures for search results
        foreach ($search_results as &$result) {
            $db->where('user_id', $result['user_id']);
            $profile = $db->getOne('user_profile');
            $result['profile_picture'] = $profile['profile_picture'] ?? 'assets/default-avatar.png';
        }
    }
}

// Get suggested friends (people who are not already friends)
$suggested_friends = $db->rawQuery("
    SELECT 
        u.user_id,
        u.username,
        up.profile_picture,
        s.is_online,
        (
            SELECT COUNT(*) 
            FROM friendships f1
            JOIN friendships f2 ON 
                (f1.user1_id = f2.user1_id OR f1.user1_id = f2.user2_id OR 
                 f1.user2_id = f2.user1_id OR f1.user2_id = f2.user2_id)
            WHERE 
                (f1.user1_id = ? OR f1.user2_id = ?) AND 
                (f2.user1_id = u.user_id OR f2.user2_id = u.user_id) AND 
                f1.status = 'accepted' AND 
                f2.status = 'accepted' AND
                f1.friendship_id != f2.friendship_id
        ) as mutual_friends
    FROM users u
    LEFT JOIN user_profile up ON u.user_id = up.user_id
    LEFT JOIN sessions s ON u.user_id = s.user_id
    WHERE 
        u.user_id != ? AND
        u.user_id NOT IN (
            SELECT IF(user1_id = ?, user2_id, user1_id) 
            FROM friendships 
            WHERE (user1_id = ? OR user2_id = ?) AND status = 'accepted'
        )
    ORDER BY mutual_friends DESC, RAND()
    LIMIT 6
", [
    $current_user_id, // For mutual friends subquery (1)
    $current_user_id, // For mutual friends subquery (2)
    $current_user_id, // For u.user_id != ?
    $current_user_id, // For NOT IN subquery (1)
    $current_user_id, // For NOT IN subquery (2)
    $current_user_id  // For NOT IN subquery (3)
]);

// Handle cancel friend request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_request']) && isset($_POST['recipient_id'])) {
    $recipient_id = (int)$_POST['recipient_id'];
    
    // Find and delete the pending request
    $db->where('(user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)', 
              [$current_user_id, $recipient_id, $recipient_id, $current_user_id]);
    $db->where('status', 'pending');
    $db->where('action_user_id', $current_user_id);
    
    if ($db->delete('friendships')) {
        $_SESSION['success_message'] = "Friend request cancelled successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to cancel friend request.";
    }
    
    header("Location: find-friend.php" . (isset($_GET['search']) ? "?search=" . urlencode($_GET['search']) : ""));
    exit;
}

include_once 'includes/header1.php';
?>

<!-- Find Friends Section -->
<div class="container mt-4">
    <!-- Display success/error messages -->
    <?php if (isset($_SESSION['success_message'])) : ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <!-- Search Bar -->
    <div class="search-card">
        <h4 class="mb-4">Find New Friends</h4>
        <form method="GET" action="find-friend.php">
            <div class="input-group mb-4">
                <input type="text" 
                       class="form-control" 
                       name="search"
                       value="<?= htmlspecialchars($search_query) ?>" 
                       placeholder="Search for people..." 
                       aria-label="Search for people">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search me-2"></i>Search
                </button>
            </div>
        </form>
    </div>

    <?php if (!empty($search_query)) : ?>
        <!-- Search Results -->
        <div class="search-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Search Results for "<?= htmlspecialchars($search_query) ?>"</h4>
                <a href="find-friend.php" class="btn btn-light">
                    <i class="fas fa-times me-2"></i>Clear Search
                </a>
            </div>
            
            <?php if (empty($search_results)) : ?>
                <div class="alert alert-info">
                    No users found matching your search.
                </div>
            <?php else : ?>
                <div class="friend-grid">
                    <?php foreach ($search_results as $user) : ?>
                        <div class="friend-card position-relative">
                            <a href="user-profile.php?user_id=<?= htmlspecialchars($user['user_id']); ?>" style="text-decoration: none;"><img src="<?= htmlspecialchars($user['profile_picture']) ?>" 
                                 class="rounded-circle"
                                 alt="<?= htmlspecialchars($user['username']) ?>"
                                 width="100" height="100">
                            </a>
                            
                            <?php if ($user['is_online'] ?? false) : ?>
                                <div class="online-status"></div>
                            <?php endif; ?>
                            
                            <h6 >
                                <a href="user-profile.php?user_id=<?= htmlspecialchars($user['user_id']); ?>" style="text-decoration: none;"><?= htmlspecialchars($user['username']) ?>
                                </h6>
                            
                            <?php 
                            // Check friendship status
                            $db->where('(user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)', 
                                      [$current_user_id, $user['user_id'], $user['user_id'], $current_user_id]);
                            $friendship = $db->getOne('friendships');
                            ?>
                            
                            <?php if ($friendship) : ?>
                                <?php if ($friendship['status'] == 'accepted') : ?>
                                    <button class="btn btn-success btn-sm" disabled>
                                        <i class="fas fa-user-friends me-2"></i>Friends
                                    </button>
                                <?php elseif ($friendship['status'] == 'pending') : ?>
                                    <?php if ($friendship['action_user_id'] == $current_user_id) : ?>
                                        <form method="POST" action="find-friend.php" class="d-flex gap-2">
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                <i class="fas fa-user-clock me-2"></i>Request Sent
                                            </button>
                                            <input type="hidden" name="recipient_id" value="<?= $user['user_id'] ?>">
                                            <button type="submit" name="cancel_request" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times me-2"></i>Cancel
                                            </button>
                                        </form>
                                    <?php else : ?>
                                        <a href="friend-request.php" class="btn btn-info btn-sm">
                                            <i class="fas fa-user-clock me-2"></i>Respond to Request
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php else : ?>
                                <form method="POST" action="send-friend-request.php">
                                    <input type="hidden" name="recipient_id" value="<?= $user['user_id'] ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-user-plus me-2"></i>Add Friend
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <!-- Suggested Friends -->
        <div class="search-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Suggested Friends</h4>
                <a href="user-profile.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                </a>
            </div>
            
            <?php if (empty($suggested_friends)) : ?>
                <div class="alert alert-info">
                    No friend suggestions available at this time.
                </div>
            <?php else : ?>
                <div class="friend-grid">
                    <?php foreach ($suggested_friends as $friend) : ?>
                        <div class="friend-card position-relative">
                           <a href="user-profile.php?user_id=<?= htmlspecialchars($friend['user_id']); ?>" style="text-decoration: none;"> <img src="<?= htmlspecialchars($friend['profile_picture']) ?>" 
                                 class="rounded-circle"
                                 alt="<?= htmlspecialchars($friend['username']) ?>"
                                 width="100" height="100">
                            </a>
                            
                            <?php if ($friend['is_online'] ?? false) : ?>
                                <div class="online-status"></div>
                            <?php endif; ?>
                            
                            <h6>
                                <a href="user-profile.php?user_id=<?= htmlspecialchars($friend['user_id']); ?>" style="text-decoration: none;"><?= htmlspecialchars($friend['username']) ?>
                                </a>
                            
                            </h6>
                            <p class="text-muted small">
                                <?= $friend['mutual_friends'] ?> mutual friend<?= $friend['mutual_friends'] != 1 ? 's' : '' ?>
                            </p>
                            
                            <?php 
                            // Check friendship status
                            $db->where('(user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)', 
                                      [$current_user_id, $friend['user_id'], $friend['user_id'], $current_user_id]);
                            $friendship = $db->getOne('friendships');
                            ?>
                            
                            <?php if ($friendship) : ?>
                                <?php if ($friendship['status'] == 'accepted') : ?>
                                    <button class="btn btn-success btn-sm" disabled>
                                        <i class="fas fa-user-friends me-2"></i>Friends
                                    </button>
                                <?php elseif ($friendship['status'] == 'pending') : ?>
                                    <?php if ($friendship['action_user_id'] == $current_user_id) : ?>
                                        <form method="POST" action="find-friend.php" class="d-flex gap-2">
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                <i class="fas fa-user-clock me-2"></i>Request Sent
                                            </button>
                                            <input type="hidden" name="recipient_id" value="<?= $friend['user_id'] ?>">
                                            <button type="submit" name="cancel_request" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times me-2"></i>Cancel
                                            </button>
                                        </form>
                                    <?php else : ?>
                                        <a href="friend-request.php" class="btn btn-info btn-sm">
                                            <i class="fas fa-user-clock me-2"></i>Respond to Request
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php else : ?>
                                <form method="POST" action="send-friend-request.php">
                                    <input type="hidden" name="recipient_id" value="<?= $friend['user_id'] ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-user-plus me-2"></i>Add Friend
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
include_once 'includes/footer1.php';
?>