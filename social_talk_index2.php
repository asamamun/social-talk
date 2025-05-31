<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];

// Initialize database connection
$db = new MysqliDb();
// Get user's basic info
$db->where ("user_id", $_SESSION['user_id']);
$user = $db->getOne ("users");

// Function to get posts for the current user's feed
function getFeedPosts($db, $user_id) {
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
    
    return $db->rawQuery($posts_query, array($user_id, $user_id));
}

// Function to get user's basic info
function getCurrentUser($db, $user_id) {
    $user_query = "
        SELECT u.user_id, u.username, u.email, up.profile_picture, up.first_name, up.last_name
        FROM users u
        LEFT JOIN user_profile up ON u.user_id = up.user_id
        WHERE u.user_id = ?
    ";
    return $db->rawQuery($user_query, array($user_id))[0] ?? null;
}

// Function to get comments for a post
function getPostComments($db, $post_id) {
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
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    return floor($time/31536000) . ' years ago';
}

// Get current user info
$current_user = getCurrentUser($db, $current_user_id);

// Get posts for feed
$posts = getFeedPosts($db, $current_user_id);

// Handle AJAX requests for likes and comments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'like_post':
            $post_id = intval($_POST['post_id']);
            
            // Check if already liked
            $existing_like_query = "SELECT like_id FROM likes WHERE post_id = ? AND user_id = ?";
            $existing_like = $db->rawQuery($existing_like_query, array($post_id, $current_user_id));
            
            if ($existing_like) {
                // Unlike
                $unlike_query = "DELETE FROM likes WHERE post_id = ? AND user_id = ?";
                $db->rawQuery($unlike_query, array($post_id, $current_user_id));
                echo json_encode(['status' => 'unliked']);
            } else {
                // Like
                $like_query = "INSERT INTO likes (post_id, user_id, created_at) VALUES (?, ?, NOW())";
                $db->rawQuery($like_query, array($post_id, $current_user_id));
                echo json_encode(['status' => 'liked']);
            }
            exit();
            
        case 'add_comment':
            $post_id = intval($_POST['post_id']);
            $comment = trim($_POST['comment']);
            
            if (!empty($comment)) {
                $comment_query = "INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())";
                $db->rawQuery($comment_query, array($post_id, $current_user_id, $comment));
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Comment cannot be empty']);
            }
            exit();
            
        case 'load_comments':
            $post_id = intval($_POST['post_id']);
            $comments = getPostComments($db, $post_id);
            echo json_encode(['status' => 'success', 'comments' => $comments]);
            exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Talk - Home</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .post-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .post-image {
            max-width: 100%;
            border-radius: 8px;
            margin: 10px 0;
        }
        .post-images {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 10px 0;
        }
        .post-images img {
            max-width: calc(50% - 5px);
            border-radius: 8px;
            object-fit: cover;
        }
        .like-btn, .comment-btn {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            transition: color 0.3s;
            padding: 5px 10px;
        }
        .like-btn.liked {
            color: #e74c3c;
        }
        .like-btn:hover {
            color: #e74c3c;
        }
        .comment-btn:hover {
            color: #3498db;
        }
        .navbar-brand {
            font-weight: bold;
            color: #2c3e50 !important;
        }
        .comment-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 8px;
        }
        .comment-item .profile-img {
            width: 24px;
            height: 24px;
        }
    </style>
</head>
<body class="bg-light">

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-comments"></i> Social Talk
        </a>
        <div class="navbar-nav ms-auto">
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <img src="<?php echo htmlspecialchars($current_user['profile_picture'] ?: 'assets/default-avatar.png'); ?>" 
                         alt="Profile" class="profile-img me-2">
                    <?php 
                    $display_name = $current_user['first_name'] && $current_user['last_name'] 
                        ? $current_user['first_name'] . ' ' . $current_user['last_name'] 
                        : $current_user['username'];
                    echo htmlspecialchars($display_name); 
                    ?>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                    <li><a class="dropdown-item" href="friends.php">Friends</a></li>
                    <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Create Post Section -->
            <div class="card post-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?php echo htmlspecialchars($current_user['profile_picture'] ?: 'assets/default-avatar.png'); ?>" 
                             alt="Your Profile" class="profile-img me-3">
                        <input type="text" class="form-control" placeholder="What's on your mind?" 
                               onclick="window.location.href='create_post.php'">
                    </div>
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-outline-primary btn-sm" onclick="window.location.href='create_post.php'">
                            <i class="fas fa-edit"></i> Create Post
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="window.location.href='create_post.php'">
                            <i class="fas fa-image"></i> Photo
                        </button>
                    </div>
                </div>
            </div>

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
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?php echo htmlspecialchars($post['profile_picture'] ?: 'assets/default-avatar.png'); ?>" 
                                     alt="Profile" class="profile-img me-3">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($post['username']); ?></h6>
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

                            <!-- Post Content -->
                            <p class="mb-3"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                            
                            <!-- Post Images -->
                            <?php if (!empty($post['images'])): ?>
                                <?php 
                                $images = explode(',', $post['images']);
                                $image_count = count($images);
                                ?>
                                <div class="post-images">
                                    <?php foreach ($images as $index => $image): ?>
                                        <?php if ($index < 4): // Show max 4 images ?>
                                            <img src="uploads/<?php echo htmlspecialchars(trim($image)); ?>" 
                                                 alt="Post image" class="post-image"
                                                 style="<?php echo $image_count === 1 ? 'max-width: 100%;' : ''; ?>">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php if ($image_count > 4): ?>
                                        <div class="more-images-overlay">
                                            <span>+<?php echo $image_count - 4; ?> more</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Post Actions -->
                            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                <div class="d-flex align-items-center">
                                    <button class="like-btn me-3 <?php echo $post['user_liked'] ? 'liked' : ''; ?>" 
                                            onclick="toggleLike(<?php echo $post['post_id']; ?>)">
                                        <i class="fas fa-heart me-1"></i>
                                        <span class="like-count"><?php echo $post['like_count']; ?></span>
                                    </button>
                                    <button class="comment-btn me-3" onclick="toggleComments(<?php echo $post['post_id']; ?>)">
                                        <i class="fas fa-comment me-1"></i>
                                        <span class="comment-count"><?php echo $post['comment_count']; ?></span>
                                    </button>
                                </div>
                                <small class="text-muted">
                                    <?php echo $post['like_count']; ?> likes · <?php echo $post['comment_count']; ?> comments
                                </small>
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

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="create_post.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create Post
                        </a>
                        <a href="friends.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-users"></i> Find Friends
                        </a>
                        <a href="messages.php" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-envelope"></i> Messages
                        </a>
                    </div>
                </div>
            </div>

            <!-- Friend Suggestions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">People You May Know</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Friend suggestions coming soon...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
function toggleLike(postId) {
    fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=like_post&post_id=${postId}`
    })
    .then(response => response.json())
    .then(data => {
        const likeBtn = document.querySelector(`[data-post-id="${postId}"] .like-btn`);
        const likeCount = likeBtn.querySelector('.like-count');
        
        if (data.status === 'liked') {
            likeBtn.classList.add('liked');
            likeCount.textContent = parseInt(likeCount.textContent) + 1;
        } else {
            likeBtn.classList.remove('liked');
            likeCount.textContent = Math.max(0, parseInt(likeCount.textContent) - 1);
        }
        
        // Update the summary text
        const summaryText = document.querySelector(`[data-post-id="${postId}"] .text-muted`);
        const newLikeCount = likeCount.textContent;
        const commentCount = document.querySelector(`[data-post-id="${postId}"] .comment-count`).textContent;
        summaryText.textContent = `${newLikeCount} likes · ${commentCount} comments`;
    })
    .catch(error => console.error('Error:', error));
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
    fetch('index.php', {
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
            // Update comment count
            const commentCount = document.querySelector(`[data-post-id="${postId}"] .comment-count`);
            const currentCount = parseInt(commentCount.textContent);
            commentCount.textContent = currentCount + 1;
            
            // Update summary text
            const summaryText = document.querySelector(`[data-post-id="${postId}"] .text-muted`);
            const likeCount = document.querySelector(`[data-post-id="${postId}"] .like-count`).textContent;
            summaryText.textContent = `${likeCount} likes · ${currentCount + 1} comments`;
        }
    })
    .catch(error => console.error('Error:', error));
}

function loadComments(postId) {
    fetch('index.php', {
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
</script>

</body>
</html>