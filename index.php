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


/* 
create post script
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_post') {

    // 1. Retrieve user ID
    $userId = $_SESSION['user_id'];

    // 2. Retrieve post content
    $content = ($_POST['postContent']);



    // 3. Prepare upload path
    $uploadDir = "assets/contentimages/$userId/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // 4. Handle image uploads
    $uploadedImages = [];
    if (!empty($_FILES['media']['name'][0])) {
        foreach ($_FILES['media']['name'] as $key => $name) {
            $tmpName = $_FILES['media']['tmp_name'][$key];
            $error = $_FILES['media']['error'][$key];

            if ($error === UPLOAD_ERR_OK) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $randomName = uniqid('img_', true) . '.' . $ext;
                $targetFile = $uploadDir . $randomName;

                if (move_uploaded_file($tmpName, $targetFile)) {
                    $uploadedImages[] = $randomName;
                    //TODO: resize the image
                }
            }
        }
    }


    // 5. Convert image names to CSV
    $imagesCSV = implode(',', $uploadedImages);

    // 6. Set default visibility
    // Retrieve and validate post content
    $content = ($_POST['postContent'] ?? '');
    $visibility = $_POST['privacy'] ?? 'public';
    $allowed_visibilities = ['public', 'friends', 'private'];

    // Validate inputs
    if (empty($content) || strlen($content) < 2) {
        $_SESSION['error'] = "Post content must be at least 2 characters long.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    if (!in_array($visibility, $allowed_visibilities)) {
        $_SESSION['error'] = "Invalid visibility setting.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }


    // 7. Insert post into database
    // $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, images, visibility) VALUES (?, ?, ?, ?)");
    $data = [
        'user_id' => $userId,
        'content' => $content,
        'images' => $imagesCSV,
        'visibility' => $visibility
    ];
    if ($db->insert('posts', $data)) {
        $_SESSION['message'] = "Post created successfully";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}


/* 
create post script end
*/
// Function to get current user's profile details
function getCurrentUserProfile($db, $user_id)
{
    $query = "
        SELECT 
            u.user_id, 
            u.username, 
            up.profile_picture,
            up.bio
        FROM users u
        LEFT JOIN user_profile up ON u.user_id = up.user_id
        WHERE u.user_id = ?
    ";
    $result = $db->rawQuery($query, [$user_id]);
    $user = $result[0] ?? null;
    if ($user) {
        // Set default profile picture if none exists
        $user['profile_picture'] = $user['profile_picture'] ?: 'assets/default-avatar.png';
        // Use bio (trimmed to 50 characters) or fallback to 'Member'
        $user['bio'] = $user['bio'] ? substr($user['bio'], 0, 50) . (strlen($user['bio']) > 50 ? '...' : '') : 'Member';
    }
    return $user;
}

// Function to count accepted friends
function getFriendCount($db, $user_id)
{
    $query = "
        SELECT COUNT(*) as friend_count
        FROM friendships
        WHERE (user1_id = ? OR user2_id = ?) AND status = 'accepted'
    ";
    $result = $db->rawQuery($query, [$user_id, $user_id]);
    return $result[0]['friend_count'] ?? 0;
}

// Function to count user's posts
function getPostCount($db, $user_id)
{
    $query = "
        SELECT COUNT(*) as post_count
        FROM posts
        WHERE user_id = ?
    ";
    $result = $db->rawQuery($query, [$user_id]);
    return $result[0]['post_count'] ?? 0;
}

// Function to count unread messages
function getUnreadMessageCount($db, $user_id)
{
    $query = "
        SELECT COUNT(*) as unread_count
        FROM messages
        WHERE receiver_id = ? AND is_read = 0
    ";
    $result = $db->rawQuery($query, [$user_id]);
    return $result[0]['unread_count'] ?? 0;
}

// Function to count pending friend requests
function getPendingFriendRequestCount($db, $user_id)
{
    $query = "
        SELECT COUNT(*) as pending_count
        FROM friendships
        WHERE user2_id = ? AND status = 'pending'
    ";
    $result = $db->rawQuery($query, [$user_id]);
    return $result[0]['pending_count'] ?? 0;
}

// Fetch data
$current_user = getCurrentUserProfile($db, $_SESSION['user_id']);
$friend_count = getFriendCount($db, $_SESSION['user_id']);
$post_count = getPostCount($db, $_SESSION['user_id']);
$unread_message_count = getUnreadMessageCount($db, $_SESSION['user_id']);
$pending_request_count = getPendingFriendRequestCount($db, $_SESSION['user_id']);

// Function to get friend suggestions for the current user
function getFriendSuggestions($db, $current_user_id, $limit = 3)
{
    // Get current user's friends
    $friends_query = "
        SELECT CASE 
            WHEN user1_id = ? THEN user2_id 
            ELSE user1_id 
        END as friend_id
        FROM friendships 
        WHERE (user1_id = ? OR user2_id = ?) AND status = 'accepted'
    ";
    $friends = $db->rawQuery($friends_query, [$current_user_id, $current_user_id, $current_user_id]);
    $friend_ids = array_column($friends, 'friend_id');
    $friend_ids[] = $current_user_id; // Exclude current user

    // Convert friend IDs to a comma-separated string for exclusion
    $friend_ids_str = implode(',', array_map('intval', $friend_ids));

    // Get suggested users (not friends, not the current user)
    $suggestions_query = "
        SELECT 
            u.user_id,
            u.username,
            up.profile_picture,
            CONCAT(up.first_name, ' ', up.last_name) as full_name
        FROM users u
        LEFT JOIN user_profile up ON u.user_id = up.user_id
        WHERE u.user_id NOT IN ($friend_ids_str)
        ORDER BY RAND()
        LIMIT ?
    ";
    $suggestions = $db->rawQuery($suggestions_query, [$limit]);

    // Calculate mutual friends for each suggestion
    foreach ($suggestions as &$suggestion) {
        $suggested_user_id = $suggestion['user_id'];
        $mutual_friends_query = "
            SELECT COUNT(*) as mutual_count
            FROM friendships f1
            WHERE f1.status = 'accepted'
            AND (
                (f1.user1_id = ? AND f1.user2_id IN (
                    SELECT CASE 
                        WHEN user1_id = ? THEN user2_id 
                        ELSE user1_id 
                    END
                    FROM friendships 
                    WHERE (user1_id = ? OR user2_id = ?) AND status = 'accepted'
                ))
                OR 
                (f1.user2_id = ? AND f1.user1_id IN (
                    SELECT CASE 
                        WHEN user1_id = ? THEN user2_id 
                        ELSE user1_id 
                    END
                    FROM friendships 
                    WHERE (user1_id = ? OR user2_id = ?) AND status = 'accepted'
                ))
            )
        ";
        $mutual_result = $db->rawQuery($mutual_friends_query, [
            $suggested_user_id,
            $current_user_id,
            $current_user_id,
            $current_user_id,
            $suggested_user_id,
            $current_user_id,
            $current_user_id,
            $current_user_id
        ]);
        $suggestion['mutual_count'] = $mutual_result[0]['mutual_count'] ?? 0;

        // Use default profile picture if none exists
        $suggestion['profile_picture'] = $suggestion['profile_picture'] ?: 'assets/default-avatar.png';
    }

    // Sort by mutual friends (descending) to prioritize relevant suggestions
    usort($suggestions, function ($a, $b) {
        return $b['mutual_count'] <=> $a['mutual_count'];
    });

    return $suggestions;
}

// Get friend suggestions
$friend_suggestions = getFriendSuggestions($db, $_SESSION['user_id'], 3);
/*
show all posts of users and users friends
*/
//////
// Function to get posts for the current user's feed
$current_user_id = $_SESSION['user_id'];
function getFeedPosts($db, $user_id)
{
    // Get user's friends using the actual friendship structure
    $friends_query = "
        SELECT CASE 
            WHEN user1_id = ? THEN user2_id 
            ELSE user1_id 
        END as friend_id
        FROM friendships 
        WHERE (user1_id = ? OR user2_id = ?) AND status = 'accepted'
    ";

    $friends = $db->rawQuery($friends_query, array($user_id, $user_id, $user_id));
    $friend_ids = array($user_id); // Include current user's posts

    foreach ($friends as $friend) {
        $friend_ids[] = $friend['friend_id'];
    }

    // Convert array to comma-separated string for IN clause
    $friend_ids_str = implode(',', array_map('intval', $friend_ids));

    // Get posts from friends and current user with proper column names
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
        up.profile_picture,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) as like_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comment_count,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id AND user_id = ?) as user_liked,
        op.content as original_content,
        op.images as original_images,
        op.created_at as original_created_at,
        ou.username as original_username,
        oup.profile_picture as original_profile_picture,
        op.user_id as original_user_id
    FROM posts p
    JOIN users u ON p.user_id = u.user_id
    LEFT JOIN user_profile up ON p.user_id = up.user_id
    LEFT JOIN posts op ON p.original_post_id = op.post_id
    LEFT JOIN users ou ON op.user_id = ou.user_id
    LEFT JOIN user_profile oup ON ou.user_id = oup.user_id
    WHERE 
        (p.visibility = 'public') OR 
        (p.visibility = 'friends' AND p.user_id IN ($friend_ids_str)) OR
        (p.user_id = ?)
    ORDER BY p.created_at DESC
    LIMIT 50
";

    return $db->rawQuery($posts_query, array($user_id, $user_id));
}

// Function to get user's basic info
function getCurrentUser($db, $user_id)
{
    $user_query = "
        SELECT u.user_id, u.username, u.email, up.profile_picture, up.first_name, up.last_name
        FROM users u
        LEFT JOIN user_profile up ON u.user_id = up.user_id
        WHERE u.user_id = ?
    ";
    return $db->rawQuery($user_query, array($user_id))[0] ?? null;
}

// Function to get comments for a post
function getPostComments($db, $post_id)
{
    $comments_query = "
        SELECT 
            c.comment_id,
            c.content,
            c.created_at,
            u.username,
            up.profile_picture
        FROM comments c
        JOIN users u ON c.user_id = u.user_id
        LEFT JOIN user_profile up ON c.user_id = up.user_id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC
        LIMIT 10
    ";
    return $db->rawQuery($comments_query, array($post_id));
}

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

// Get current user info
$current_user = getCurrentUser($db, $current_user_id);

// Get posts for feed
$posts = getFeedPosts($db, $current_user_id);
// Handle AJAX requests for likes and comments
include_once 'includes/header1.php';

?>

<div class="container mt-4">

    <!-- Main Content -->
    <div class="container-fluid mt-3">
        <div class="row" id="mainContent">

            <!-- Left Sidebar -->
            <div class="col-lg-3">
                <div class="sidebar fade-in">
                    <div class="text-center mb-4">
                        <img src="<?= htmlspecialchars($current_user['profile_picture']) ?>"
                            class="profile-pic-lg mb-3"
                            alt="<?= htmlspecialchars($current_user['username']) ?>">
                        <h5> <a href="user-profile.php?user_id=<?= htmlspecialchars($current_user['user_id']); ?>" style="text-decoration: none;"><?= htmlspecialchars($current_user['username']) ?> </a></h5>
                        <p class="text-muted"><?= htmlspecialchars($current_user['bio'] ?? 'No bio yet') ?></p>
                        <div class="d-flex justify-content-around">
                            <div class="text-center">
                                <div class="fw-bold"><?= $friend_count ?></div>
                                <small class="text-muted">Friends</small>
                            </div>
                            <div class="text-center">
                                <div class="fw-bold"><?= $post_count ?></div>
                                <small class="text-muted">Posts</small>
                            </div>
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="timeline.php" class="list-group-item active" onclick="socialTalk.showFeed()" aria-current="true">
                            <i class="fas fa-home me-2"></i>News Feed
                        </a>
                        <a href="friend.php" class="list-group-item" onclick="socialTalk.showFriends()">
                            <i class="fas fa-users me-2"></i>Friends
                        </a>
                        <a href="messages.php" class="list-group-item" onclick="socialTalk.showMessages()">
                            <i class="fas fa-envelope me-2"></i>Messages
                            <?php if ($unread_message_count > 0): ?>
                                <span class="badge bg-primary text-dark"><?= $unread_message_count ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="friend-request.php" class="list-group-item" onclick="socialTalk.showFriendRequests()">
                            <i class="fas fa-user-plus me-2"></i>Friend Requests
                            <?php if ($pending_request_count > 0): ?>
                                <span class="badge bg-success"><?= $pending_request_count ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Feed -->
            <div class="col-lg-6">
                <!-- Create Post -->
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" id="createPostForm" name="createPostForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create_post">


                    <div class="post-card p-4" id="feedSection">
                        <!-- Privacy Dropdown (Custom Implementation) -->
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
                            <img src="<?= $current_user['profile_picture'] ?>" class="profile-pic me-3">
                            <!-- <input type="text" class="form-control" placeholder="What's on your mind, John?" onclick="openCreatePost()"> -->
                            <textarea name="postContent" id="" class="form-control" placeholder="What's on your mind, <?= $user['username'] ?>?" required minlength="2"></textarea>
                        </div>


                        <div class="d-flex justify-content-between">
                            <input type="file" name="media[]" id="media" class="d-none" multiple>
                            <div id="selectedImages"></div>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <label for="media" class="btn btn-light flex-fill me-2">
                                <i class="fas fa-image me-2"></i>Add Image
                            </label>
                            <button class="btn btn-light flex-fill me-2" type="submit" name="createPost" id="createPost">
                                <i class="fas fa-edit me-2"></i>Create Post
                            </button>
                        </div>
                    </div>
                </form>
                <!-- Create Post end -->

                <!-- Posts Feed -->
                <div id="postsContainer">
                    <!--  -->
                    <!--  -->
                    <!--  -->
                    <!-- Posts Feed -->
                    <?php if (empty($posts)): ?>
                        <div class="card post-card">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5>No posts to show</h5>
                                <p class="text-muted">Start following friends or create your first post!</p>
                                <a href="find_friends.php" class="btn btn-primary">Find Friends</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="card post-card" data-post-id="<?php echo $post['post_id']; ?>">
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
                    <!--  -->
                    <!--  -->
                    <!--  -->
                </div>
            </div>


            <!-- Right Sidebar -->
            <div class="col-lg-3">
                <div class="sidebar">

                    <h6 class="mb-3">Friend Suggestions</h6>
                    <?php if (empty($friend_suggestions)): ?>
                        <div class="mb-3 text-muted">
                            <p>No friend suggestions available.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($friend_suggestions as $suggestion): ?>
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                <a href="user-profile.php?user_id=<?= htmlspecialchars($suggestion['user_id']); ?>" style="text-decoration: none;">
                                <img src="<?= htmlspecialchars($suggestion['profile_picture']) ?>"
                                        class="profile-pic me-2"
                                        style="width: 40px; height: 40px;"
                                        alt="<?= htmlspecialchars($suggestion['full_name']) ?>"> </a>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0" style="font-size: 0.9em;">
                                        <a href="user-profile.php?user_id=<?= htmlspecialchars($suggestion['user_id']); ?>" style="text-decoration: none;"> <?= htmlspecialchars($suggestion['full_name'] ?: $suggestion['username']) ?></a>
                                        </h6>
                                        <small class="text-muted"><?= $suggestion['mutual_count'] ?> mutual friend<?= $suggestion['mutual_count'] != 1 ? 's' : '' ?></small>
                                    </div>
                                    <button class="btn btn-primary btn-sm"
                                        onclick="socialTalk.sendFriendRequest(this, <?= $suggestion['user_id'] ?>)"
                                        aria-label="Add <?= htmlspecialchars($suggestion['full_name']) ?> as friend">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="sidebar fade-in">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Upcoming Events</h5>
                        <a href="#" class="text-decoration-none">See All</a>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded p-2 me-3 text-center" style="width: 40px;">
                                    <div class="fw-bold">15</div>
                                    <small>JUN</small>
                                </div>
                                <div>
                                    <h6 class="mb-0">Tech Conference</h6>
                                    <small class="text-muted">San Francisco</small>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item">
                            <div class="d-flex align-items-center">
                                <div class="bg-success text-white rounded p-2 me-3 text-center" style="width: 40px;">
                                    <div class="fw-bold">22</div>
                                    <small>JUN</small>
                                </div>
                                <div>
                                    <h6 class="mb-0">Birthday Party</h6>
                                    <small class="text-muted">Sarah's House</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="sidebar fade-in">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Suggested Groups</h5>
                        <a href="#" class="text-decoration-none">See All</a>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item">
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/40" class="rounded me-3" width="40" alt="Web Developers group">
                                <div>
                                    <h6 class="mb-0">Web Developers</h6>
                                    <small class="text-muted">245K members</small>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item">
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/40" class="rounded me-3" width="40" alt="Photography Enthusiasts group">
                                <div>
                                    <h6 class="mb-0">Photography Enthusiasts</h6>
                                    <small class="text-muted">189K members</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // In your socialTalk object
        function sendFriendRequest(buttonElement, userId) {
            const btn = $(buttonElement);
            btn.prop('disabled', true);

            $.post('ajax/send_friend_request.php', {
                user_id: userId
            }, function(response) {
                if (response.success) {
                    btn.html('<i class="fas fa-check"></i> Sent');
                    btn.removeClass('btn-primary').addClass('btn-success');
                } else {
                    btn.prop('disabled', false);
                    alert(response.message || 'Request failed');
                }
            }).fail(function() {
                btn.prop('disabled', false);
                alert('Network error');
            });
        }
    </script>

    <?php
    include_once 'includes/footer1.php';
    ?>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Report Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reportForm">
                    <input type="hidden" id="reportPostId" name="post_id">
                    <div class="mb-3">
                        <label for="reportReason" class="form-label">Reason for reporting:</label>
                        <textarea class="form-control" id="reportReason" name="reason" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Submit Report</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        if (window.reportScriptLoaded) {
            return;
        }
        window.reportScriptLoaded = true;

        document.addEventListener('DOMContentLoaded', function() {
            var reportModal = document.getElementById('reportModal');
            reportModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var postId = button.getAttribute('data-post-id');
                var modalPostIdInput = reportModal.querySelector('#reportPostId');
                modalPostIdInput.value = postId;
            });

            document.getElementById('reportForm').addEventListener('submit', function(event) {
                event.preventDefault();
                var postId = document.getElementById('reportPostId').value;
                var reason = document.getElementById('reportReason').value;

                fetch('apis/report_post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'post_id=' + encodeURIComponent(postId) + '&reason=' + encodeURIComponent(reason)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Post reported successfully!');
                        var modal = bootstrap.Modal.getInstance(reportModal);
                        modal.hide();
                    } else {
                        alert('Error reporting post: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while reporting the post.');
                });
            });
        });
    })();
</script>