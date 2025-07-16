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

// Get education history
$db->where("user_id", $viewing_user_id);
$education = $db->get("education");

// Get work history
$db->where("user_id", $viewing_user_id);
$work_history = $db->get("work_history");

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
            if (!$db->delete('friendships')) {
                throw new Exception('Failed to unfriend: ' . $db->getLastError());
            }
            error_log("Unfriended: user1_id=$current_user_id, user2_id=$target_user_id");

            $db->commit();
            echo json_encode(['status' => 'success', 'message' => 'Unfriended successfully']);
            exit;
        }
    } catch (Exception $e) {
        $db->rollback();
        error_log("User Profile Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

include_once 'includes/header1.php';
?>

<div class="container mt-4">


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
                <h1 class="profile-name">
                    <?= htmlspecialchars($profile_user['first_name'] ?? $profile_user['username']) ?>
                    <?= htmlspecialchars($profile_user['last_name'] ?? '') ?>
                </h1>

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
                            <button class="btn btn-outline-danger me-2" onclick="unfriend(<?= $viewing_user_id; ?>)">
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
                        <i class="fas fa-tint mt-1"></i>
                        <div>
                            <h6 class="mb-0">Blood Group</h6>
                            <p class="text-muted"><?= htmlspecialchars($profile_user['blood_group']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile_user['country'])): ?>
                    <div class="info-item d-flex">
                        <i class="fas fa-globe mt-1"></i>
                        <div>
                            <h6 class="mb-0">Country</h6>
                            <p class="text-muted"><?= htmlspecialchars($profile_user['country']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile_user['city'])): ?>
                    <div class="info-item d-flex">
                        <i class="fas fa-city mt-1"></i>
                        <div>
                            <h6 class="mb-0">City</h6>
                            <p class="text-muted"><?= htmlspecialchars($profile_user['city']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile_user['gender'])): ?>
                    <div class="info-item d-flex">
                        <i class="fas fa-venus-mars mt-1"></i>
                        <div>
                            <h6 class="mb-0">Gender</h6>
                            <p class="text-muted"><?= htmlspecialchars($profile_user['gender']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile_user['relationship'])): ?>
                    <div class="info-item d-flex">
                        <i class="fas fa-heart mt-1"></i> <!-- Changed to heart icon for relationship -->
                        <div>
                            <h6 class="mb-0">Relationship</h6> <!-- Fixed spelling -->
                            <p class="text-muted"><?= htmlspecialchars($profile_user['relationship']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile_user['date_of_birth'])): ?>
                    <div class="info-item d-flex">
                        <i class="fas fa-birthday-cake mt-1"></i>
                        <div>
                            <h6 class="mb-0">Date of Birth</h6>
                            <p class="text-muted"><?= date('F j, Y', strtotime($profile_user['date_of_birth'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile_user['phone_number'])): ?>
                    <div class="info-item d-flex">
                        <i class="fas fa-phone mt-1"></i>
                        <div>
                            <h6 class="mb-0">Phone</h6>
                            <p class="text-muted"><?= htmlspecialchars($profile_user['phone_number']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="info-item d-flex">
                    <i class="fas fa-calendar-alt mt-1"></i>
                    <div>
                        <h6 class="mb-0">Joined</h6>
                        <p class="text-muted"><?= date('F j, Y', strtotime($profile_user['created_at'])) ?></p>
                    </div>
                </div>

                <?php if ($is_own_profile): ?>
                    <a href="edit-profile.php" class="btn btn-primary btn-sm px-3 py-2 mt-3" style="font-size: 0.9rem;">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-8">
            <!-- Profile Navigation -->
            <ul class="nav profile-nav mb-4">
                <li class="nav-item">
                    <a class="nav-link" href="user-profile.php?user_id=<?= $viewing_user_id ?>" data-section="posts">Posts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="user-profile-about.php?user_id=<?= $viewing_user_id ?>" data-section="about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="friend.php?user_id=<?= $viewing_user_id ?>" data-section="friends">Friends</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="photo.php?user_id=<?= $viewing_user_id ?>" data-section="photos">Photos</a>
                </li>
            </ul>

            <!-- About Section -->
            <div class="profile-card">
                <h4 class="mb-4">About <?= htmlspecialchars($profile_user['first_name'] ?? $profile_user['username']) ?></h4>

                <?php if (!empty($profile_user['bio'])): ?>
                    <p><?= htmlspecialchars($profile_user['bio']) ?></p>
                <?php else: ?>
                    <p class="text-muted">No bio information available.</p>
                <?php endif; ?>

                <!-- Work and Education Section -->
                <?php if (!empty($work_history) || !empty($education)): ?>
                    <h5 class="mt-5 mb-3">Work and Education</h5>

                    <?php if (!empty($work_history)): ?>
                        <?php foreach ($work_history as $work): ?>
                            <div class="info-item d-flex mb-3">
                                <i class="fas fa-briefcase mt-1"></i>
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($work['job_title']) ?></h6>
                                    <p class="text-muted"><?= htmlspecialchars($work['company_name']) ?></p>
                                    <?php if (!empty($work['location'])): ?>
                                        <p class="text-muted"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($work['location']) ?></p>
                                    <?php endif; ?>
                                    <p class="text-muted">
                                        <?= date('F Y', strtotime($work['start_date'])) ?>
                                        <?= $work['end_date'] ? ' - ' . date('F Y', strtotime($work['end_date'])) : ' - Present' ?>
                                    </p>
                                    <?php if (!empty($work['description'])): ?>
                                        <p><?= htmlspecialchars($work['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($education)): ?>
                        <?php foreach ($education as $edu): ?>
                            <div class="info-item d-flex mb-3">
                                <i class="fas fa-graduation-cap mt-1"></i>
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($edu['degree']) ?></h6>
                                    <p class="text-muted"><?= htmlspecialchars($edu['institution_name']) ?></p>
                                    <?php if (!empty($edu['field_of_study'])): ?>
                                        <p class="text-muted"><?= htmlspecialchars($edu['field_of_study']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($edu['location'])): ?>
                                        <p class="text-muted"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($edu['location']) ?></p>
                                    <?php endif; ?>
                                    <p class="text-muted">
                                        <?= date('F Y', strtotime($edu['start_date'])) ?>
                                        <?= $edu['end_date'] ? ' - ' . date('F Y', strtotime($edu['end_date'])) : ' - Present' ?>
                                    </p>
                                    <?php if (!empty($edu['description'])): ?>
                                        <p><?= htmlspecialchars($edu['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Places Lived Section -->
                <?php if (!empty($profile_user['country']) || !empty($profile_user['city']) || !empty($profile_user['address_line1'])): ?>
                    <h5 class="mt-5 mb-3">Places Lived</h5>

                    <?php if (!empty($profile_user['city']) || !empty($profile_user['country'])): ?>
                        <div class="info-item d-flex mb-3">
                            <i class="fas fa-home mt-1"></i>
                            <div>
                                <h6 class="mb-0">Current City</h6>
                                <p class="text-muted">
                                    <?= !empty($profile_user['city']) ? htmlspecialchars($profile_user['city']) . ', ' : '' ?>
                                    <?= !empty($profile_user['country']) ? htmlspecialchars($profile_user['country']) : '' ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($profile_user['address_line1'])): ?>
                        <div class="info-item d-flex mb-3">
                            <i class="fas fa-map-marker-alt mt-1"></i>
                            <div>
                                <h6 class="mb-0">Address</h6>
                                <p class="text-muted"><?= htmlspecialchars($profile_user['address_line1']) ?></p>
                                <?php if (!empty($profile_user['address_line2'])): ?>
                                    <p class="text-muted"><?= htmlspecialchars($profile_user['address_line2']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($profile_user['postal_code'])): ?>
                                    <p class="text-muted"><?= htmlspecialchars($profile_user['postal_code']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Contact Information Section -->
                <h5 class="mt-5 mb-3">Contact Information</h5>

                <div class="info-item d-flex mb-3">
                    <i class="fas fa-envelope mt-1"></i>
                    <div>
                        <h6 class="mb-0">Email</h6>
                        <p class="text-muted"><?= htmlspecialchars($profile_user['email']) ?></p>
                    </div>
                </div>

                <?php if (!empty($profile_user['phone_number'])): ?>
                    <div class="info-item d-flex mb-3">
                        <i class="fas fa-phone mt-1"></i>
                        <div>
                            <h6 class="mb-0">Phone</h6>
                            <p class="text-muted"><?= htmlspecialchars($profile_user['phone_number']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Basic Information Section -->
                <h5 class="mt-5 mb-3">Basic Information</h5>

                <?php if (!empty($profile_user['gender'])): ?>
                    <div class="info-item d-flex mb-3">
                        <i class="fas fa-venus-mars mt-1"></i>
                        <div>
                            <h6 class="mb-0">Gender</h6>
                            <p class="text-muted"><?= htmlspecialchars($profile_user['gender']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile_user['date_of_birth'])): ?>
                    <div class="info-item d-flex mb-3">
                        <i class="fas fa-birthday-cake mt-1"></i>
                        <div>
                            <h6 class="mb-0">Birthday</h6>
                            <p class="text-muted"><?= date('F j, Y', strtotime($profile_user['date_of_birth'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile_user['blood_group'])): ?>
                    <div class="info-item d-flex mb-3">
                        <i class="fas fa-tint mt-1"></i>
                        <div>
                            <h6 class="mb-0">Blood Group</h6>
                            <p class="text-muted"><?= htmlspecialchars($profile_user['blood_group']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($profile_user['relationship'])): ?>
                    <div class="info-item d-flex">
                        <i class="fas fa-heart mt-1"></i> <!-- Changed to heart icon for relationship -->
                        <div>
                            <h6 class="mb-0">Relationship</h6> <!-- Fixed spelling -->
                            <p class="text-muted"><?= htmlspecialchars($profile_user['relationship']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($is_own_profile): ?>
                    <div class="mt-4">
                        <a href="edit-about.php" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit About
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
include_once 'includes/footer1.php';
?>

<script>
    function sendFriendRequest(targetUserId) {
        $.ajax({
            url: window.location.href,
            method: 'POST',
            data: {
                action: 'send_friend_request',
                target_user_id: targetUserId
            },
            success: function(response) {
                const result = JSON.parse(response);
                if (result.status === 'success') {
                    alert(result.message);
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            },
            error: function() {
                alert('Error sending friend request');
            }
        });
    }

    function acceptFriendRequest(targetUserId) {
        $.ajax({
            url: window.location.href,
            method: 'POST',
            data: {
                action: 'accept_friend_request',
                target_user_id: targetUserId
            },
            success: function(response) {
                const result = JSON.parse(response);
                if (result.status === 'success') {
                    alert(result.message);
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            },
            error: function() {
                alert('Error accepting friend request');
            }
        });
    }

    function cancelFriendRequest(targetUserId) {
        $.ajax({
            url: window.location.href,
            method: 'POST',
            data: {
                action: 'cancel_friend_request',
                target_user_id: targetUserId
            },
            success: function(response) {
                const result = JSON.parse(response);
                if (result.status === 'success') {
                    alert(result.message);
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            },
            error: function() {
                alert('Error cancelling friend request');
            }
        });
    }

    function unfriend(targetUserId) {
    if (confirm('Are you sure you want to unfriend this user?')) {
        fetch('user-profile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=unfriend&target_user_id=${targetUserId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update UI without reloading
                document.getElementById('friendship-buttons').innerHTML = `
                    <button class="btn btn-primary me-2" onclick="sendFriendRequest(${targetUserId})">
                        <i class="fas fa-user-plus me-2"></i>Add Friend
                    </button>
                    <button class="btn btn-success" 
                        onclick="startMessaging(${targetUserId})"
                        data-bs-toggle="tooltip"
                        title="Send message to user">
                        <i class="fas fa-envelope me-2"></i>Message
                    </button>
                `;
                
                // Show success message without alert
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: 'Unfriended successfully',
                    showConfirmButton: false,
                    timer: 1500
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while trying to unfriend'
            });
        });
    }
}

    function startMessaging(targetUserId) {
        window.location.href = 'messages.php?user_id=' + targetUserId;
    }
</script>