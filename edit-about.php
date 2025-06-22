
<?php
// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication and database setup
require __DIR__ . '/vendor/autoload.php';


// Redirect if not logged in or admin
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    header("Location: admin/");
    exit;
}

// Initialize variables
$user_id = $_SESSION['user_id'];
$db = new MysqliDb();
$message = '';
$error = '';
$defaultProfilePic = 'assets/images/profile.png';
$defaultCoverPhoto = 'assets/images/cover.jpg';

// Fetch user and profile data
$db->where("user_id", $user_id);
$user = $db->getOne("users");
if (!$user) {
    die("User not found.");
}

$db->where("user_id", $user_id);
$profile = $db->getOne("user_profile") ?: [
    'first_name' => '', 'last_name' => '', 'blood_group' => '', 'country' => '',
    'address_line1' => '', 'address_line2' => '', 'city' => '', 'state' => '',
    'postal_code' => '', 'phone_number' => '', 'bio' => '', 'profile_picture' => '',
    'cover_photo' => '', 'date_of_birth' => '', 'gender' => ''
];

$userProfilePic = $profile['profile_picture'] ?: $defaultProfilePic;
$userCoverPhoto = $profile['cover_photo'] ?: $defaultCoverPhoto;

// Calculate profile completion percentage
function calculateCompletionPercentage($profile) {
    $fields = ['first_name', 'last_name', 'profile_picture', 'cover_photo', 'date_of_birth', 
               'phone_number', 'gender', 'blood_group', 'bio', 'city', 'country'];
    $completed = count(array_filter($fields, fn($field) => !empty($profile[$field])));
    return round(($completed / count($fields)) * 100);
}
$completionPercentage = calculateCompletionPercentage($profile);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array_map('trim', [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'blood_group' => $_POST['blood_group'] ?? null,
        'country' => $_POST['country'] ?? '',
        'address_line1' => $_POST['address_line1'] ?? '',
        'address_line2' => $_POST['address_line2'] ?? '',
        'city' => $_POST['city'] ?? '',
        'state' => $_POST['state'] ?? '',
        'postal_code' => $_POST['postal_code'] ?? '',
        'phone_number' => $_POST['phone_number'] ?? '',
        'bio' => $_POST['bio'] ?? '',
        'date_of_birth' => $_POST['date_of_birth'] ?? null,
        'gender' => $_POST['gender'] ?? null
    ]);

    if (empty($data['first_name']) || empty($data['last_name'])) {
        $error = "First name and last name are required.";
    } else {
        $upload_dir = "assets/contentimages/{$user_id}/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            if (in_array($file_extension, $allowed_extensions)) {
                $filename = "profile_{$user_id}_" . time() . ".$file_extension";
                $upload_path = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                    if ($profile['profile_picture'] && file_exists($profile['profile_picture']) && 
                        $profile['profile_picture'] !== $defaultProfilePic) {
                        unlink($profile['profile_picture']);
                    }
                    $data['profile_picture'] = $upload_path;
                } else {
                    $error = "Failed to upload profile picture.";
                }
            } else {
                $error = "Invalid file type for profile picture. Only JPG, JPEG, PNG, and GIF are allowed.";
            }
        }

        // Handle cover photo upload
        if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
            $file_extension = strtolower(pathinfo($_FILES['cover_photo']['name'], PATHINFO_EXTENSION));
            if (in_array($file_extension, $allowed_extensions)) {
                $filename = "cover_{$user_id}_" . time() . ".$file_extension";
                $upload_path = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['cover_photo']['tmp_name'], $upload_path)) {
                    if ($profile['cover_photo'] && file_exists($profile['cover_photo']) && 
                        $profile['cover_photo'] !== $defaultCoverPhoto) {
                        unlink($profile['cover_photo']);
                    }
                    $data['cover_photo'] = $upload_path;
                } else {
                    $error = "Failed to upload cover photo.";
                }
            } else {
                $error = "Invalid file type for cover photo. Only JPG, JPEG, PNG, and GIF are allowed.";
            }
        }

        if (empty($error)) {
            try {
                $db->where("user_id", $user_id);
                if ($db->getOne("user_profile")) {
                    $db->where("user_id", $user_id);
                    if ($db->update("user_profile", $data)) {
                        $message = "Profile updated successfully!";
                        $_SESSION['message'] = $message;
                    } else {
                        $error = "Failed to update profile: " . $db->getLastError();
                    }
                } else {
                    $data['user_id'] = $user_id;
                    if ($db->insert("user_profile", $data)) {
                        $message = "Profile created successfully!";
                        $_SESSION['message'] = $message;
                    } else {
                        $error = "Failed to create profile: " . $db->getLastError();
                    }
                }
                if (empty($error)) {
                    $db->where("user_id", $user_id);
                    $profile = $db->getOne("user_profile");
                    $userProfilePic = $profile['profile_picture'] ?: $defaultProfilePic;
                    $userCoverPhoto = $profile['cover_photo'] ?: $defaultCoverPhoto;
                    $completionPercentage = calculateCompletionPercentage($profile);
                }
            } catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social-Talk - Connect with Friends</title>
    <link rel="icon" type="image/x-icon" href="assets/logo/logo1.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style1.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/profile-styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top" aria-label="Main navigation">
        <div class="container">
            <a class="navbar-brand pulse" href="timeline.php">
                <img src="assets/logo/logo.png" alt="Social-Talk Logo" style="height: 50px; width: 120px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                    <div class="nav-item me-3">
                        <input type="search" id="searchFriends" class="form-control" 
                               placeholder="Search friends..." aria-label="Search friends">
                    </div>
                    <div class="nav-item me-3">
                        <a class="nav-link" href="messages.php" data-bs-toggle="modal" 
                           data-bs-target="#chatModal" aria-label="Open chat">
                            <i class="fas fa-comments fa-lg"></i>
                        </a>
                    </div>
                    <div class="nav-item dropdown me-3">
                        <a class="nav-link position-relative" href="notifications.php" 
                           data-bs-toggle="dropdown" aria-expanded="false" 
                           aria-label="Notifications, 3 unread">
                            <i class="fas fa-bell fa-lg"></i>
                            <span class="notification-badge badge bg-danger position-absolute pulse">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" style="width: 350px;">
                            <h6 class="dropdown-header">Notifications</h6>
                            <div class="notification-item unread">
                                <div class="d-flex align-items-center">
                                    <img src="https://images.unsplash.com/photo-1494790108755-2616b612b820?w=50&h=50&fit=crop&crop=face" 
                                         class="profile-pic me-3" alt="Sarah Johnson profile picture">
                                    <div>
                                        <strong>Sarah Johnson</strong> liked your post
                                        <div class="text-muted small">2 minutes ago</div>
                                    </div>
                                </div>
                            </div>
                            <div class="notification-item unread">
                                <div class="d-flex align-items-center">
                                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=50&h=50&fit=crop&crop=face" 
                                         class="profile-pic me-3" alt="Mike Chen profile picture">
                                    <div>
                                        <strong>Mike Chen</strong> sent you a friend request
                                        <div class="text-muted small">5 minutes ago</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a class="nav-link" href="user-profile.php" data-bs-toggle="dropdown" 
                           aria-expanded="false" aria-label="User profile">
                            <img src="<?= htmlspecialchars($userProfilePic) ?>" 
                                 class="profile-pic" alt="User profile picture">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="user-profile.php?id=<?= $_SESSION['user_id'] ?>">
                                <i class="fas fa-user me-2"></i>My Profile
                            </a>
                            <a class="dropdown-item" href="setting.php" onclick="showSettings()">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chatModalTitle">Chat with Friend</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="chat-container" id="chatContainer"></div>
                    <div class="mt-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="messageInput" 
                                   placeholder="Type your message..." aria-label="Type your message">
                            <button class="btn btn-primary" onclick="socialTalk.sendMessage()" 
                                    aria-label="Send message">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <script>
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: "<?= htmlspecialchars($_SESSION['message']) ?>",
                showConfirmButton: false,
                timer: 1500
            });
        </script>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="container">
        <div class="progress-bar"></div>

        <?php if ($message): ?>
            <div id="successAlert" class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <span id="successMessage"><?= htmlspecialchars($message) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div id="errorAlert" class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <span id="errorMessage"><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <div class="cover-photo-section">
            <div class="cover-photo-container">
                <img id="coverPhotoPreview" src="<?= htmlspecialchars($userCoverPhoto) ?>" 
                     class="cover-photo-preview" alt="Cover Photo">
                <div class="cover-photo-overlay" onclick="document.getElementById('coverPhotoInput').click()">
                    <button type="button" class="cover-upload-btn">
                        <i class="fas fa-camera me-2"></i>Change Cover Photo
                    </button>
                </div>
                <div class="cover-actions">
                    <button type="button" class="cover-action-btn" 
                            onclick="document.getElementById('coverPhotoInput').click()" 
                            title="Upload Cover Photo">
                        <i class="fas fa-camera"></i>
                    </button>
                    <button type="button" class="cover-action-btn" 
                            onclick="removeCoverPhoto()" title="Remove Cover Photo">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>

        

        <form id="profileForm" method="POST" enctype="multipart/form-data">
            <input type="file" id="coverPhotoInput" name="cover_photo" accept="image/*" style="display: none;">
            <input type="file" id="profilePicInput" name="profile_picture" accept="image/*" style="display: none;">
            
            <div class="row">
                <div class="col-lg-4">
                    <div class="form-section text-center">
                        <h4 class="section-title">Profile Picture</h4>
                        <div class="profile-pic-container mb-3">
                            <img id="profilePicPreview" src="<?= htmlspecialchars($userProfilePic) ?>" 
                                 class="profile-pic-edit" alt="Profile Picture">
                            <div class="pic-upload-overlay" 
                                 onclick="document.getElementById('profilePicInput').click()">
                                <i class="fas fa-camera"></i>
                            </div>
                        </div>
                        <p class="text-muted small">Click the camera icon to upload a new profile picture</p>
                    </div>

                    <div class="form-section">
                        <h4 class="section-title">Profile Completion</h4>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Progress</span>
                            <span id="completionPercent"><?= $completionPercentage ?>%</span>
                        </div>
                        <div class="progress mb-3">
                            <div id="completionBar" class="progress-bar bg-success" 
                                 style="width: <?= $completionPercentage ?>%"></div>
                        </div>
                        <small class="text-muted">Complete your profile to connect with more people!</small>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="form-section">
                        <h4 class="section-title">Basic Information</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="firstName" name="first_name" 
                                           placeholder="First Name" value="<?= htmlspecialchars($profile['first_name']) ?>" 
                                           required>
                                    <label for="firstName" class="required-field">First Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="lastName" name="last_name" 
                                           placeholder="Last Name" value="<?= htmlspecialchars($profile['last_name']) ?>" 
                                           required>
                                    <label for="lastName" class="required-field">Last Name</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth" 
                                           value="<?= htmlspecialchars($profile['date_of_birth']) ?>">
                                    <label for="dateOfBirth">Date of Birth</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="tel" class="form-control" id="phoneNumber" name="phone_number" 
                                           placeholder="Phone Number" value="<?= htmlspecialchars($profile['phone_number']) ?>">
                                    <label for="phoneNumber">Phone Number</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Gender</label>
                            <div class="gender-selector">
                                <?php foreach (['Male', 'Female', 'Other'] as $gender): ?>
                                    <div class="gender-option <?= ($profile['gender'] === $gender) ? 'selected' : '' ?>" 
                                         data-value="<?= $gender ?>">
                                        <i class="fas fa-<?= $gender === 'Male' ? 'mars' : ($gender === 'Female' ? 'venus' : 'genderless') ?> fa-2x mb-2"></i>
                                        <div><?= $gender ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" id="genderInput" name="gender" 
                                   value="<?= htmlspecialchars($profile['gender']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Blood Group</label>
                            <div class="blood-group-selector">
                                <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bloodGroup): ?>
                                    <div class="blood-group-option <?= ($profile['blood_group'] === $bloodGroup) ? 'selected' : '' ?>" 
                                         data-value="<?= $bloodGroup ?>"><?= $bloodGroup ?></div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" id="bloodGroupInput" name="blood_group" 
                                   value="<?= htmlspecialchars($profile['blood_group']) ?>">
                        </div>

                        <div class="form-floating">
                            <textarea class="form-control" id="bio" name="bio" style="height: 120px" 
                                      placeholder="Tell us about yourself..." maxlength="500"><?= htmlspecialchars($profile['bio']) ?></textarea>
                            <label for="bio">Bio</label>
                            <div class="character-count">
                                <span id="bioCount"><?= strlen($profile['bio']) ?></span>/500 characters
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4 class="section-title">Address Information</h4>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="addressLine1" name="address_line1" 
                                   placeholder="Address Line 1" value="<?= htmlspecialchars($profile['address_line1']) ?>">
                            <label for="addressLine1">Address Line 1</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="addressLine2" name="address_line2" 
                                   placeholder="Address Line 2" value="<?= htmlspecialchars($profile['address_line2']) ?>">
                            <label for="addressLine2">Address Line 2 (Optional)</label>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="city" name="city" 
                                           placeholder="City" value="<?= htmlspecialchars($profile['city']) ?>">
                                    <label for="city">City</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="state" name="state" 
                                           placeholder="State/Province" value="<?= htmlspecialchars($profile['state']) ?>">
                                    <label for="state">State/Province</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="postalCode" name="postal_code" 
                                           placeholder="Postal Code" value="<?= htmlspecialchars($profile['postal_code']) ?>">
                                    <label for="postalCode">Postal Code</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="country" name="country" 
                                           placeholder="Country" value="<?= htmlspecialchars($profile['country']) ?>">
                                    <label for="country">Country</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                        <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary btn-lg" id="resetBtn" role="button">
                            <i class="fas fa-undo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview functionality
        ['profilePicInput', 'coverPhotoInput'].forEach(id => {
            document.getElementById(id).addEventListener('change', function(e) {
                if (e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = e => document.getElementById(`${id.replace('Input', 'Preview')}`).src = e.target.result;
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        });

        // Gender and Blood Group Selection
        document.addEventListener('DOMContentLoaded', () => {
            const selectOptions = (selector, inputId) => {
                document.querySelectorAll(selector).forEach(option => {
                    option.addEventListener('click', () => {
                        document.querySelectorAll(selector).forEach(opt => opt.classList.remove('selected'));
                        option.classList.add('selected');
                        document.getElementById(inputId).value = option.dataset.value;
                    });
                });
            };

            selectOptions('.gender-option', 'genderInput');
            selectOptions('.blood-group-option', 'bloodGroupInput');

            // Bio character counter
            const bio = document.getElementById('bio');
            const bioCount = document.getElementById('bioCount');
            bio.addEventListener('input', () => bioCount.textContent = bio.value.length);
        });

        // Remove cover photo
        function removeCoverPhoto() {
            document.getElementById('coverPhotoPreview').src = '<?= $defaultCoverPhoto ?>';
            document.getElementById('coverPhotoInput').value = '';
        }
    </script>
</body>
</html>
