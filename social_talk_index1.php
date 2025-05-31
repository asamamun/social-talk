<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';
$db = new MysqliDb();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];


// Function to get posts for the current user's feed
function getFeedPosts($db, $user_id) {
    // Get user's friends
    $friends_query = "
        SELECT user1_id 
        FROM friendships 
        WHERE user2_id = ? AND status = 'accepted'
        UNION
        SELECT user2_id as friend_id
        FROM friendships 
        WHERE user1_id = ? AND status = 'accepted'
    ";
    
    $friends = $db->rawQuery($friends_query, array($user_id, $user_id));
    $friend_ids = array($user_id); // Include current user's posts
    
    foreach ($friends as $friend) {
        $friend_ids[] = $friend['friend_id'];
    }
    
    // Convert array to comma-separated string for IN clause
    $friend_ids_str = implode(',', array_map('intval', $friend_ids));
    
    // Get posts from friends and current user
    // Posts should be either:
    // 1. Public posts from anyone
    // 2. Friends-only posts from friends
    // 3. Current user's own posts (all visibility levels)
    $posts_query = "
        SELECT 
            p.id,
            p.user_id,
            p.content,
            p.visibility,
            p.image_url,
            p.created_at,
            u.username,
            u.profile_picture,
            (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as like_count,
            (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comment_count,
            (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = ?) as user_liked
        FROM posts p
        JOIN users u ON p.user_id = u.id
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
    $db->where('id', $user_id);
    return $db->getOne('users', 'id, username, profile_picture, email');
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
            $db->where('post_id', $post_id);
            $db->where('user_id', $current_user_id);
            $existing_like = $db->getOne('post_likes');
            
            if ($existing_like) {
                // Unlike
                $db->where('post_id', $post_id);
                $db->where('user_id', $current_user_id);
                $db->delete('post_likes');
                echo json_encode(['status' => 'unliked']);
            } else {
                // Like
                $like_data = array(
                    'post_id' => $post_id,
                    'user_id' => $current_user_id,
                    'created_at' => date('Y-m-d H:i:s')
                );
                $db->insert('post_likes', $like_data);
                echo json_encode(['status' => 'liked']);
            }
            exit();
            
        case 'add_comment':
            $post_id = intval($_POST['post_id']);
            $comment = trim($_POST['comment']);
            
            if (!empty($comment)) {
                $comment_data = array(
                    'post_id' => $post_id,
                    'user_id' => $current_user_id,
                    'content' => $comment,
                    'created_at' => date('Y-m-d H:i:s')
                );
                $db->insert('post_comments', $comment_data);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Comment cannot be empty']);
            }
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
        .like-btn, .comment-btn {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            transition: color 0.3s;
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
                    <?php echo htmlspecialchars($current_user['username']); ?>
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
                    <div class="card post-card" data-post-id="<?php echo $post['id']; ?>">
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
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>

                            <!-- Post Content -->
                            <p class="mb-3"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                            
                            <!-- Post Image -->
                            <?php if (!empty($post['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" 
                                     alt="Post image" class="post-image">
                            <?php endif; ?>

                            <!-- Post Actions -->
                            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                <div class="d-flex align-items-center">
                                    <button class="like-btn me-3 <?php echo $post['user_liked'] ? 'liked' : ''; ?>" 
                                            onclick="toggleLike(<?php echo $post['id']; ?>)">
                                        <i class="fas fa-heart me-1"></i>
                                        <span class="like-count"><?php echo $post['like_count']; ?></span>
                                    </button>
                                    <button class="comment-btn me-3" onclick="toggleComments(<?php echo $post['id']; ?>)">
                                        <i class="fas fa-comment me-1"></i>
                                        <?php echo $post['comment_count']; ?>
                                    </button>
                                </div>
                                <small class="text-muted">
                                    <?php echo $post['like_count']; ?> likes · <?php echo $post['comment_count']; ?> comments
                                </small>
                            </div>

                            <!-- Comment Section -->
                            <div class="comment-section mt-3" id="comments-<?php echo $post['id']; ?>" style="display: none;">
                                <div class="border-top pt-3">
                                    <div class="d-flex mb-3">
                                        <img src="<?php echo htmlspecialchars($current_user['profile_picture'] ?: 'assets/default-avatar.png'); ?>" 
                                             alt="Your Profile" class="profile-img me-2">
                                        <div class="flex-grow-1">
                                            <input type="text" class="form-control form-control-sm" 
                                                   placeholder="Write a comment..." 
                                                   onkeypress="handleCommentSubmit(event, <?php echo $post['id']; ?>)">
                                        </div>
                                    </div>
                                    <div class="comments-list" id="comments-list-<?php echo $post['id']; ?>">
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

            <!-- Online Friends (if you have this feature) -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Online Friends</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Feature coming soon...</p>
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
            likeCount.textContent = parseInt(likeCount.textContent) - 1;
        }
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
            const commentBtn = document.querySelector(`[data-post-id="${postId}"] .comment-btn`);
            const currentCount = parseInt(commentBtn.textContent.trim());
            commentBtn.innerHTML = `<i class="fas fa-comment me-1"></i> ${currentCount + 1}`;
        }
    })
    .catch(error => console.error('Error:', error));
}

function loadComments(postId) {
    // This would require another endpoint to load comments
    // For now, we'll just show a placeholder
    const commentsList = document.getElementById(`comments-list-${postId}`);
    if (commentsList.children.length === 0) {
        commentsList.innerHTML = '<p class="text-muted small">Comments will be loaded here...</p>';
    }
}
</script>

</body>
</html>