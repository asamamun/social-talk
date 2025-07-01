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
    if($db->insert('posts', $data)){
        $_SESSION['message'] = "Post created successfully"; 
        header("Location: user-profile.php" );
        exit;
    }

}


/* 
create post script end
*/

include_once 'includes/header1.php';


?>
       <!-- Create Post Modal -->
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
  

<?php
include_once 'includes/footer1.php';
?>