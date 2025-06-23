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

/* 
create post script
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_post') {

    // 1. Retrieve user ID
    $userId = $_SESSION['user_id'];

    // 2. Retrieve and validate post content
    $content = trim($_POST['postContent'] ?? '');
    $visibility = $_POST['privacy'] ?? 'public';
    $allowed_visibilities = ['public', 'friends', 'private'];

    // Validate inputs
    if (empty($content) || strlen($content) < 5) {
        $_SESSION['error'] = "Post content must be at least 5 characters long.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    if (!in_array($visibility, $allowed_visibilities)) {
        $_SESSION['error'] = "Invalid visibility setting.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // 3. Prepare upload path
    $uploadDir = "assets/contentimages/$userId/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // 4. Handle image uploads
    $uploadedImages = [];
    if (!empty($_FILES['media']['name'][0])) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        foreach ($_FILES['media']['name'] as $key => $name) {
            $tmpName = $_FILES['media']['tmp_name'][$key];
            $error = $_FILES['media']['error'][$key];
            $size = $_FILES['media']['size'][$key];

            if ($error === UPLOAD_ERR_OK) {
                // Validate file size
                if ($size > $maxFileSize) {
                    $_SESSION['error'] = "File size too large. Maximum 5MB allowed.";
                    continue;
                }

                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                // Validate file extension
                if (!in_array($ext, $allowedExtensions)) {
                    $_SESSION['error'] = "Invalid file type. Only JPG, PNG, GIF, WEBP allowed.";
                    continue;
                }

                // Validate file is actually an image
                $imageInfo = getimagesize($tmpName);
                if ($imageInfo === false) {
                    $_SESSION['error'] = "Invalid image file.";
                    continue;
                }

                $randomName = uniqid('img_', true) . '.' . $ext;
                $targetFile = $uploadDir . $randomName;

                if (move_uploaded_file($tmpName, $targetFile)) {
                    $uploadedImages[] = $randomName;
                }
            }
        }
    }

    // 5. Convert image names to CSV
    $imagesCSV = implode(',', $uploadedImages);

    // 6. Insert post into database
    $data = [
        'user_id' => $userId,
        'content' => $content,
        'images' => $imagesCSV,
        'visibility' => $visibility,
        'created_at' => date('Y-m-d H:i:s')
    ];

    if ($db->insert('posts', $data)) {
        $_SESSION['message'] = "Post created successfully";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $_SESSION['error'] = "Failed to create post. Please try again.";
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

// Function to get ONLY the current user's posts
function getUserPosts($db, $user_id)
{
    // Get posts from only the current user with proper column names
    $posts_query = "
        SELECT 
            p.post_id,
            p.user_id,
            p.content,
            p.visibility,
            p.images,
            p.created_at,
            u.username,
            COALESCE(up.profile_picture, 'assets/default-avatar.png') as profile_picture,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) as like_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comment_count,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id AND user_id = ?) as user_liked
        FROM posts p
        JOIN users u ON p.user_id = u.user_id
        LEFT JOIN user_profile up ON p.user_id = up.user_id
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
        LIMIT 50
    ";

    return $db->rawQuery($posts_query, array($user_id, $user_id));
}

// Get posts for the user's profile (only their own posts)
$current_user_id = $_SESSION['user_id'];
$posts = getUserPosts($db, $current_user_id);

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
        <div class="profile-header-content" >

            <!-- Cover Photo as Image -->
          
            <img  src="<?= htmlspecialchars($current_user['cover_photo']) ?>" 
            class="cover-photo-section" alt="<?= htmlspecialchars($user['username']) ?>">
          

            <img src="<?= htmlspecialchars($current_user['profile_picture']) ?>" class="profile-pic-xl prfile-pic-md profile-pic-sm" alt="<?= htmlspecialchars($user['username']) ?>">
            <div class="profile-info">
                <h1 class="profile-name"><?= htmlspecialchars($user['username']) ?></h1>
                <!-- You can add additional profile info here if needed -->
            </div>
        </div>

        <!-- Edit Profile Button -->
        <div class="profile-actions" style="margin-top: 60px;">
            <a href="edit-about.php" class="btn btn-primary">
                <i class="fas fa-pencil-alt me-2"></i> Edit Profile
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
                        <p class="text-muted"><?= htmlspecialchars($current_user['work_place'] ?? 'Not specified') ?></p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-graduation-cap mt-1"></i>
                    <div>
                        <h6 class="mb-0">Studied at</h6>
                        <p class="text-muted"><?= htmlspecialchars($current_user['education'] ?? 'Not specified') ?></p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-home mt-1"></i>
                    <div>
                        <h6 class="mb-0">Lives in</h6>
                        <p class="text-muted"><?= htmlspecialchars($current_user['current_city'] ?? 'Not specified') ?></p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-map-marker-alt mt-1"></i>
                    <div>
                        <h6 class="mb-0">From</h6>
                        <p class="text-muted"><?= htmlspecialchars($current_user['hometown'] ?? 'Not specified') ?></p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-heart mt-1"></i>
                    <div>
                        <h6 class="mb-0">Relationship</h6>
                        <p class="text-muted"><?= htmlspecialchars($current_user['relationship_status'] ?? 'Not specified') ?></p>
                    </div>
                </div>
                <div class="info-item d-flex">
                    <i class="fas fa-calendar-alt mt-1"></i>
                    <div>
                        <h6 class="mb-0">Joined</h6>
                        <p class="text-muted"><?= date('F Y', strtotime($user['created_at'] ?? 'now')) ?></p>
                    </div>
                </div>

                <a href="edit-about.php" class="btn btn-primary btn-sm px-3 py-2" style="font-size: 0.9rem;">
                    <i class="fas fa-edit"></i> Edit About
                </a>
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

                    // Show only first 6 images
                    $displayImages = array_slice($allImages, 0, 6);

                    if (!empty($displayImages)):
                        foreach ($displayImages as $img): ?>
                            <img src="assets/contentimages/<?= $_SESSION['user_id']; ?>/<?= htmlspecialchars($img); ?>" alt="Photo" class="img-fluid rounded">
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
                    <a href="friend.php" class="text-primary" data-section="friends">See All</a>
                </div>

                <?php
                // Get actual friend count
                $friends_count_query = "
                    SELECT COUNT(*) as count 
                    FROM friendships 
                    WHERE (user1_id = ? OR user2_id = ?) AND status = 'accepted'
                ";
                $friend_count_result = $db->rawQuery($friends_count_query, array($_SESSION['user_id'], $_SESSION['user_id']));
                $friend_count = $friend_count_result[0]['count'] ?? 0;
                ?>

                <p class="text-muted mb-4"><?= $friend_count ?> friends</p>

                <?php
                // Get some friends to display
                $sample_friends_query = "
                    SELECT u.username, COALESCE(up.profile_picture, 'assets/default-avatar.png') as profile_picture
                    FROM friendships f
                    JOIN users u ON (CASE WHEN f.user1_id = ? THEN f.user2_id ELSE f.user1_id END = u.user_id)
                    LEFT JOIN user_profile up ON u.user_id = up.user_id
                    WHERE (f.user1_id = ? OR f.user2_id = ?) AND f.status = 'accepted'
                    LIMIT 4
                ";
                $sample_friends = $db->rawQuery($sample_friends_query, array($_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']));
                ?>

                <div class="row">
                    <?php if (!empty($sample_friends)): ?>
                        <?php foreach ($sample_friends as $friend): ?>
                            <div class="col-6 friend-card">
                                <img src="<?= htmlspecialchars($friend['profile_picture']) ?>" alt="<?= htmlspecialchars($friend['username']) ?>">
                                <h6><?= htmlspecialchars($friend['username']) ?></h6>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No friends yet. <a href="find_friends.php">Find friends</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-8">
            <!-- Profile Navigation -->
            <ul class="nav profile-nav mb-4">
                <li class="nav-item">
                    <a class="nav-link active" href="user-profile.php" data-section="posts">Posts</a>
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
                            <textarea name="postContent" class="form-control" placeholder="What's on your mind, <?= htmlspecialchars($user['username']) ?>?" required minlength="30" rows="3"></textarea>
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

                <!-- Posts Feed -->
                <div class="container mt-4">
                    <?php if (empty($posts)): ?>
                        <div class="card post-card">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5>No posts to show</h5>
                                <p class="text-muted">Create your first post!</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="post-card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="<?= htmlspecialchars($post['profile_picture']) ?>" class="profile-img me-3" alt="<?= htmlspecialchars($post['username']) ?>">
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($post['username']) ?></h6>
                                            <small class="text-muted"><?= timeAgo($post['created_at']); ?></small>
                                        </div>
                                        <div class="ms-auto dropdown">
                                            <button class="btn btn-sm" data-bs-toggle="dropdown" aria-label="Post options">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="editPost(<?= $post['post_id'] ?>)">Edit</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="deletePost(<?= $post['post_id'] ?>)">Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <p><?= nl2br(htmlspecialchars($post['content'])); ?></p>

                                    <?php if (!empty($post['images'])):
                                        $images = array_filter(explode(',', $post['images']));
                                        foreach ($images as $img):
                                            $img = trim($img);
                                            if (!empty($img)):
                                    ?>
                                                <img src="assets/contentimages/<?= $post['user_id']; ?>/<?= htmlspecialchars($img); ?>" class="img-fluid rounded mb-3" alt="Post image">
                                    <?php
                                            endif;
                                        endforeach;
                                    endif; ?>

                                    <div class="mb-2">
                                        <span class="text-muted">
                                            <?= $post['like_count']; ?> likes • <?= $post['comment_count']; ?> comments
                                        </span>
                                    </div>

                                    <!-- Post Actions -->
                                    <div class="d-flex justify-content-between border-top pt-2">
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
                                    <div class="comment-section mt-3" id="comments-<?= $post['post_id']; ?>" style="display: none;">
                                        <div class="border-top pt-3">
                                            <div class="d-flex mb-3">
                                                <img src="<?= htmlspecialchars($current_user['profile_picture']); ?>"
                                                    alt="Your Profile" class="profile-img me-2">
                                                <div class="flex-grow-1">
                                                    <input type="text" class="form-control form-control-sm comment-input"
                                                        placeholder="Write a comment..."
                                                        onkeypress="handleCommentSubmit(event, <?= $post['post_id']; ?>)">
                                                </div>
                                            </div>
                                            <div class="comments-list" id="comments-list-<?= $post['post_id']; ?>">
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

<script>
    // Privacy dropdown functionality
    document.addEventListener('DOMContentLoaded', function() {
        const privacyDropdown = document.getElementById('privacyDropdown');
        const selectedPrivacy = document.getElementById('selectedPrivacy');
        const dropdownItems = document.querySelectorAll('.dropdown-item[data-value]');

        dropdownItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const value = this.getAttribute('data-value');
                const text = this.textContent.trim();
                const icon = this.querySelector('i').className;

                // Update button
                privacyDropdown.innerHTML = `<i class="${icon} me-2"></i> ${text.split(' ').slice(1).join(' ')}`;

                // Update hidden input
                selectedPrivacy.value = value;

                // Update active state
                dropdownItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // File input preview
        const mediaInput = document.getElementById('media');
        const selectedImages = document.getElementById('selectedImages');

        mediaInput.addEventListener('change', function() {
            selectedImages.innerHTML = '';
            if (this.files.length > 0) {
                const fileText = this.files.length === 1 ? '1 image selected' : `${this.files.length} images selected`;
                selectedImages.innerHTML = `<small class="text-muted">${fileText}</small>`;
            }
        });
    });

    // Placeholder functions for social interactions
    function toggleLike(postId) {
        fetch('user-profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=like_post&post_id=${postId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'liked' || data.status === 'unliked') {
                    const likeBtn = document.querySelector(`button[onclick="toggleLike(${postId})"]`);
                    const likeText = likeBtn.querySelector('.like-text');
                    const likeCount = likeBtn.querySelector('.like-count');
                    const currentCount = parseInt(likeCount.textContent.replace(/[^0-9]/g, '')) || 0;

                    if (data.status === 'liked') {
                        likeBtn.classList.add('text-danger');
                        likeText.textContent = 'Liked';
                        likeCount.textContent = `(${currentCount + 1})`;
                    } else {
                        likeBtn.classList.remove('text-danger');
                        likeText.textContent = 'Like';
                        likeCount.textContent = `(${Math.max(0, currentCount - 1)})`;
                    }

                    // Update the summary text
                    const summaryText = likeBtn.closest('.post-card').querySelector('.text-muted');
                    if (summaryText) {
                        const commentCount = likeBtn.closest('.post-card').querySelector('.comment-count').textContent.replace(/[^0-9]/g, '');
                        summaryText.textContent = `${likeCount.textContent.replace(/[()]/g, '')} likes • ${commentCount} comments`;
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function toggleComments(postId) {
        const commentsSection = document.getElementById('comments-' + postId);
        if (commentsSection.style.display === 'none') {
            commentsSection.style.display = 'block';
            loadComments(postId);
        } else {
            commentsSection.style.display = 'none';
        }
    }

    function loadComments(postId) {
        fetch('user-profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=load_comments&post_id=${postId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const commentsList = document.getElementById('comments-list-' + postId);
                    commentsList.innerHTML = '';

                    data.comments.forEach(comment => {
                        const commentHtml = `
                    <div class="comment-item d-flex mb-2">
                        <img src="${comment.profile_picture || 'assets/default-avatar.png'}" 
                             alt="Profile" class="profile-img-sm me-2">
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

    function sharePost(postId) {
        fetch('user-profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=share_post&post_id=${postId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'shared') {
                    alert('Post shared successfully!');
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function editPost(postId) {
        // Implementation for edit functionality
        console.log('Edit post:', postId);
        // You would typically show a modal with the post content to edit
    }

    function deletePost(postId) {
        if (confirm('Are you sure you want to delete this post?')) {
            fetch('user-profile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_post&post_id=${postId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Remove the post from the DOM
                        document.querySelector(`.post-card[data-post-id="${postId}"]`)?.remove();
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    }

    function handleCommentSubmit(event, postId) {
        if (event.key === 'Enter') {
            const comment = event.target.value.trim();
            if (comment) {
                fetch('user-profile.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=add_comment&post_id=${postId}&comment=${encodeURIComponent(comment)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            event.target.value = '';
                            loadComments(postId);

                            // Update comment count
                            const commentCountElements = document.querySelectorAll(`.comment-count`);
                            commentCountElements.forEach(element => {
                                const currentCount = parseInt(element.textContent.replace(/[^0-9]/g, '')) || 0;
                                element.textContent = `(${currentCount + 1})`;
                            });
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }
    }

    function timeAgoJS(datetime) {
        const time = Math.floor((new Date() - new Date(datetime)) / 1000);

        if (time < 60) return 'just now';
        if (time < 3600) return Math.floor(time / 60) + ' minutes ago';
        if (time < 86400) return Math.floor(time / 3600) + ' hours ago';
        if (time < 2592000) return Math.floor(time / 86400) + ' days ago';
        if (time < 31536000) return Math.floor(time / 2592000) + ' months ago';
        return Math.floor(time / 31536000) + ' years ago';
    }
</script>

<?php
include_once 'includes/footer1.php';
?>