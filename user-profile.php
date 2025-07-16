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
$current_user_id = (int)$_SESSION['user_id'];
error_log("User Profile - Current user_id: $current_user_id");

// Get current logged-in user data
$db->where("user_id", $current_user_id);
$current_user = $db->getOne("users");
if (!$current_user) {
    error_log("User Profile - Current user not found: user_id=$current_user_id");
    header("Location: error.php?message=User+not+found");
    exit;
}

// Get current user profile data
$db->where("user_id", $current_user_id);
$current_user_profile = $db->getOne("user_profile");

// Merge user data with profile data
$current_user = array_merge($current_user, $current_user_profile ?: []);
$current_user['profile_picture'] = $current_user['profile_picture'] ?? 'assets/default-avatar.png';
$current_user['cover_photo'] = $current_user['cover_photo'] ?? 'assets/default-cover.png';

// Determine which user profile to show
$viewing_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $current_user_id;
$is_own_profile = ($viewing_user_id === $current_user_id);

// Get the profile user data (the user whose profile we're viewing)
if ($is_own_profile) {
    $profile_user = $current_user;
} else {
    $db->join("user_profile up", "up.user_id = u.user_id", "LEFT");
    $db->where("u.user_id", $viewing_user_id);
    $profile_user = $db->getOne("users u");

    if (!$profile_user) {
        error_log("User Profile - Viewing user not found: user_id=$viewing_user_id");
        header("Location: error.php?message=User+not+found");
        exit;
    }

    $profile_user['profile_picture'] = $profile_user['profile_picture'] ?? 'assets/default-avatar.png';
    $profile_user['cover_photo'] = $profile_user['cover_photo'] ?? 'assets/default-cover.png';
}

// Function to get friendship status and details
function getFriendshipStatus($db, $user1_id, $user2_id)
{
    $friendship_query = "
        SELECT friendship_id, status, user1_id, user2_id, action_user_id
        FROM friendships 
        WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
    ";
    $friendships = $db->rawQuery($friendship_query, [$user1_id, $user2_id, $user2_id, $user1_id]);

    $result = [
        'status' => 'none',
        'outgoing_pending' => false,
        'incoming_pending' => false,
        'friendship_id' => null
    ];

    foreach ($friendships as $friendship) {
        if ($friendship['status'] == 'accepted') {
            $result['status'] = 'accepted';
            $result['friendship_id'] = $friendship['friendship_id'];
            break;
        } elseif ($friendship['status'] == 'pending') {
            if ($friendship['action_user_id'] == $user1_id) {
                $result['outgoing_pending'] = true;
                $result['status'] = 'pending';
                $result['friendship_id'] = $friendship['friendship_id'];
            } elseif ($friendship['action_user_id'] == $user2_id) {
                $result['incoming_pending'] = true;
                $result['status'] = 'pending';
                $result['friendship_id'] = $friendship['friendship_id'];
            }
        }
    }

    error_log("Friendship status for user1_id=$user1_id, user2_id=$user2_id: " . print_r($result, true));
    return $result;
}

// Check friendship status (only if viewing someone else's profile)
$friendship_info = ['status' => 'none', 'outgoing_pending' => false, 'incoming_pending' => false, 'friendship_id' => null];
if (!$is_own_profile) {
    $friendship_info = getFriendshipStatus($db, $current_user_id, $viewing_user_id);
}

/* Handle all AJAX and POST actions */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $db->startTransaction();
    try {
        if ($_POST['action'] === 'send_friend_request') {
            $target_user_id = isset($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : 0;

            if ($target_user_id <= 0 || $target_user_id == $current_user_id) {
                throw new Exception('Invalid user ID');
            }

            $existing = getFriendshipStatus($db, $current_user_id, $target_user_id);

            if ($existing['status'] == 'accepted') {
                throw new Exception('You are already friends with this user.');
            } elseif ($existing['outgoing_pending']) {
                throw new Exception('You have already sent a friend request to this user.');
            }

            $data = [
                'user1_id' => min($current_user_id, $target_user_id),
                'user2_id' => max($current_user_id, $target_user_id),
                'status' => 'pending',
                'action_user_id' => $current_user_id,
                'created_at' => date('Y-m-d H:i:s')
            ];

            if (!$db->insert('friendships', $data)) {
                throw new Exception('Failed to send friend request: ' . $db->getLastError());
            }
            error_log("Friend request sent: user1_id={$data['user1_id']}, user2_id={$data['user2_id']}, action_user_id=$current_user_id");

            // Create notification for the recipient
            $notification_data = [
                'user_id' => $target_user_id,
                'type' => 'friend_request',
                'source_id' => $current_user_id,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            if (!$db->insert('notifications', $notification_data)) {
                throw new Exception('Failed to create notification: ' . $db->getLastError());
            }
            error_log("Notification created for user_id=$target_user_id");

            $response = ['status' => 'success', 'message' => 'Friend request sent'];
            if ($existing['incoming_pending']) {
                $response['message'] .= '. Note: You have a pending friend request from this user.';
            }

            $db->commit();
            echo json_encode($response);
            exit;
        }

        if ($_POST['action'] === 'accept_friend_request') {
            $target_user_id = isset($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : 0;

            if ($target_user_id <= 0 || $target_user_id == $current_user_id) {
                throw new Exception('Invalid user ID');
            }

            $db->where("user1_id", min($current_user_id, $target_user_id));
            $db->where("user2_id", max($current_user_id, $target_user_id));
            $db->where("status", 'pending');
            $db->where("action_user_id", $target_user_id);

            if (!$db->update('friendships', ['status' => 'accepted', 'action_user_id' => $current_user_id, 'updated_at' => date('Y-m-d H:i:s')])) {
                throw new Exception('Failed to accept friend request: ' . $db->getLastError());
            }
            error_log("Friend request accepted: user1_id=$current_user_id, user2_id=$target_user_id");

            // Create notification for the sender
            $notification_data = [
                'user_id' => $target_user_id,
                'type' => 'friend_request_accepted',
                'source_id' => $current_user_id,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            if (!$db->insert('notifications', $notification_data)) {
                throw new Exception('Failed to create notification: ' . $db->getLastError());
            }
            error_log("Acceptance notification created for user_id=$target_user_id");

            // Delete any outgoing pending request from current user to target user
            $db->where("user1_id", min($current_user_id, $target_user_id));
            $db->where("user2_id", max($current_user_id, $target_user_id));
            $db->where("status", 'pending');
            $db->where("action_user_id", $current_user_id);
            if ($db->delete('friendships')) {
                error_log("Deleted outgoing pending request: user1_id=$current_user_id, user2_id=$target_user_id");
            }

            $db->commit();
            echo json_encode(['status' => 'success', 'message' => 'Friend request accepted']);
            exit;
        }

        if ($_POST['action'] === 'cancel_friend_request') {
            $target_user_id = isset($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : 0;

            if ($target_user_id <= 0 || $target_user_id == $current_user_id) {
                throw new Exception('Invalid user ID');
            }

            $db->where("user1_id", min($current_user_id, $target_user_id));
            $db->where("user2_id", max($current_user_id, $target_user_id));
            $db->where("status", 'pending');
            $db->where("action_user_id", $current_user_id); // Cancel outgoing request
            if ($db->delete('friendships')) {
                error_log("Cancelled outgoing friend request: user1_id=$current_user_id, user2_id=$target_user_id");
                $db->commit();
                echo json_encode(['status' => 'success', 'message' => 'Friend request cancelled']);
            } else {
                // Check if cancelling an incoming request (decline)
                $db->where("user1_id", min($current_user_id, $target_user_id));
                $db->where("user2_id", max($current_user_id, $target_user_id));
                $db->where("status", 'pending');
                $db->where("action_user_id", $target_user_id);
                if ($db->delete('friendships')) {
                    error_log("Declined incoming friend request: user1_id=$target_user_id, user2_id=$current_user_id");
                    $notification_data = [
                        'user_id' => $target_user_id,
                        'type' => 'friend_request_declined',
                        'source_id' => $current_user_id,
                        'is_read' => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $db->insert('notifications', $notification_data);
                    error_log("Decline notification created for user_id=$target_user_id");
                    $db->commit();
                    echo json_encode(['status' => 'success', 'message' => 'Friend request declined']);
                } else {
                    throw new Exception('Failed to cancel or decline friend request: ' . $db->getLastError());
                }
            }
            exit;
        }

        if ($_POST['action'] === 'unfriend') {
            $target_user_id = isset($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : 0;

            if ($target_user_id <= 0 || $target_user_id == $current_user_id) {
                throw new Exception('Invalid user ID');
            }

            $db->where("user1_id", min($current_user_id, $target_user_id));
            $db->where("user2_id", max($current_user_id, $target_user_id));
            $db->where("status", 'accepted');
            error_log("Unfriend attempt: current_user_id=$current_user_id, target_user_id=$target_user_id");
            error_log("Unfriend WHERE clause: user1_id=" . min($current_user_id, $target_user_id) . ", user2_id=" . max($current_user_id, $target_user_id) . ", status='accepted'");
            $deleted = $db->delete('friendships');
            error_log("MysqliDb last query: " . $db->getLastQuery());

            if ($deleted && $db->count > 0) {
                error_log("Unfriended successfully: user1_id=$current_user_id, user2_id=$target_user_id. Rows affected: " . $db->count);
                $db->commit();
                echo json_encode(['status' => 'success', 'message' => 'Unfriended successfully']);
            } else {
                $db->rollback();
                if ($db->count === 0) {
                    error_log("Unfriend failed: Friendship record not found for user1_id=$current_user_id, user2_id=$target_user_id with status 'accepted'.");
                    echo json_encode(['status' => 'error', 'message' => 'Friendship not found or already unfriended.', 'debug_query' => $db->getLastQuery()]);
                } else {
                    error_log("Failed to unfriend: " . $db->getLastError());
                    echo json_encode(['status' => 'error', 'message' => 'Failed to unfriend: ' . $db->getLastError(), 'debug_query' => $db->getLastQuery()]);
                }
            }
            exit;
        }
    } catch (Exception $e) {
        $db->rollback();
        $debug_query = $db->getLastQuery();
        error_log("User Profile Error: " . $e->getMessage() . " | Last Query: " . $debug_query);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'debug_query' => $debug_query]);
        exit;
    }

    // Create Post (only for own profile)
    if ($_POST['action'] === 'create_post' && $is_own_profile) {
        $content = trim($_POST['postContent'] ?? '');
        $visibility = $_POST['privacy'] ?? 'public';
        $allowed_visibilities = ['public', 'friends', 'private'];

        if (empty($content) || strlen($content) < 5) {
            $_SESSION['error'] = "Post content must be at least 5 characters long.";
            header("Location: " . $_SERVER['PHP_SELF'] . ($viewing_user_id ? "?user_id=$viewing_user_id" : ""));
            exit;
        }
        if (!in_array($visibility, $allowed_visibilities)) {
            $_SESSION['error'] = "Invalid visibility setting.";
            header("Location: " . $_SERVER['PHP_SELF'] . ($viewing_user_id ? "?user_id=$viewing_user_id" : ""));
            exit;
        }

        $uploadDir = "assets/contentimages/$current_user_id/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadedImages = [];
        if (!empty($_FILES['media']['name'][0])) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $maxFileSize = 5 * 1024 * 1024;

            foreach ($_FILES['media']['name'] as $key => $name) {
                $tmpName = $_FILES['media']['tmp_name'][$key];
                $error = $_FILES['media']['error'][$key];
                $size = $_FILES['media']['size'][$key];

                if ($error === UPLOAD_ERR_OK) {
                    if ($size > $maxFileSize) {
                        $_SESSION['error'] = "File size too large. Maximum 5MB allowed.";
                        continue;
                    }

                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowedExtensions)) {
                        $_SESSION['error'] = "Invalid file type. Only JPG, PNG, GIF, WEBP allowed.";
                        continue;
                    }

                    $imageInfo = getimagesize($tmpName);
                    if ($imageInfo === false) {
                        $_SESSION['error'] = "Invalid image file.";
                        continue;
                    }

                    $randomName = uniqid('img_', true) . '.' . $ext;
                    $targetFile = $uploadDir . $randomName;

                    if (move_uploaded_file($tmpName, $target = $targetFile)) {
                        $uploadedImages[] = $randomName;
                    }
                }
            }
        }

        $imagesCSV = implode(',', $uploadedImages);
        $data = [
            'user_id' => $current_user_id,
            'content' => $content,
            'images' => $imagesCSV,
            'visibility' => $visibility,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db->startTransaction();
        try {
            if ($db->insert('posts', $data)) {
                $db->commit();
                $_SESSION['message'] = "Post created successfully";
                header("Location: " . $_SERVER['PHP_SELF'] . ($viewing_user_id ? "?user_id=$viewing_user_id" : ""));
                exit;
            } else {
                throw new Exception("Failed to create post: " . $db->getLastError());
            }
        } catch (Exception $e) {
            $db->rollback();
            error_log("Create Post Error: " . $e->getMessage());
            $_SESSION['error'] = "Failed to create post. Please try again.";
            header("Location: " . $_SERVER['PHP_SELF'] . ($viewing_user_id ? "?user_id=$viewing_user_id" : ""));
            exit;
        }
    }

    // Load Comments
    if ($_POST['action'] === 'load_comments') {
        header('Content-Type: application/json');
        try {
            $postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
            if ($postId <= 0) {
                throw new Exception('Invalid post ID');
            }

            $comments_query = "
                SELECT 
                    c.comment_id,
                    c.content,
                    c.created_at,
                    u.username,
                    COALESCE(up.profile_picture, 'assets/default-avatar.png') as profile_picture
                FROM comments c
                JOIN users u ON c.user_id = u.user_id
                LEFT JOIN user_profile up ON c.user_id = up.user_id
                WHERE c.post_id = ?
                ORDER BY c.created_at ASC
                LIMIT 50
            ";
            $comments = $db->rawQuery($comments_query, [$postId]);

            echo json_encode(['status' => 'success', 'comments' => $comments]);
            exit;
        } catch (Exception $e) {
            error_log("Load Comments Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
            exit;
        }
    }

    // Delete Post
    if ($_POST['action'] === 'delete_post') {
        header('Content-Type: application/json');
        try {
            $postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
            if ($postId <= 0) {
                throw new Exception('Invalid post ID');
            }
            $db->where('post_id', $postId);
            $db->where('user_id', $current_user_id);
            if (!$db->delete('posts')) {
                throw new Exception('Failed to delete post: ' . $db->getLastError());
            }
            error_log("Post deleted: post_id=$postId, user_id=$current_user_id");
            $db->commit();
            echo json_encode(['status' => 'success']);
            exit;
        } catch (Exception $e) {
            $db->rollback();
            error_log("Delete Post Error: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }
}

// Function to get user posts with visibility filtering
function getUserPosts($db, $user_id, $viewer_id, $is_own_profile)
{
    $visibility_condition = "";
    $params = [$viewer_id, $user_id];

    if (!$is_own_profile) {
        $friendship_info = getFriendshipStatus($db, $viewer_id, $user_id);
        if ($friendship_info['status'] == 'accepted') {
            $visibility_condition = "AND (p.visibility = 'public' OR p.visibility = 'friends')";
        } else {
            $visibility_condition = "AND p.visibility = 'public'";
        }
    }

    $posts_query = "
        SELECT 
            p.post_id,
            p.user_id,
            p.content,
            p.visibility,
            p.images,
            p.created_at,
            p.original_post_id,
            u.username,
            COALESCE(up.profile_picture, 'assets/default-avatar.png') as profile_picture,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) as like_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comment_count,
            (SELECT COUNT(*) FROM shares WHERE post_id = p.post_id) as share_count,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id AND user_id = ?) as user_liked,
            op.content as original_content,
            op.images as original_images,
            op.created_at as original_created_at,
            ou.username as original_username,
            COALESCE(oup.profile_picture, 'assets/default-avatar.png') as original_profile_picture,
            op.user_id as original_user_id
        FROM posts p
        JOIN users u ON p.user_id = u.user_id
        LEFT JOIN user_profile up ON p.user_id = up.user_id
        LEFT JOIN posts op ON p.original_post_id = op.post_id
        LEFT JOIN users ou ON op.user_id = ou.user_id
        LEFT JOIN user_profile oup ON ou.user_id = oup.user_id
        WHERE p.user_id = ? $visibility_condition
        ORDER BY p.created_at DESC
        LIMIT 50
    ";
    return $db->rawQuery($posts_query, $params);
}

// Get posts for the profile
$posts = getUserPosts($db, $viewing_user_id, $current_user_id, $is_own_profile);

// Function to format time ago
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

<div class="container mt-4">
    <!-- Display Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-header-content">
            <!-- Cover Photo -->
            <div class="cover-photo-container">
                <img src="<?= htmlspecialchars($profile_user['cover_photo']) ?>"
                    class="cover-photo-section" alt="<?= htmlspecialchars($profile_user['username']) ?>">
            </div>

            <!-- Profile Picture -->
            <img src="<?= htmlspecialchars($profile_user['profile_picture']) ?>"
                class="profile-pic-xl prfile-pic-md profile-pic-sm"
                alt="<?= htmlspecialchars($profile_user['username']) ?>">

            <!-- Profile Info -->
            <div class="profile-info">
                <h1 class="profile-name"><?= htmlspecialchars($profile_user['username']) ?></h1>
            </div>

            <!-- Profile Actions -->
            <div class="profile-actions" style="margin-top: 60px;" id="profile-actions">
                <?php if ($is_own_profile): ?>
                    <a href="edit-profile.php" class="btn btn-primary">
                        <i class="fas fa-pencil-alt me-2"></i> Edit Profile
                    </a>
                <?php else: ?>
                    <div id="friendship-buttons">
                        <?php if ($friendship_info['status'] == 'accepted'): ?>
                            <button class="btn btn-success me-2" disabled>
                                <i class="fas fa-user-check me-2"></i>Friends
                            </button>
                            <button class="btn btn-outline-danger me-2" onclick="socialTalk.unfriend(<?= $viewing_user_id; ?>)">
                                <i class="fas fa-user-times me-2"></i>Unfriend
                            </button>
                        <?php elseif ($friendship_info['outgoing_pending'] && $friendship_info['incoming_pending']): ?>
                            <button class="btn btn-secondary me-2" disabled>
                                <i class="fas fa-hourglass-half me-2"></i>Request Sent
                            </button>
                            <button class="btn btn-warning me-2" onclick="acceptFriendRequest(<?= $viewing_user_id; ?>)">
                                <i class="fas fa-user-check me-2"></i>Accept Request
                            </button>
                            <button class="btn btn-outline-danger me-2" onclick="cancelFriendRequest(<?= $viewing_user_id; ?>)">
                                <i class="fas fa-times me-2"></i>Cancel Your Request
                            </button>
                        <?php elseif ($friendship_info['outgoing_pending']): ?>
                            <button class="btn btn-secondary me-2" disabled>
                                <i class="fas fa-hourglass-half me-2"></i>Request Sent
                            </button>
                            <button class="btn btn-outline-danger me-2" onclick="cancelFriendRequest(<?= $viewing_user_id; ?>)">
                                <i class="fas fa-times me-2"></i>Cancel Request
                            </button>
                        <?php elseif ($friendship_info['incoming_pending']): ?>
                            <button class="btn btn-warning me-2" onclick="acceptFriendRequest(<?= $viewing_user_id; ?>)">
                                <i class="fas fa-user-check me-2"></i>Accept Request
                            </button>
                            <button class="btn btn-outline-danger me-2" onclick="cancelFriendRequest(<?= $viewing_user_id; ?>)">
                                <i class="fas fa-times me-2"></i>Decline
                            </button>
                        <?php else: ?>
                            <button class="btn btn-primary me-2" onclick="sendFriendRequest(<?= $viewing_user_id; ?>)">
                                <i class="fas fa-user-plus me-2"></i>Add Friend
                            </button>
                        <?php endif; ?>


                        <button class="btn btn-success"
                            onclick="startMessaging(<?= $viewing_user_id ?>)"
                            data-bs-toggle="tooltip"
                            title="Send message to <?= htmlspecialchars($profile_user['username']) ?>">
                            <i class="fas fa-envelope me-2"></i>Message
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-4">
            <!-- About Card -->
            <div class="profile-card">
                <h4 class="mb-4">About</h4>

                <?php if (!empty($profile_user['blood_group'])): ?>
                    <div class="info-item d-flex">
                        <i class="fas fa-tint fa-lg mt-1"></i>
                        <div>
                            <h6 class="mb-0">  Blood Group</h6>
                            <p class="text-muted"><?= htmlspecialchars($profile_user['blood_group']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile_user['country'])): ?>
                    <div class="info-item d-flex">
                        <i class="fas fa-globe fa-lg mt-1"></i>
                        <div>
                            <h6 class="mb-0">  Country</h6>
                            <p class="text-muted"><?= htmlspecialchars($profile_user['country']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile_user['city'])): ?>
                    <div class="info-item d-flex">
                        <i class="fas fa-city fa-lg mt-1"></i>
                        <div>
                            <h6 class="mb-0">  City</h6>
                            <p class="text-muted"><?= htmlspecialchars($profile_user['city']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile_user['gender'])): ?>
                    <div class="info-item d-flex">
                        <i class="fas fa-venus-mars fa-lg mt-1 "></i>
                        <div>
                            <h6 class="mb-0">  Gender</h6>
                            <p class="text-muted"><?= htmlspecialchars($profile_user['gender']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile_user['relationship'])): ?>
                    <div class="info-item d-flex">
                        <i class="fas fa-heart fa-lg mt-1"></i> <!-- Changed to heart icon for relationship -->
                        <div>
                            <h6 class="mb-0">  Relationship</h6> <!-- Fixed spelling -->
                            <p class="text-muted"><?= htmlspecialchars($profile_user['relationship']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile_user['date_of_birth'])): ?>
                    <div class="info-item d-flex">
                        <i class="fas fa-birthday-cake fa-lg mt-1"></i>
                        <div>
                            <h6 class="mb-0">  Date of Birth</h6>
                            <p class="text-muted"><?= date('F j, Y', strtotime($profile_user['date_of_birth'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile_user['phone_number'])): ?>
                    <div class="info-item d-flex">
                        <i class="fas fa-phone fa-lg mt-1"></i>
                        <div>
                            <h6 class="mb-0">  Phone</h6>
                            <p class="text-muted"><?= htmlspecialchars($profile_user['phone_number']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="info-item d-flex">
                    <i class="fas fa-calendar-alt fa-lg mt-1"></i>
                    <div>
                        <h6 class="mb-0">  Joined</h6>
                        <p class="text-muted"><?= date('F j, Y', strtotime($profile_user['created_at'])) ?></p>
                    </div>
                </div>

                <?php if ($is_own_profile): ?>
                    <a href="edit-about.php" class="btn btn-primary btn-sm px-3 py-2 mt-3" style="font-size: 0.9rem;">
                        <i class="fas fa-edit"></i> Edit About
                    </a>
                <?php endif; ?>
            </div>
            <!-- Photos Card -->
            <div class="profile-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Photos</h4>
                    <a href="photo.php?user_id=<?= $viewing_user_id ?>" class="text-primary" data-section="photos">See All</a>
                </div>

                <div class="photo-grid">
                    <?php
                    $db->where("user_id", $viewing_user_id);
                    $db->where("images IS NOT NULL");
                    $db->where("images != ''");
                    $photoPosts = $db->get("posts");

                    $allImages = [];
                    foreach ($photoPosts as $post) {
                        if (!empty($post['images'])) {
                            $imgs = explode(',', $post['images']);
                            foreach ($imgs as $img) {
                                $trimmed = trim($img);
                                if (!empty($trimmed)) {
                                    $allImages[] = $trimmed;
                                }
                            }
                        }
                    }

                    $displayImages = array_slice($allImages, 0, 6);

                    if (!empty($displayImages)):
                        foreach ($displayImages as $img): ?>
                            <img src="assets/contentimages/<?= $viewing_user_id; ?>/<?= htmlspecialchars($img); ?>" alt="Photo" class="img-fluid rounded">
                    <?php
                        endforeach;
                    else:
                        echo '<p class="text-muted">No photos uploaded yet.</p>';
                    endif;
                    ?>
                </div>
            </div>

            <!-- Friends Card -->
            <div class="profile-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Friends</h4>
                    <a href="friend.php?user_id=<?= $viewing_user_id ?>" class="text-primary" data-section="friends">See All</a>
                </div>

                <?php
                $friends_count_query = "
                    SELECT COUNT(*) as count 
                    FROM friendships 
                    WHERE (user1_id = ? OR user2_id = ?) AND status = 'accepted'
                ";
                $friend_count_result = $db->rawQuery($friends_count_query, [$viewing_user_id, $viewing_user_id]);
                $friend_count = $friend_count_result[0]['count'] ?? 0;
                ?>

                <p class="text-muted mb-4"><?= $friend_count ?> friends</p>

                <?php
                $sample_friends_query = "
                    SELECT u.username, COALESCE(up.profile_picture, 'assets/default-avatar.png') as profile_picture, u.user_id
                    FROM friendships f
                    JOIN users u ON (CASE WHEN f.user1_id = ? THEN f.user2_id ELSE f.user1_id END = u.user_id)
                    LEFT JOIN user_profile up ON u.user_id = up.user_id
                    WHERE (f.user1_id = ? OR f.user2_id = ?) AND f.status = 'accepted'
                    LIMIT 4
                ";
                $sample_friends = $db->rawQuery($sample_friends_query, [$viewing_user_id, $viewing_user_id, $viewing_user_id]);
                ?>

                <div class="row">
                    <?php if (!empty($sample_friends)): ?>
                        <?php foreach ($sample_friends as $friend): ?>
                            <div class="col-6 friend-card">
                                <a href="user-profile.php?user_id=<?= $friend['user_id'] ?>">
                                    <img src="<?= htmlspecialchars($friend['profile_picture']) ?>" alt="<?= htmlspecialchars($friend['username']) ?>">
                                    <h6><?= htmlspecialchars($friend['username']) ?></h6>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php if ($is_own_profile): ?>
                            <p class="text-muted">No friends yet. <a href="find-friend.php">Find friends</a></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-8">
            <!-- Profile Navigation -->
            <ul class="nav profile-nav mb-4">
                <li class="nav-item">
                    <a class="nav-link active" href="user-profile.php?user_id=<?= $viewing_user_id ?>" data-section="posts">Posts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="user-profile-about.php?user_id=<?= $viewing_user_id ?>" data-section="about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="friend.php?user_id=<?= $viewing_user_id ?>" data-section="friends">Friends</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="photo.php?user_id=<?= $viewing_user_id ?>" data-section="photos">Photos</a>
                </li>
            </ul>

            <!-- Dynamic Content Area -->
            <div id="content-area">
                <!-- Create Post Form (Only show for own profile) -->
                <?php if ($is_own_profile): ?>
                    <form action="<?php echo $_SERVER['PHP_SELF'] . ($viewing_user_id ? "?user_id=$viewing_user_id" : ""); ?>" id="createPostForm" name="createPostForm" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="create_post">

                        <div class="post-card p-4" id="feedSection">
                            <!-- Privacy Dropdown -->
                            <div class="d-flex mb-3 justify-content-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" id="privacyDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-globe-americas me-2"></i> Public
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button class="dropdown-item d-flex align-items-center active" type="button" data-value="public">
                                                <i class="fas fa-globe-americas me-2"></i> Public
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item d-flex align-items-center" type="button" data-value="friends">
                                                <i class="fas fa-user-friends me-2"></i> Friends Only
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item d-flex align-items-center" type="button" data-value="private">
                                                <i class="fas fa-lock me-2"></i> Only Me
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <input type="hidden" name="privacy" id="selectedPrivacy" value="public">
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                <img src="<?= htmlspecialchars($current_user['profile_picture']) ?>" class="profile-pic me-3" alt="Your profile">
                                <textarea name="postContent" class="form-control" placeholder="What's on your mind, <?= htmlspecialchars($current_user['username']) ?>?" required minlength="2" rows="3"></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <input type="file" name="media[]" id="media" class="d-none" multiple accept="image/*">
                                <div id="selectedImages"></div>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <label for="media" class="btn btn-light flex-fill me-2">
                                    <i class="fas fa-image me-2"></i>Add Image
                                </label>
                                <button class="btn btn-primary flex-fill" type="submit" name="createPost" id="createPost">
                                    <i class="fas fa-edit me-2"></i>Create Post
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>

                <!-- Posts Feed -->
                <div class="container mt-4">
                    <?php if (empty($posts)): ?>
                        <div class="card post-card">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5>No posts to show</h5>
                                <p class="text-muted"><?= $is_own_profile ? 'Create your first post!' : 'This user has no posts yet.' ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="card post-card" data-post-id="<?php echo $post['post_id']; ?>" id="post-<?php echo $post['post_id']; ?>">
                                <div class="card-body">
                                    <!-- Post Header -->
                                    <div class="d-flex align-items-center mb-3 justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <a href="user-profile.php?user_id=<?= htmlspecialchars($post['user_id']); ?>" style="text-decoration: none;">
                                                <img src="<?php echo htmlspecialchars($post['profile_picture'] ?: 'assets/default-avatar.png'); ?>"
                                                    alt="Profile" class="profile-img me-3">
                                            </a>
                                            <div>
                                                <h6 class="mb-0">
                                                    <a href="user-profile.php?user_id=<?= htmlspecialchars($post['user_id']); ?>" style="text-decoration: none;">
                                                        <?= htmlspecialchars($post['username']); ?>
                                                    </a>
                                                </h6>
                                                <small class="text-muted">
                                                    <?php echo timeAgo($post['created_at']); ?>
                                                    <?php if ($post['visibility'] === 'friends'): ?>
                                                        <i class="fas fa-users ms-1" title="Friends only"></i>
                                                    <?php elseif ($post['visibility'] === 'public'): ?>
                                                        <i class="fas fa-globe ms-1" title="Public"></i>
                                                    <?php elseif ($post['visibility'] === 'private'): ?>
                                                        <i class="fas fa-lock ms-1" title="Private"></i>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-link text-muted" type="button" id="postOptions<?= $post['post_id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="postOptions<?= $post['post_id']; ?>">
                                                <?php if ($post['user_id'] !== $_SESSION['user_id']): ?>
                                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#reportModal" data-post-id="<?= $post['post_id']; ?>">Report Post</a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Post Content -->
                                    <?php if (!empty($post['original_post_id'])): ?>
                                        <div class="shared-post-container border p-3 mb-3 rounded">
                                            <div class="d-flex align-items-center mb-2">
                                                <img src="<?php echo htmlspecialchars($post['original_profile_picture'] ?: 'assets/default-avatar.png'); ?>"
                                                    alt="Original Profile" class="profile-img me-2" style="width: 30px; height: 30px;">
                                                <div>
                                                    <h6 class="mb-0" style="font-size: 0.9em;">
                                                        <a href="user-profile.php?user_id=<?= htmlspecialchars($post['original_user_id']); ?>" style="text-decoration: none;">
                                                            <?= htmlspecialchars($post['original_username']); ?>
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <?php echo timeAgo($post['original_created_at']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <p class="mb-3"><?php echo nl2br(htmlspecialchars($post['original_content'])); ?></p>
                                            <?php if (!empty($post['original_images'])): ?>
                                                <?php
                                                $original_images = explode(',', $post['original_images']);
                                                $original_image_count = count($original_images);
                                                ?>
                                                <div class="post-images">
                                                    <?php foreach ($original_images as $index => $image): ?>
                                                        <a href="assets/contentimages/<?= $post['original_user_id'] ?>/<?= htmlspecialchars(trim($image)); ?>" data-lightbox="post-images-<?php echo $post['post_id']; ?>-original"><img src="assets/contentimages/<?= $post['original_user_id'] ?>/<?= htmlspecialchars(trim($image)); ?>"
                                                                alt="Original Post image" class="post-image"
                                                                style="<?php echo $original_image_count === 1 ? 'max-width: 100%;' : 'width:200px'; ?>"></a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="mb-3"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                                        <?php if (!empty($post['images'])): ?>
                                            <?php
                                            $images = explode(',', $post['images']);
                                            $image_count = count($images);
                                            ?>
                                            <div class="post-images">
                                                <?php foreach ($images as $index => $image): ?>
                                                    <a href="assets/contentimages/<?= $post['user_id'] ?>/<?= htmlspecialchars(trim($image)); ?>" data-lightbox="post-images-<?php echo $post['post_id']; ?>"><img src="assets/contentimages/<?= $post['user_id'] ?>/<?= htmlspecialchars(trim($image)); ?>"
                                                            alt="Post image" class="post-image"
                                                            style="<?php echo $image_count === 1 ? 'max-width: 100%;' : 'width:200px'; ?>"></a>
                                                <?php endforeach; ?>
                                                <?php if ($image_count > 4): ?>
                                                    <div class="more-images-overlay">
                                                        <span>+<?php echo $image_count - 4; ?> more</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>


                                    <div class="mb-2">
                                        <span class="text-muted likecomment">
                                            <?= $post['like_count']; ?> likes • <?= $post['comment_count']; ?> comments
                                        </span>
                                    </div>

                                    <!-- Post Actions -->
                                    <div class="d-flex justify-content-between border-top pt-2">
                                        <!-- Like -->
                                        <button class="like-btn btn btn-light flex-fill me-2 <?= $post['user_liked'] ? 'text-danger' : ''; ?>"
                                            onclick="toggleLike(<?= $post['post_id']; ?>)">
                                            <i class="fas fa-heart me-1"></i>
                                            <span class="like-text">Like</span>
                                            (<span class="like-count"><?= $post['like_count']; ?></span>)
                                        </button>

                                        <!-- Comment -->
                                        <button class="btn btn-light flex-fill me-2" onclick="toggleComments(<?= $post['post_id']; ?>)">
                                            <i class="fas fa-comment me-1"></i>
                                            <span class="comment-text">Comment</span>
                                            (<span class="comment-count"><?= $post['comment_count']; ?></span>)
                                        </button>

                                        <!-- Share -->
                                        <button class="btn btn-light flex-fill" onclick="socialTalk.sharePost(<?= $post['post_id']; ?>)">
                                            <i class="fas fa-share me-1"></i>Share
                                        </button>
                                    </div>

                                    <!-- Comment Section -->
                                    <div class="comment-section mt-3" id="comments-<?= $post['post_id']; ?>" style="display: none;">
                                        <div class="border-top pt-3">
                                            <div class="d-flex mb-3">
                                                <img src="<?= htmlspecialchars($current_user['profile_picture']); ?>"
                                                    alt="Your Profile" class="profile-img me-2">
                                                <div class="flex-grow-1">
                                                    <input type="text" class="form-control form-control-sm comment-input commentinput"
                                                        placeholder="Write a comment..."
                                                        onkeypress="handleCommentSubmit(event, <?= $post['post_id']; ?>)">
                                                </div>
                                            </div>
                                            <div class="comments-list" id="comments-list-<?= $post['post_id']; ?>">
                                                <!-- Comments will be loaded here -->
                                                <!-- Comments will be loaded here end -->

                                            </div>
                                        </div>
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
    function sendFriendRequest(targetUserId) {
        fetch('user-profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=send_friend_request&target_user_id=${targetUserId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    location.reload(); // Reload to update buttons
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while sending the friend request.');
            });
    }

    function acceptFriendRequest(targetUserId) {
        fetch('user-profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=accept_friend_request&target_user_id=${targetUserId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while accepting the friend request.');
            });
    }

    function cancelFriendRequest(targetUserId) {
        fetch('user-profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=cancel_friend_request&target_user_id=${targetUserId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while cancelling the friend request.');
            });
    }

    

    //messages
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    function startMessaging(userId) {
        window.location.href = 'messages.php?user_id=' + userId;
    }


    function deletePost(postId) {
        if (confirm('Are you sure you want to delete this post?')) {
            fetch('user-profile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=delete_post&post_id=${postId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the post.');
                });
        }
    }

    function editPost(postId) {
        // Implement edit post functionality
    }


    function timeAgoJS(date) {
        const time = Math.floor((new Date() - new Date(date)) / 1000);
        if (time < 60) return 'just now';
        if (time < 3600) return Math.floor(time / 60) + ' minutes ago';
        if (time < 86400) return Math.floor(time / 3600) + ' hours ago';
        if (time < 2592000) return Math.floor(time / 86400) + ' days ago';
        if (time < 31536000) return Math.floor(time / 2592000) + ' months ago';
        return Math.floor(time / 31536000) + ' years ago';
    }

    // Handle privacy dropdown
    document.querySelectorAll('.dropdown-item[data-value]').forEach(item => {
        item.addEventListener('click', () => {
            const value = item.getAttribute('data-value');
            document.getElementById('selectedPrivacy').value = value;
            document.getElementById('privacyDropdown').innerHTML = `
            <i class="fas fa-${value === 'public' ? 'globe-americas' : value === 'friends' ? 'user-friends' : 'lock'} me-2"></i>
            ${value === 'public' ? 'Public' : value === 'friends' ? 'Friends Only' : 'Only Me'}
        `;
            document.querySelectorAll('.dropdown-item[data-value]').forEach(i => i.classList.remove('active'));
            item.classList.add('active');
        });
    });
</script>

<?php
include_once 'includes/footer1.php';
?>
<script>
    function toggleLike(postId) {
        fetch('apis/like.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ post_id: postId, action: 'like' }),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                console.error('Server error:', data.message);
                return;
            }
            const likeBtn = document.querySelector(`[data-post-id="${postId}"] .like-btn`);
            const likeCount = likeBtn.querySelector('.like-count');

            if (data.status === 'liked') {
                likeBtn.classList.add('text-danger');
                likeCount.textContent = parseInt(likeCount.textContent) + 1;
            } else {
                likeBtn.classList.remove('text-danger');
                likeCount.textContent = Math.max(0, parseInt(likeCount.textContent) - 1);
            }

            const summaryText = document.querySelector(`[data-post-id="${postId}"] .text-muted`);
            const newLikeCount = likeCount.textContent;
            const commentCount = document.querySelector(`[data-post-id="${postId}"] .comment-count`).textContent;
            summaryText.textContent = `${newLikeCount} likes • ${commentCount} comments`;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to process like. Please try again.');
        });
    }

    function toggleComments(postId) {
        const commentsSection = document.getElementById(`comments-${postId}`);
        if (commentsSection.style.display === 'none') {
            commentsSection.style.display = 'block';
            loadComments(postId);
        } else {
            commentsSection.style.display = 'none';
        }
    }

    function handleCommentSubmit(event, postId) {
        if (event.key === 'Enter') {
            const comment = event.target.value.trim();
            if (comment) {
                addComment(postId, comment);
                event.target.value = '';
            }
        }
    }

    function addComment(postId, comment) {
        fetch('apis/comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add_comment&post_id=${postId}&comment=${encodeURIComponent(comment)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                loadComments(postId);
                const commentCount = document.querySelector(`[data-post-id="${postId}"] .comment-count`);
                const currentCount = parseInt(commentCount.textContent);
                commentCount.textContent = currentCount + 1;
                
                const summaryText = document.querySelector(`[data-post-id="${postId}"] .text-muted`);
                const likeCount = document.querySelector(`[data-post-id="${postId}"] .like-count`).textContent;
                summaryText.textContent = `${likeCount} likes • ${currentCount + 1} comments`;
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function loadComments(postId) {
        fetch('apis/comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=load_comments&post_id=${postId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const commentsList = document.getElementById(`comments-list-${postId}`);
                commentsList.innerHTML = '';
                
                data.comments.forEach(comment => {
                    const commentHtml = `
                        <div class="comment-item d-flex">
                            <img src="${comment.profile_picture || 'assets/default-avatar.png'}" 
                                 alt="Profile" class="profile-img me-2">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <strong class="small">${comment.username}</strong>
                                    <small class="text-muted">${timeAgoJS(comment.created_at)}</small>
                                </div>
                                <p class="mb-0 small">${comment.content}</p>
                            </div>
                        </div>
                    `;
                    commentsList.innerHTML += commentHtml;
                });
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function timeAgoJS(datetime) {
        const time = Math.floor((new Date() - new Date(datetime)) / 1000);
        
        if (time < 60) return 'just now';
        if (time < 3600) return Math.floor(time/60) + ' minutes ago';
        if (time < 86400) return Math.floor(time/3600) + ' hours ago';
        if (time < 2592000) return Math.floor(time/86400) + ' days ago';
        if (time < 31536000) return Math.floor(time/2592000) + ' months ago';
        return Math.floor(time/31536000) + ' years ago';
    }

    function sharePost(postId) {
        fetch('apis/share_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ post_id: postId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Failed to share post.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error. Failed to share post.');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const postId = urlParams.get('post_id');
        if (postId) {
            const postElement = document.getElementById('post-' + postId);
            if (postElement) {
                postElement.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });
</script>
