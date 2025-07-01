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
    'first_name' => '',
    'last_name' => '',
    'blood_group' => '',
    'country' => '',
    'address_line1' => '',
    'address_line2' => '',
    'city' => '',
    'state' => '',
    'postal_code' => '',
    'phone_number' => '',
    'bio' => '',
    'profile_picture' => '',
    'cover_photo' => '',
    'date_of_birth' => '',
    'gender' => '',
    'relationship' => ''
];

$userProfilePic = $profile['profile_picture'] ?: $defaultProfilePic;
$userCoverPhoto = $profile['cover_photo'] ?: $defaultCoverPhoto;

// Calculate profile completion percentage
function calculateCompletionPercentage($profile)
{
    $fields = [
        'first_name',
        'last_name',
        'profile_picture',
        'cover_photo',
        'date_of_birth',
        'phone_number',
        'gender',
        'blood_group',
        'bio',
        'city',
        'country',
        'relationship'
    ];
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
        'gender' => $_POST['gender'] ?? null,
        'relationship' => $_POST['relationship'] ?? null
    ]);

    if (empty($data['first_name']) || empty($data['last_name'])) {
        $error = "First name and last name are required.";
    } else {
        $upload_dir = "assets/contentimages/{$user_id}/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $allowed_extensions = ['jpg', 'jpeg', 'png','webp', 'gif'];

        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            if (in_array($file_extension, $allowed_extensions)) {
                $filename = "profile_{$user_id}_" . time() . ".$file_extension";
                $upload_path = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                    if (
                        $profile['profile_picture'] && file_exists($profile['profile_picture']) &&
                        $profile['profile_picture'] !== $defaultProfilePic
                    ) {
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
                    if (
                        $profile['cover_photo'] && file_exists($profile['cover_photo']) &&
                        $profile['cover_photo'] !== $defaultCoverPhoto
                    ) {
                        unlink($profile['cover_photo']);
                    }
                    $data['cover_photo'] = $upload_path;
                } else {
                    $error = "Failed to upload cover photo.";
                }
            } else {
                $error = "Invalid file type for cover photo. Only JPG, JPEG, PNG, WEBP and GIF are allowed.";
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
//
$validRelationships = ['Single', 'In a relationship', 'Married', 'Divorced', 'Complicated', null];
if (isset($data['relationship']) && !in_array($data['relationship'], $validRelationships)) {
    $error = "Invalid relationship status selected";
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
    <?php include_once 'includes/header1.php'; ?>
    <style>
        .relationship-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .relationship-option {
            padding: 8px 15px;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }

        .relationship-option:hover {
            background-color: #f8f9fa;
        }

        .relationship-option.selected {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }
    </style>
</head>

<body>
    

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
                        <h4 class="section-title">Relationship Status</h4>
                        <div class="relationship-selector mb-3">
                            <?php
                            $relationshipOptions = [
                                'Single' => 'fas fa-user',
                                'In a relationship' => 'fas fa-heart',
                                'Married' => 'fas fa-ring',
                                'Divorced' => 'fas fa-heart-broken',
                                'Complicated' => 'fas fa-question'
                            ];

                            foreach ($relationshipOptions as $value => $icon): ?>
                                <div class="relationship-option <?= ($profile['relationship'] === $value) ? 'selected' : '' ?>"
                                    data-value="<?= $value ?>">
                                    <i class="<?= $icon ?> me-2"></i>
                                    <?= $value ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="relationshipInput" name="relationship"
                            value="<?= htmlspecialchars($profile['relationship']) ?>">
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

        // Initialize all selectors
    document.addEventListener('DOMContentLoaded', () => {
        // Generic function to handle option selection
        const initSelector = (selector, inputId) => {
            const inputElement = document.getElementById(inputId);
            if (!inputElement) return;

            const options = document.querySelectorAll(selector);
            if (!options.length) return;

            // Set initial selection based on input value
            options.forEach(option => {
                if (option.dataset.value === inputElement.value) {
                    option.classList.add('selected');
                }
            });

            // Add click handlers
            options.forEach(option => {
                option.addEventListener('click', () => {
                    options.forEach(opt => opt.classList.remove('selected'));
                    option.classList.add('selected');
                    inputElement.value = option.dataset.value;
                });
            });
        };

        // Initialize all selectors
        initSelector('.gender-option', 'genderInput');
        initSelector('.blood-group-option', 'bloodGroupInput');
        initSelector('.relationship-option', 'relationshipInput');

        // Bio character counter
        const bio = document.getElementById('bio');
        const bioCount = document.getElementById('bioCount');
        if (bio && bioCount) {
            bio.addEventListener('input', () => bioCount.textContent = bio.value.length);
        }
    });

        // Remove cover photo
        function removeCoverPhoto() {
            document.getElementById('coverPhotoPreview').src = '<?= $defaultCoverPhoto ?>';
            document.getElementById('coverPhotoInput').value = '';
        }
        // Add to your existing selectOptions function
selectOptions('.relationship-option', 'relationshipInput');
    </script>
</body>

</html>