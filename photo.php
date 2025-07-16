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

// Determine which user profile to show
$viewing_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $_SESSION['user_id'];
$is_own_profile = ($viewing_user_id === $_SESSION['user_id']);

// Get current logged-in user data
$db->where("user_id", $_SESSION['user_id']);
$current_user = $db->getOne("users");

// Get current user profile data
$db->where("user_id", $_SESSION['user_id']);
$current_user_profile = $db->getOne("user_profile");

// Merge current user data with profile data
$current_user = array_merge($current_user, $current_user_profile ?: []);

// Set default profile picture for current user
if (empty($current_user['profile_picture'])) {
    $current_user['profile_picture'] = 'assets/default-avatar.png';
}

// Get the profile user data (the user whose profile we're viewing)
if ($is_own_profile) {
    $profile_user = $current_user;
} else {
    $db->join("user_profile up", "up.user_id = u.user_id", "LEFT");
    $db->where("u.user_id", $viewing_user_id);
    $profile_user = $db->getOne("users u");
    
    if (!$profile_user) {
        header("Location: error.php?message=User+not+found");
        exit;
    }
    
    // Set default images for target user
    if (empty($profile_user['profile_picture'])) {
        $profile_user['profile_picture'] = 'assets/default-avatar.png';
    }
}

// Fetch user's photos from posts table
$db->where("user_id", $viewing_user_id);
$db->where("images IS NOT NULL");
$db->where("images != ''");
$photos = $db->get("posts");

include_once 'includes/header1.php';
?>

<!-- Photos Section -->
<div class="container mt-4">
    <div class="photo-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><a href="user-profile.php?user_id=<?= htmlspecialchars($profile_user['user_id']); ?>" style="text-decoration: none;"><?= htmlspecialchars($profile_user['username']) ?></a>'s Photos</h4>
            
            <div>
                <?php if ($is_own_profile): ?>
                <a href="add-post.php" class="btn btn-primary me-2">
                    <i class="fas fa-plus me-2"></i>Upload Photo
                </a>
                <?php endif; ?>
                <a href="user-profile.php?user_id=<?= $viewing_user_id ?>" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                </a>
            </div>
        </div>

        <!-- Photo Grid -->
        <div class="photo-grid">
            <?php if ($photos): ?>
                <?php foreach ($photos as $photo): ?>
                    <?php
                    // Split the CSV string into an array of image filenames
                    $imageArray = array_filter(explode(',', $photo['images']));
                    foreach ($imageArray as $image):
                        // Construct the correct path using the viewing user's ID
                        $imagePath = "assets/contentimages/{$viewing_user_id}/" . htmlspecialchars(trim($image));
                    ?>
                         <a href="assets/contentimages/<?php echo $viewing_user_id; ?>/<?= htmlspecialchars(trim($image)); ?>" data-lightbox="post-images-<?php echo $photo['post_id']; ?>"><img src="<?php echo $imagePath; ?>" alt="Photo" class="img-fluid rounded"></a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No photos uploaded yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once 'includes/footer1.php'; ?>