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
    if (empty($content) || strlen($content) < 30) {
        $_SESSION['error'] = "Post content must be at least 30 characters long.";
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
            u.username,
            up.profile_picture,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) as like_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comment_count,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id AND user_id = ?) as user_liked
        FROM posts p
        JOIN users u ON p.user_id = u.user_id
        LEFT JOIN user_profile up ON p.user_id = up.user_id
        WHERE 
            (p.visibility = 'public') OR 
            (p.visibility = 'friends' AND p.user_id IN ($friend_ids_str)) OR
            (p.user_id = ?)
        ORDER BY p.created_at DESC
        LIMIT 50
    ";

    // return $db->rawQuery($posts_query, array($user_id, $user_id));
    return $db->rawQuery($posts_query, array($user_id, $user_id, $user_id));


}
/* 
create post script end
*/
include_once 'includes/header1.php';

?>


<div class="container mt-4">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-header-content">
            <img src="<?= $current_user['profile_picture'] ?>" class="profile-pic-xl" alt="<?= $user['username'] ?>">
            <div class="profile-info">
                <h1 class="profile-name"><?= $user['username'] ?></h1>
                <!-- You can add additional profile info here if needed -->
            </div>
        </div>
        <div class="profile-actions">
            <a href="edit-about.php" class="btn btn-primary">
                <i class="fas fa-pencil-alt me-2"></i>Edit Profile
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-4">
            <!-- About Card -->
            <div class="profile-card">
                <h4 class="mb-4">About</h4>
                <div class="info-item d-flex">
                    <i class="fas fa-briefcase mt-1"></i>
                    <div>
                        <h6 class="mb-0">Works at</h6>
                        <p class="text-muted">Tech Solutions Inc.</p>
                    </div>
                </div>
                <div class="info-item d-flex">

                    <div>
                        <h6 class="mb-0">Studied at</h6>
                        <p class="text-muted">Stanford University</p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-home mt-1"></i>
                    <div>
                        <h6 class="mb-0">Lives in</h6>
                        <p class="text-muted">San Francisco, CA</p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-map-marker-alt mt-1"></i>
                    <div>
                        <h6 class="mb-0">From</h6>
                        <p class="text-muted">New York, NY</p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-heart mt-1"></i>
                    <div>
                        <h6 class="mb-0">Relationship</h6>
                        <p class="text-muted">In a relationship</p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-calendar-alt mt-1"></i>
                    <div>
                        <h6 class="mb-0">Joined</h6>
                        <p class="text-muted">January 2018</p>
                    </div>
                </div>

                <a href="edit-about.php" class="btn btn-primary btn-sm px-3 py-2" style="font-size: 0.9rem;">
                    <i class="fas fa-edit"></i> Edit About
                </a>
                </h4>
            </div>

            <!-- Photos Card -->
            <div class="profile-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Photos</h4>
                    <a href="photo.php" class="text-primary" data-section="photos">See All</a>
                </div>

                <div class="photo-grid">
                    <?php
                    // Fetch photos from posts table
                    $db->where("user_id", $_SESSION['user_id']);
                    $db->where("images IS NOT NULL");
                    $posts = $db->get("posts");

                    $allImages = [];
                    foreach ($posts as $post) {
                        $imgs = explode(',', $post['images']);
                        foreach ($imgs as $img) {
                            $trimmed = trim($img);
                            if (!empty($trimmed)) {
                                $allImages[] = $trimmed;
                            }
                        }
                    }

                    // Show only first 6 images
                    $displayImages = array_slice($allImages, 0, 6);

                    if (!empty($displayImages)):
                        foreach ($displayImages as $img): ?>
                            <img src="assets/contentimages/<?= $_SESSION['user_id']; ?>/<?= htmlspecialchars($img); ?>" alt="Photo">
                    <?php
                        endforeach;
                    else:
                        echo '<p>No photos uploaded yet.</p>';
                    endif;
                    ?>
                </div>
            </div>

            <!-- Friends Card -->
            <div class="profile-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Friends</h4>
                    <a href="friend.php" class="text-primary" data-section="friends">See All</a>
                </div>
                <p class="text-muted mb-4">245 friends</p>
                <div class="row">
                    <div class="col-6 friend-card">
                        <img src="https://images.unsplash.com/photo-1494790108755-2616b612b820?w=100&h=100&fit=crop&crop=face" alt="Sarah Johnson">
                        <h6>Sarah Johnson</h6>
                        <!-- <div class="online-status"></div> -->
                    </div>
                    <div class="col-6 friend-card">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face" alt="Mike Chen">
                        <h6>Mike Chen</h6>
                    </div>
                    <div class="col-6 friend-card">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop&crop=face" alt="Emma Wilson">
                        <h6>Emma Wilson</h6>
                        <!-- <div class="online-status"></div> -->
                    </div>
                    <div class="col-6 friend-card">
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop&crop=face" alt="Alex Rodriguez">
                        <h6>Alex Rodriguez</h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-8">
            <!-- Profile Navigation -->
            <ul class="nav profile-nav mb-4">
                <li class="nav-item">
                    <a class="nav-link active" href="#posts" data-section="posts">Posts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="user-profile-about.php" data-section="about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="friend.php" data-section="friends">Friends</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="photo.php" data-section="photos">Photos</a>
                </li>

            </ul>

            <!-- Dynamic Content Area -->
            <div id="content-area">
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
                            <textarea name="postContent" id="" class="form-control" placeholder="What's on your mind, <?= $user['username'] ?>?" required minlength="30"></textarea>
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

                <div class="container mt-4">
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
                            <div class="post-card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="<?= $current_user['profile_picture'] ?>" class="profile-img mb-3" alt="<?= $user['username'] ?>">

                                        <div>
                                            <h6 class="mb-0"><?= $user['username'] ?></h6>
                                            <small class="text-muted"><?= timeAgo($post['created_at']); ?></small>
                                        </div>
                                        <div class="ms-auto dropdown">
                                            <button class="btn btn-sm" data-bs-toggle="dropdown" aria-label="Post options">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="editPost(this)">Edit</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="deletePost(this)">Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <p><?= nl2br(htmlspecialchars($post['content'])); ?></p>

                                    <?php if (!empty($post['images'])):
                                        $images = explode(',', $post['images']);
                                    ?>
                                        <?php foreach ($images as $img): ?>
                                            <img src="assets/contentimages/<?= $post['user_id']; ?>/<?= htmlspecialchars(trim($img)); ?>" class="img-fluid rounded mb-3" alt="Post image">
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <div>
                                        <span class="text-muted">
                                            <?= $post['like_count']; ?> likes • <?= $post['comment_count']; ?> comments
                                        </span>
                                    </div>

                                    <!-- Post Actions -->
                                    <div class="d-flex justify-content-between">
                                        <!-- Like -->
                                        <button class="btn btn-light flex-fill me-2 <?= $post['user_liked'] ? 'text-danger' : ''; ?>"
                                            onclick="toggleLike(<?= $post['post_id']; ?>)">
                                            <i class="fas fa-heart me-1"></i>
                                            <span class="like-text">Like</span>
                                            <span class="like-count">(<?= $post['like_count']; ?>)</span>
                                        </button>

                                        <!-- Comment -->
                                        <button class="btn btn-light flex-fill me-2" onclick="toggleComments(<?= $post['post_id']; ?>)">
                                            <i class="fas fa-comment me-1"></i>
                                            <span class="comment-text">Comment</span>
                                            <span class="comment-count">(<?= $post['comment_count']; ?>)</span>
                                        </button>

                                        <!-- Share -->
                                        <button class="btn btn-light flex-fill" onclick="sharePost(<?= $post['post_id']; ?>)">
                                            <i class="fas fa-share me-1"></i>Share
                                        </button>
                                    </div>

                                    <!-- Comment Section -->
                                    <div class="comment-section mt-3" id="comments-<?php echo $post['post_id']; ?>" style="display: none;">
                                        <div class="border-top pt-3">
                                            <div class="d-flex mb-3">
                                                <img src="<?php echo htmlspecialchars($current_user['profile_picture'] ?: 'assets/default-avatar.png'); ?>"
                                                    alt="Your Profile" class="profile-img me-2">
                                                <div class="flex-grow-1">
                                                    <input type="text" class="form-control form-control-sm comment-input"
                                                        placeholder="Write a comment..."
                                                        onkeypress="handleCommentSubmit(event, <?php echo $post['post_id']; ?>)">
                                                </div>
                                            </div>
                                            <div class="comments-list" id="comments-list-<?php echo $post['post_id']; ?>">
                                                <!-- Comments will be loaded here -->
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




<?php
include_once 'includes/footer1.php';
?>