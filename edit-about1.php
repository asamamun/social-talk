<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


include_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$profile_data = null;

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch user profile data
$query = "SELECT * FROM user_profile WHERE user_id = " . (int)$user_id;
$result = $conn->query($query);
if ($result) {
    $profile_data = $result->fetch_assoc();
} else {
    echo "<div class='alert alert-danger'>Error fetching profile: " . $conn->error . "</div>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name'] ?? '');
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number'] ?? '');
    $gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? '');
    $blood_group = mysqli_real_escape_string($conn, $_POST['blood_group'] ?? '');
    $bio = mysqli_real_escape_string($conn, $_POST['bio'] ?? '');
    $address_line1 = mysqli_real_escape_string($conn, $_POST['address_line1'] ?? '');
    $address_line2 = mysqli_real_escape_string($conn, $_POST['address_line2'] ?? '');
    $city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
    $state = mysqli_real_escape_string($conn, $_POST['state'] ?? '');
    $postal_code = mysqli_real_escape_string($conn, $_POST['postal_code'] ?? '');
    $country = mysqli_real_escape_string($conn, $_POST['country'] ?? '');

    // Handle file uploads
    $profile_picture = $profile_data['profile_picture'] ?? '';
    $cover_photo = $profile_data['cover_photo'] ?? '';

    // Profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        if (in_array($_FILES['profile_picture']['type'], $allowed_types) && $_FILES['profile_picture']['size'] <= $max_size) {
            $profile_picture = $upload_dir . uniqid() . '_' . basename($_FILES['profile_picture']['name']);
            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture)) {
                $error_message = "Failed to move profile picture to $profile_picture.";
            }
        } else {
            $error_message = "Invalid profile picture format or size (max 2MB, JPG/PNG/GIF only).";
        }
    } elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $error_message = "Profile picture upload error: " . $_FILES['profile_picture']['error'];
    }

/*     // Cover photo upload
    if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        if (in_array($_FILES['cover_photo']['type'], $allowed_types) && $_FILES['cover_photo']['size'] <= $max_size) {
            $cover_photo = $upload_dir . uniqid() . '_' . basename($_FILES['cover_photo']['name']);
            if (!move_uploaded_file($_FILES['cover_photo']['tmp_name'], $cover_photo)) {
                $error_message = "Failed to move cover photo to $cover_photo.";
            }
        } else {
            $error_message = "Invalid cover photo format or size (max 5MB, JPG/PNG/GIF only).";
        }
    } elseif (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $error_message = "Cover photo upload error: " . $_FILES['cover_photo']['error'];
    }  */
      // Cover photo upload
if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    if (in_array($_FILES['cover_photo']['type'], $allowed_types) && $_FILES['cover_photo']['size'] <= $max_size) {
        $cover_photo = $upload_dir . uniqid() . '_' . basename($_FILES['cover_photo']['name']);
        if (!move_uploaded_file($_FILES['cover_photo']['tmp_name'], $cover_photo)) {
            $error_message = "Failed to move cover photo to $cover_photo.";
        }
    } else {
        $error_message = "Invalid cover photo format or size (max 2MB, JPG/PNG/GIF only).";
    }
}


    // Proceed with database update if no errors
    if (!isset($error_message)) {
        // Use prepared statements for security
        if ($profile_data) {
            $stmt = $conn->prepare("UPDATE user_profile SET 
                first_name = ?, last_name = ?, date_of_birth = ?, phone_number = ?, 
                gender = ?, blood_group = ?, bio = ?, address_line1 = ?, address_line2 = ?, 
                city = ?, state = ?, postal_code = ?, country = ?, profile_picture = ?, cover_photo = ? 
                WHERE user_id = ?");
            $stmt->bind_param("sssssssssssssssi", $first_name, $last_name, $date_of_birth, $phone_number, 
                $gender, $blood_group, $bio, $address_line1, $address_line2, 
                $city, $state, $postal_code, $country, $profile_picture, $cover_photo, $user_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO user_profile (
                user_id, first_name, last_name, date_of_birth, phone_number, 
                gender, blood_group, bio, address_line1, address_line2, 
                city, state, postal_code, country, profile_picture, cover_photo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssssssssssss", $user_id, $first_name, $last_name, $date_of_birth, $phone_number, 
                $gender, $blood_group, $bio, $address_line1, $address_line2, 
                $city, $state, $postal_code, $country, $profile_picture, $cover_photo);
        }

        if ($stmt->execute()) {
            $success_message = "Profile updated successfully!";
            // Refresh profile data
            $result = $conn->query("SELECT * FROM user_profile WHERE user_id = " . (int)$user_id);
            if ($result) {
                $profile_data = $result->fetch_assoc();
            }
        } else {
            $error_message = "Error updating profile: " . $stmt->error;
        }
        $stmt->close();
    }
}
include_once 'includes/header1.php';
?>

<div class="container">
    <!-- Progress Bar -->
    <div class="progress-bar"></div>

    <!-- Success/Error Messages -->
    <?php if (isset($success_message)): ?>
        <script>
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: '<?php echo htmlspecialchars($success_message); ?>',
                showConfirmButton: false,
                timer: 1500
            });
        </script>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div id="errorAlert" class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <span id="errorMessage"><?php echo htmlspecialchars($error_message); ?></span>
        </div>
    <?php endif; ?>
<form id="profileForm" enctype="multipart/form-data" method="POST">
        <input type="hidden" name="MAX_FILE_SIZE" value="5242880"> <!-- 5MB -->
    <!-- Cover Photo Section -->
    <div class="cover-photo-section">
        <div class="cover-photo-container">
            <img id="coverPhotoPreview" src="<?php echo htmlspecialchars($profile_data['cover_photo'] ?? 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=300&fit=crop'); ?>" class="cover-photo-preview" alt="Cover Photo">
            <div class="cover-photo-overlay" onclick="document.getElementById('coverPhotoInput').click()">
                <button class="cover-upload-btn">
                    <i class="fas fa-camera me-2"></i>Change Cover Photo
                </button>
            </div>
            <div class="cover-actions">
                <div class="cover-action-btn" onclick="document.getElementById('coverPhotoInput').click()" title="Upload Cover Photo">
                    <i class="fas fa-camera"></i>
                </div>
                <div class="cover-action-btn" onclick="removeCoverPhoto()" title="Remove Cover Photo">
                    <i class="fas fa-trash"></i>
                </div>
            </div>
        </div>
        <input type="hidden" name="MAX_FILE_SIZE" value="5242880"> <!-- 5MB -->
        <input type="file" id="coverPhotoInput" name="cover_photo" accept="image/*" style="display: none;">
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

    
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-4">
                <!-- Profile Picture Section -->
                <div class="form-section text-center">
                    <h4 class="section-title">Profile Picture</h4>
                    <div class="profile-pic-container mb-3">
                        <img id="profilePicPreview" src="<?php echo htmlspecialchars($profile_data['profile_picture'] ?? 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face'); ?>" class="profile-pic-edit" alt="Profile Picture">
                        <div class="pic-upload-overlay" onclick="document.getElementById('profilePicInput').click()">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                    <input type="file" id="profilePicInput" name="profile_picture" accept="image/*" style="display: none;">
                    <p class="text-muted small">Click the camera icon to upload a new profile picture</p>
                </div>

                <!-- Quick Stats -->
                <div class="form-section">
                    <h4 class="section-title">Profile Completion</h4>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Progress</span>
                        <span id="completionPercent">75%</span>
                    </div>
                    <div class="progress mb-3">
                        <div id="completionBar" class="progress-bar bg-success" style="width: 75%"></div>
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
                                <input type="text" class="form-control" id="firstName" name="first_name" placeholder="First Name" value="<?php echo htmlspecialchars($profile_data['first_name'] ?? ''); ?>">
                                <label for="firstName" class="required-field">First Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($profile_data['last_name'] ?? ''); ?>">
                                <label for="lastName" class="required-field">Last Name</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth" value="<?php echo htmlspecialchars($profile_data['date_of_birth'] ?? ''); ?>">
                                <label for="dateOfBirth">Date of Birth</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="phoneNumber" name="phone_number" placeholder="Phone Number" value="<?php echo htmlspecialchars($profile_data['phone_number'] ?? ''); ?>">
                                <label for="phoneNumber">Phone Number</label>
                            </div>
                        </div>
                    </div>

                    <!-- Gender Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Gender</label>
                        <div class="gender-selector">
                            <div class="gender-option <?php echo ($profile_data['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>" data-value="Male">
                                <i class="fas fa-mars fa-2x mb-2"></i>
                                <div>Male</div>
                            </div>
                            <div class="gender-option <?php echo ($profile_data['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>" data-value="Female">
                                <i class="fas fa-venus fa-2x mb-2"></i>
                                <div>Female</div>
                            </div>
                            <div class="gender-option <?php echo ($profile_data['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>" data-value="Other">
                                <i class="fas fa-genderless fa-2x mb-2"></i>
                                <div>Other</div>
                            </div>
                        </div>
                        <input type="hidden" id="genderInput" name="gender" value="<?php echo htmlspecialchars($profile_data['gender'] ?? ''); ?>">
                    </div>

                    <!-- Blood Group Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Blood Group</label>
                        <div class="blood-group-selector">
                            <?php
                            $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                            foreach ($blood_groups as $group) {
                                $selected = ($profile_data['blood_group'] ?? '') === $group ? 'selected' : '';
                                echo "<div class='blood-group-option $selected' data-value='$group'>$group</div>";
                            }
                            ?>
                        </div>
                        <input type="hidden" id="bloodGroupInput" name="blood_group" value="<?php echo htmlspecialchars($profile_data['blood_group'] ?? ''); ?>">
                    </div>

                    <!-- Bio Section -->
                    <div class="form-floating">
                        <textarea class="form-control" id="bio" name="bio" style="height: 120px" placeholder="Tell us about yourself..." maxlength="500"><?php echo htmlspecialchars($profile_data['bio'] ?? ''); ?></textarea>
                        <label for="bio">Bio</label>
                        <div class="character-count">
                            <span id="bioCount"><?php echo strlen($profile_data['bio'] ?? ''); ?></span>/500 characters
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="form-section">
                    <h4 class="section-title">Address Information</h4>
                    
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="addressLine1" name="address_line1" placeholder="Address Line 1" value="<?php echo htmlspecialchars($profile_data['address_line1'] ?? ''); ?>">
                        <label for="addressLine1">Address Line 1</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="addressLine2" name="address_line2" placeholder="Address Line 2" value="<?php echo htmlspecialchars($profile_data['address_line2'] ?? ''); ?>">
                        <label for="addressLine2">Address Line 2 (Optional)</label>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="city" name="city" placeholder="City" value="<?php echo htmlspecialchars($profile_data['city'] ?? ''); ?>">
                                <label for="city">City</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="state" name="state" placeholder="State/Province" value="<?php echo htmlspecialchars($profile_data['state'] ?? ''); ?>">
                                <label for="state">State/Province</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="postalCode" name="postal_code" placeholder="Postal Code" value="<?php echo htmlspecialchars($profile_data['postal_code'] ?? ''); ?>">
                                <label for="postalCode">Postal Code</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="country" name="country">
                                    <option value="">Select Country</option>
                                    <?php
                                    $countries = [
                                        'BD' => 'Bangladesh',
                                        'US' => 'United States',
                                        'CA' => 'Canada',
                                        'UK' => 'United Kingdom',
                                        'AU' => 'Australia',
                                        'DE' => 'Germany',
                                        'FR' => 'France',
                                        'IN' => 'India',
                                        'JP' => 'Japan',
                                        'BR' => 'Brazil',
                                        'MX' => 'Mexico'                                        
                                    ];
                                    foreach ($countries as $code => $name) {
                                        $selected = ($profile_data['country'] ?? '') === $code ? 'selected' : '';
                                        echo "<option value='$code' $selected>$name</option>";
                                    }
                                    ?>
                                </select>
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
                    <button type="button" class="btn btn-secondary btn-lg" onclick="resetForm()">
                        <i class="fas fa-undo me-2"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
// JavaScript to handle gender, blood group, and file inputs
document.addEventListener('DOMContentLoaded', function() {
    // Gender selection
    const genderOptions = document.querySelectorAll('.gender-option');
    const genderInput = document.getElementById('genderInput');
    genderOptions.forEach(option => {
        option.addEventListener('click', function() {
            genderOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            genderInput.value = this.getAttribute('data-value');
        });
    });

    // Blood group selection
    const bloodGroupOptions = document.querySelectorAll('.blood-group-option');
    const bloodGroupInput = document.getElementById('bloodGroupInput');
    bloodGroupOptions.forEach(option => {
        option.addEventListener('click', function() {
            bloodGroupOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            bloodGroupInput.value = this.getAttribute('data-value');
        });
    });

    // Bio character count
    const bioTextarea = document.getElementById('bio');
    const bioCount = document.getElementById('bioCount');
    bioTextarea.addEventListener('input', function() {
        bioCount.textContent = this.value.length;
    });

    // Profile picture input
    const profilePicInput = document.getElementById('profilePicInput');
    const profilePicPreview = document.getElementById('profilePicPreview');
    if (profilePicInput) {
        profilePicInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Profile picture must be less than 2MB');
                    e.target.value = '';
                    return;
                }
                if (!file.type.match(/^image\/(jpeg|png|gif)$/)) {
                    alert('Please select a valid image file (JPG, PNG, or GIF)');
                    e.target.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePicPreview.src = e.target.result;
                    console.log('Profile picture selected:', file.name);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Cover photo input
    const coverPhotoInput = document.getElementById('coverPhotoInput');
    const coverPhotoPreview = document.getElementById('coverPhotoPreview');
    if (coverPhotoInput) {
        coverPhotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('Cover photo must be less than 5MB');
                    e.target.value = '';
                    return;
                }
                if (!file.type.match(/^image\/(jpeg|png|gif)$/)) {
                    alert('Please select a valid image file (JPG, PNG, or GIF)');
                    e.target.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    coverPhotoPreview.src = e.target.result;
                    console.log('Cover photo selected:', file.name);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Handle cover photo removal
    window.removeCoverPhoto = function() {
        coverPhotoPreview.src = 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=300&fit=crop';
        coverPhotoInput.value = '';
        console.log('Cover photo removed.');
    };

    // Handle form reset
    window.resetForm = function() {
        document.getElementById('profileForm').reset();
        genderOptions.forEach(opt => opt.classList.remove('selected'));
        bloodGroupOptions.forEach(opt => opt.classList.remove('selected'));
        genderInput.value = '<?php echo htmlspecialchars($profile_data['gender'] ?? ''); ?>';
        bloodGroupInput.value = '<?php echo htmlspecialchars($profile_data['blood_group'] ?? ''); ?>';
        bioCount.textContent = '<?php echo strlen($profile_data['bio'] ?? ''); ?>';
        coverPhotoPreview.src = '<?php echo htmlspecialchars($profile_data['cover_photo'] ?? 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=300&fit=crop'); ?>';
        profilePicPreview.src = '<?php echo htmlspecialchars($profile_data['profile_picture'] ?? 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face'); ?>';
    };

    // Mock socialTalk.updateProfileCompletion
    window.socialTalk = window.socialTalk || {};
    socialTalk.updateProfileCompletion = function() {
        console.log('Profile completion updated.');
        document.getElementById('completionPercent').textContent = '75%';
        document.getElementById('completionBar').style.width = '75%';
    };
});
</script>

</body>
</html>