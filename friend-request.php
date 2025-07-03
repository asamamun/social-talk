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

// Handle friend request actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['friendship_id'])) {
        $friendship_id = (int)$_POST['friendship_id'];
        $action = $_POST['action'];
        
        try {            
            $db->where('friendship_id', $friendship_id);
            
            // For accept/decline, check if user is recipient
            if ($action === 'accept' || $action === 'decline') {
                $db->where('(user2_id = ?)', [$current_user_id]);
            } 
            // For cancel, check if user is sender
            elseif ($action === 'cancel') {
                $db->where('(user1_id = ?)', [$current_user_id]);
            }
            
            $friendship = $db->getOne('friendships');
            
            if (!$friendship) {
                throw new Exception('Friend request not found');
            }
            
            if ($friendship['status'] !== 'pending') {
                throw new Exception('This request has already been processed');
            }
            
            if ($action === 'accept') {
                $data = [
                    'status' => 'accepted',
                    'action_user_id' => $current_user_id                
                ];
                
                $db->where('friendship_id', $friendship_id);
                if (!$db->update('friendships', $data)) {
                    throw new Exception('Failed to update friend request');
                }
                
                // Create notification for the other user
                $other_user_id = $friendship['user1_id'];
                
                $notification_data = [
                    'user_id' => $other_user_id,
                    'type' => 'friend_request',
                    'source_id' => $current_user_id,
                    'created_at' => $db->now()
                ];
                $db->insert('notifications', $notification_data);
                
                $_SESSION['success'] = "Friend request accepted successfully!";
                
            } elseif ($action === 'decline' || $action === 'cancel') {
                $db->where('friendship_id', $friendship_id);
                if (!$db->delete('friendships')) {
                    throw new Exception('Failed to cancel friend request');
                }
                
                $_SESSION['success'] = $action === 'cancel' 
                    ? "Friend request cancelled successfully!" 
                    : "Friend request declined successfully!";
            }
            
            header("Location: friend-request.php" . (isset($_GET['show']) ? "?show=" . $_GET['show'] : ""));
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: friend-request.php" . (isset($_GET['show']) ? "?show=" . $_GET['show'] : ""));
            exit;
        }
    }
}

// Check if we should show sent requests or received requests
$show_sent_requests = isset($_GET['show']) && $_GET['show'] === 'sent';

// Get friend requests based on what we're showing
if ($show_sent_requests) {
    // Get sent friend requests where current user is the sender
    $db->where('user1_id', $current_user_id);
    $db->where('status', 'pending');
    $db->orderBy('created_at', 'desc');
    $requests = $db->get('friendships');

    $page_title = "Sent Friend Requests";
    $empty_message = "No sent friend requests.";
} else {
    // Get pending friend requests where current user is the recipient
    $db->where('user2_id', $current_user_id);
    $db->where('status', 'pending');
    $db->orderBy('created_at', 'desc');
    $requests = $db->get('friendships');

    $page_title = "Friend Requests";
    $empty_message = "No pending friend requests.";
}

$friend_requests = [];
foreach ($requests as $request) {
    $other_user_id = $show_sent_requests ? $request['user2_id'] : $request['user1_id'];

    $db->where('user_id', $other_user_id);
    $sender = $db->getOne('users');

    $db->where('user_id', $other_user_id);
    $sender_profile = $db->getOne('user_profile');

    if ($sender) {
        // Get mutual friends count
        $mutual = $db->rawQuery("
            SELECT COUNT(*) as count FROM friendships f1
            JOIN friendships f2 ON 
                (f1.user1_id = f2.user1_id OR f1.user1_id = f2.user2_id OR 
                 f1.user2_id = f2.user1_id OR f1.user2_id = f2.user2_id)
            WHERE 
                (f1.user1_id = ? OR f1.user2_id = ?) AND 
                (f2.user1_id = ? OR f2.user2_id = ?) AND 
                f1.status = 'accepted' AND 
                f2.status = 'accepted' AND
                f1.friendship_id != f2.friendship_id
        ", [$current_user_id, $current_user_id, $other_user_id, $other_user_id]);

        $mutual_count = $mutual[0]['count'] ?? 0;

        $friend_requests[] = [
            'friendship_id' => $request['friendship_id'],
            'user_id' => $sender['user_id'],
            'username' => $sender['username'],
            'profile_picture' => $sender_profile['profile_picture'] ?? 'assets/default-avatar.png',
            'mutual_friends' => $mutual_count,
            'created_at' => $request['created_at'],
            'is_sent_request' => $show_sent_requests
        ];
    }
}

include_once 'includes/header1.php';
?>

<!-- Main Content -->
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']);
                                                unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']);
                                                    unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><?= $page_title ?></h4>
                    <div>
                        <?php if ($show_sent_requests): ?>
                            <a href="friend-request.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-user-plus me-1"></i> View Received Requests
                            </a>
                        <?php else: ?>
                            <a href="friend-request.php?show=sent" class="btn btn-primary btn-sm">
                                <i class="fas fa-paper-plane me-1"></i> View Sent Requests
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div id="friendRequestsContainer">
                        <?php if (empty($friend_requests)): ?>
                            <div class="text-center text-muted py-4">
                                <p><?= $empty_message ?></p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($friend_requests as $request): ?>
                                <div class="friend-request-card mb-3 p-3 border rounded">
                                    <div class="d-flex align-items-center">
                                        <a href="user-profile.php?user_id=<?= htmlspecialchars($request['user_id']); ?>" style="text-decoration: none;">
                                            <img src="<?= htmlspecialchars($request['profile_picture']) ?>"
                                                class="rounded-circle me-3"
                                                style="width: 60px; height: 60px; object-fit: cover;"
                                                alt="<?= htmlspecialchars($request['username']) ?>">
                                        </a>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1">
                                                <a href="user-profile.php?user_id=<?= htmlspecialchars($request['user_id']); ?>" style="text-decoration: none;">
                                                    <?= htmlspecialchars($request['username']) ?>
                                                </a>
                                            </h5>
                                            <p class="text-muted mb-1">
                                                <?= $request['mutual_friends'] ?> mutual friend<?= $request['mutual_friends'] != 1 ? 's' : '' ?>
                                            </p>
                                            <small class="text-muted">
                                                <?= $request['is_sent_request'] ? 'Sent' : 'Received' ?> <?= time_elapsed_string($request['created_at']) ?>
                                            </small>
                                        </div>
                                        <div class="d-flex">
                                            <?php if ($request['is_sent_request']): ?>
                                                <form method="post" class="me-2">
                                                    <input type="hidden" name="friendship_id" value="<?= $request['friendship_id'] ?>">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-times me-1"></i> Cancel Request
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="post" class="me-2">
                                                    <input type="hidden" name="friendship_id" value="<?= $request['friendship_id'] ?>">
                                                    <input type="hidden" name="action" value="accept">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check me-1"></i> Accept
                                                    </button>
                                                </form>
                                                <form method="post" class="me-2">
                                                    <input type="hidden" name="friendship_id" value="<?= $request['friendship_id'] ?>">
                                                    <input type="hidden" name="action" value="decline">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-times me-1"></i> Decline
                                                    </button>
                                                </form>
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
</div>

<?php
include_once 'includes/footer1.php';

// Helper function to format time
function time_elapsed_string($datetime, $full = false)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $units = [
        'y' => 'year',
        'm' => 'month',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];

    $parts = [];
    foreach ($units as $unit => $text) {
        if ($diff->$unit) {
            $parts[] = $diff->$unit . ' ' . $text . ($diff->$unit > 1 ? 's' : '');
        }
    }

    if (!$full) {
        $parts = array_slice($parts, 0, 1);
    }

    return $parts ? implode(', ', $parts) . ' ago' : 'just now';
}
?>