<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';

// Redirect if not logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// Redirect if user is admin
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    header("Location: admin/");
    exit;
}

// Database connection
$db = new MysqliDb();

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch user data
$db->where("user_id", $_SESSION['user_id']);
$user = $db->getOne("users");

if (!$user) {
    die("User not found.");
}

// Fetch current user profile data
$db->where("user_id", $user_id);
$profile = $db->getOne("user_profile");

// If no profile exists, create empty array
if (!$profile) {
    $profile = [
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
        'gender' => ''
    ];
}

// Set default images if none exist
$defaultProfilePic = 'assets/images/profile.png';
$defaultCoverPhoto = 'assets/images/cover.jpg';
$userProfilePic = !empty($profile['profile_picture']) ? $profile['profile_picture'] : $defaultProfilePic;
$userCoverPhoto = !empty($profile['cover_photo']) ? $profile['cover_photo'] : $defaultCoverPhoto;

// Calculate completion percentage
function calculateCompletionPercentage($profile) {
    $fields = ['first_name', 'last_name', 'profile_picture', 'cover_photo', 'date_of_birth', 'phone_number', 'gender', 'blood_group', 'bio', 'city', 'country'];
    $completed = 0;
    foreach ($fields as $field) {
        if (!empty($profile[$field])) {
            $completed++;
        }
    }
    return round(($completed / count($fields)) * 100);
}

$completionPercentage = calculateCompletionPercentage($profile);

include_once 'includes/header1.php';

?>

<div class="container">
    <!-- Progress Bar -->
    <div class="progress-bar"></div>

    <!-- Success/Error Messages -->
    <?php if ($message): ?>
        <div id="successAlert" class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <span id="successMessage"><?php echo htmlspecialchars($message); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div id="errorAlert" class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <span id="errorMessage"><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <!-- Cover Photo Section -->
    <div class="cover-photo-section">
        <div class="cover-photo-container">
            <img id="coverPhotoPreview" src="<?php echo htmlspecialchars($userCoverPhoto); ?>" class="cover-photo-preview" alt="Cover Photo">
            <div class="cover-photo-overlay" onclick="document.getElementById('coverPhotoInput').click()">
                <button type="button" class="cover-upload-btn">
                    <i class="fas fa-camera me-2"></i>Change Cover Photo
                </button>
            </div>
            <div class="cover-actions">
                <button type="button" class="cover-action-btn" onclick="document.getElementById('coverPhotoInput').click()" title="Upload Cover Photo">
                    <i class="fas fa-camera"></i>
                </button>
                <button type="button" class="cover-action-btn" onclick="removeCoverPhoto()" title="Remove Cover Photo">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Cover Photo Tips -->
    <div class="cover-photo-tips">
        <h6><i class="fas fa-lightbulb me-2"></i>Cover Photo Tips</h6>
        <ul>
            <li>Recommended size: 1200x300 pixels for best quality</li>
            <li>Accepted formats: JPG, PNG, GIF (max 5MB)</li>
            <li>Choose an image that represents your personality or interests</li>
            <li>Avoid text-heavy images as they may be hard to read</li>
        </ul>
    </div>

    <form id="profileForm" method="POST" enctype="multipart/form-data">
        <!-- Hidden file inputs -->
        <input type="file" id="coverPhotoInput" name="cover_photo" accept="image/*" style="display: none;">
        <input type="file" id="profilePicInput" name="profile_picture" accept="image/*" style="display: none;">
        
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-4">
                <!-- Profile Picture Section -->
                <div class="form-section text-center">
                    <h4 class="section-title">Profile Picture</h4>
                    <div class="profile-pic-container mb-3">
                        <img id="profilePicPreview" src="<?php echo htmlspecialchars($userProfilePic); ?>" class="profile-pic-edit" alt="Profile Picture">
                        <div class="pic-upload-overlay" onclick="document.getElementById('profilePicInput').click()">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                    <p class="text-muted small">Click the camera icon to upload a new profile picture</p>
                </div>

                <!-- Quick Stats -->
                <div class="form-section">
                    <h4 class="section-title">Profile Completion</h4>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Progress</span>
                        <span id="completionPercent"><?php echo $completionPercentage; ?>%</span>
                    </div>
                    <div class="progress mb-3">
                        <div id="completionBar" class="progress-bar bg-success" style="width: <?php echo $completionPercentage; ?>%"></div>
                    </div>
                    <small class="text-muted">Complete your profile to connect with more people!</small>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="form-section">
                    <h4 class="section-title">Basic Information</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="firstName" name="first_name" placeholder="First Name" value="<?php echo htmlspecialchars($profile['first_name']); ?>" required>
                                <label for="firstName" class="required-field">First Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($profile['last_name']); ?>" required>
                                <label for="lastName" class="required-field">Last Name</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth" value="<?php echo htmlspecialchars($profile['date_of_birth']); ?>">
                                <label for="dateOfBirth">Date of Birth</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="phoneNumber" name="phone_number" placeholder="Phone Number" value="<?php echo htmlspecialchars($profile['phone_number']); ?>">
                                <label for="phoneNumber">Phone Number</label>
                            </div>
                        </div>
                    </div>

                    <!-- Gender Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Gender</label>
                        <div class="gender-selector">
                            <div class="gender-option <?php echo ($profile['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>" data-value="Male">
                                <i class="fas fa-mars fa-2x mb-2"></i>
                                <div>Male</div>
                            </div>
                            <div class="gender-option <?php echo ($profile['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>" data-value="Female">
                                <i class="fas fa-venus fa-2x mb-2"></i>
                                <div>Female</div>
                            </div>
                            <div class="gender-option <?php echo ($profile['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>" data-value="Other">
                                <i class="fas fa-genderless fa-2x mb-2"></i>
                                <div>Other</div>
                            </div>
                        </div>
                        <input type="hidden" id="genderInput" name="gender" value="<?php echo htmlspecialchars($profile['gender'] ?? ''); ?>">
                    </div>

                    <!-- Blood Group Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Blood Group</label>
                        <div class="blood-group-selector">
                            <div class="blood-group-option <?php echo ($profile['blood_group'] === 'A+') ? 'selected' : ''; ?>" data-value="A+">A+</div>
                            <div class="blood-group-option <?php echo ($profile['blood_group'] === 'A-') ? 'selected' : ''; ?>" data-value="A-">A-</div>
                            <div class="blood-group-option <?php echo ($profile['blood_group'] === 'B+') ? 'selected' : ''; ?>" data-value="B+">B+</div>
                            <div class="blood-group-option <?php echo ($profile['blood_group'] === 'B-') ? 'selected' : ''; ?>" data-value="B-">B-</div>
                            <div class="blood-group-option <?php echo ($profile['blood_group'] === 'AB+') ? 'selected' : ''; ?>" data-value="AB+">AB+</div>
                            <div class="blood-group-option <?php echo ($profile['blood_group'] === 'AB-') ? 'selected' : ''; ?>" data-value="AB-">AB-</div>
                            <div class="blood-group-option <?php echo ($profile['blood_group'] === 'O+') ? 'selected' : ''; ?>" data-value="O+">O+</div>
                            <div class="blood-group-option <?php echo ($profile['blood_group'] === 'O-') ? 'selected' : ''; ?>" data-value="O-">O-</div>
                        </div>
                        <input type="hidden" id="bloodGroupInput" name="blood_group" value="<?php echo htmlspecialchars($profile['blood_group']); ?>">
                    </div>

                    <!-- Bio Section -->
                    <div class="form-floating">
                        <textarea class="form-control" id="bio" name="bio" style="height: 120px" placeholder="Tell us about yourself..." maxlength="500"><?php echo htmlspecialchars($profile['bio']); ?></textarea>
                        <label for="bio">Bio</label>
                        <div class="character-count">
                            <span id="bioCount"><?php echo strlen($profile['bio']); ?></span>/500 characters
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="form-section">
                    <h4 class="section-title">Address Information</h4>
                    
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="addressLine1" name="address_line1" placeholder="Address Line 1" value="<?php echo htmlspecialchars($profile['address_line1']); ?>">
                        <label for="addressLine1">Address Line 1</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="addressLine2" name="address_line2" placeholder="Address Line 2" value="<?php echo htmlspecialchars($profile['address_line2']); ?>">
                        <label for="addressLine2">Address Line 2 (Optional)</label>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="city" name="city" placeholder="City" value="<?php echo htmlspecialchars($profile['city']); ?>">
                                <label for="city">City</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="state" name="state" placeholder="State/Province" value="<?php echo htmlspecialchars($profile['state']); ?>">
                                <label for="state">State/Province</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="postalCode" name="postal_code" placeholder="Postal Code" value="<?php echo htmlspecialchars($profile['postal_code']); ?>">
                                <label for="postalCode">Postal Code</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="country" name="country" placeholder="Country" value="<?php echo htmlspecialchars($profile['country']); ?>">
                                <label for="country">Country</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section text-center">
                    <button type="submit" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary btn-lg" id="resetBtn" role="button">
                        <i class="fas fa-undo me-2"></i>Reset
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>



<?php
include_once 'includes/footer1.php';
?>