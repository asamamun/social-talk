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

// Fetch user's photos from posts table
$db->where("user_id", $_SESSION['user_id']);
$db->where("images IS NOT NULL");
$photos = $db->get("posts");

 include_once 'includes/header1.php';
  ?>

<!-- Photos Section -->
<div class="container mt-4">
    <div class="photo-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><?php echo htmlspecialchars($user['username']); ?>'s Photos</h4>
            <div>
                <a href="add-post.php" class="btn btn-primary me-2">
    <i class="fas fa-plus me-2"></i>Upload Photo
</a>
                <a href="user-profile.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                </a>
            </div>
        </div>

        <!-- Photo Grid -->
        <div class="photo-grid">
            <?php if ($photos): ?>
                <?php foreach ($photos as $photo): ?>
                 
                    <img src="assets/contentimages/<?php echo $user['user_id']; ?>/<?php echo htmlspecialchars($photo['images']); ?>" alt="Photo" >

                <?php endforeach; ?>
            <?php else: ?>
                <p>No photos uploaded yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>



<?php include_once 'includes/footer1.php'; ?>