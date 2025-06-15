<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . "/vendor/autoload.php";
include_once 'includes/header1.php';

// Initialize MysqliDb
$db = new MysqliDb();

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
$profile_data = $db->where('user_id', $user_id)->getOne('user_profile');
if (!$profile_data) {
    echo "<div class='alert alert-danger'>Error fetching profile: " . $db->getLastError() . "</div>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $db->escape($_POST['first_name'] ?? '');
    $last_name = $db->escape($_POST['last_name'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $phone_number = $db->escape($_POST['phone_number'] ?? '');
    $gender = $db->escape($_POST['gender'] ?? '');
    $blood_group = $db->escape($_POST['blood_group'] ?? '');
    $bio = $db->escape($_POST['bio'] ?? '');
    $address_line1 = $db->escape($_POST['address_line1'] ?? '');
    $address_line2 = $db->escape($_POST['address_line2'] ?? '');
    $city = $db->escape($_POST['city'] ?? '');
    $state = $db->escape($_POST['state'] ?? '');
    $postal_code = $db->escape($_POST['postal_code'] ?? '');
    $country = $db->escape($_POST['country'] ?? '');

    // Handle profile picture upload
$profile_picture = $profile_data['profile_picture'] ?? '';
$old_profile_picture = $profile_picture;

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'Uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    if (in_array($_FILES['profile_picture']['type'], $allowed_types) && $_FILES['profile_picture']['size'] <= $max_size) {
        $new_filename = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $destination = $upload_dir . $new_filename;
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
            $profile_picture = $new_filename; // Store filename only
        } else {
            $error_message = "Failed to move profile picture.";
        }
    } else {
        $error_message = "Invalid profile picture format or size (max 2MB, JPG/PNG/GIF only).";
    }
} elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
    $error_message = "Profile picture upload error: " . $_FILES['profile_picture']['error'];
}

// Proceed with database update
if (!isset($error_message)) {
    $data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'date_of_birth' => $date_of_birth,
        'phone_number' => $phone_number,
        'gender' => $gender,
        'blood_group' => $blood_group,
        'bio' => $bio,
        'address_line1' => $address_line1,
        'address_line2' => $address_line2,
        'city' => $city,
        'state' => $state,
        'postal_code' => $postal_code,
        'country' => $country,
        'profile_picture' => $profile_picture
    ];

    $db->where('user_id', $user_id);
    if ($db->has('user_profile')) {
        if ($db->update('user_profile', $data)) {
            // Delete old profile picture if a new one was uploaded
            if ($profile_picture !== $old_profile_picture && $old_profile_picture && file_exists($upload_dir . $old_profile_picture)) {
                unlink($upload_dir . $old_profile_picture);
            }
            $success_message = "Profile updated successfully!";
            $profile_data = $db->where('user_id', $user_id)->getOne('user_profile');
        } else {
            $error_message = "Error updating profile: " . $db->getLastError();
        }
    } else {
        $data['user_id'] = $user_id;
        if ($db->insert('user_profile', $data)) {
            $success_message = "Profile created successfully!";
            $profile_data = $db->where('user_id', $user_id)->getOne('user_profile');
        } else {
            $error_message = "Error creating profile: " . $db->getLastError();
        }
    }
}

    /* // Handle profile picture upload
    $profile_picture = $profile_data['profile_picture'] ?? '';
    $old_profile_picture = $profile_picture;

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'Uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        if (in_array($_FILES['profile_picture']['type'], $allowed_types) && $_FILES['profile_picture']['size'] <= $max_size) {
            $profile_picture = $upload_dir . uniqid() . '_' . basename($_FILES['profile_picture']['name']);
            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture)) {
                $error_message = "Failed to move profile picture.";
            }
        } else {
            $error_message = "Invalid profile picture format or size (max 2MB, JPG/PNG/GIF only).";
        }
    } elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $error_message = "Profile picture upload error: " . $_FILES['profile_picture']['error'];
    }

    // Proceed with database update if no errors
    if (!isset($error_message)) {
        $data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'date_of_birth' => $date_of_birth,
            'phone_number' => $phone_number,
            'gender' => $gender,
            'blood_group' => $blood_group,
            'bio' => $bio,
            'address_line1' => $address_line1,
            'address_line2' => $address_line2,
            'city' => $city,
            'state' => $state,
            'postal_code' => $postal_code,
            'country' => $country,
            'profile_picture' => $profile_picture
        ];

        $db->where('user_id', $user_id);
        if ($db->has('user_profile')) {
            if ($db->update('user_profile', $data)) {
                // Delete old profile picture if a new one was uploaded
                if ($profile_picture !== $old_profile_picture && $old_profile_picture && file_exists($old_profile_picture)) {
                    unlink($old_profile_picture);
                }
                $success_message = "Profile updated successfully!";
                $profile_data = $db->where('user_id', $user_id)->getOne('user_profile');
            } else {
                $error_message = "Error updating profile: " . $db->getLastError();
            }
        } else {
            $data['user_id'] = $user_id;
            if ($db->insert('user_profile', $data)) {
                $success_message = "Profile created successfully!";
                $profile_data = $db->where('user_id', $user_id)->getOne('user_profile');
            } else {
                $error_message = "Error creating profile: " . $db->getLastError();
            }
        }
    }
        */
} 
?>

<div class="container">
    <!-- Progress Bar -->
    <div class="progress-bar"></div>

    <!-- Success/Error Messages -->
    <?php if (isset($success_message)): ?>
        <div id="successAlert" class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <span id="successMessage"><?php echo htmlspecialchars($success_message); ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div id="errorAlert" class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <span id="errorMessage"><?php echo htmlspecialchars($error_message); ?></span>
        </div>
    <?php endif; ?>
    <div id="coverPhotoAlert" class="alert d-none">
        <span id="coverPhotoMessage"></span>
    </div>

    <form id="profileForm" enctype="multipart/form-data" method="POST">
        <input type="hidden" name="MAX_FILE_SIZE" value="2097152"> <!-- 2MB for profile picture -->

        <!-- Cover Photo Section -->
        <div class="cover-photo-section">
            <div class="cover-photo-container">
                <img id="coverPhotoPreview" src="<?php echo htmlspecialchars($profile_data['cover_photo'] ? 'Uploads/' . $profile_data['cover_photo'] : 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=300&fit=crop'); ?>" class="cover-photo-preview" alt="Cover Photo">
                <div class="cover-photo-overlay" onclick="document.getElementById('coverPhotoInput').click()">
                    <button type="button" class="cover-upload-btn">
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
            <input type="file" id="coverPhotoInput" accept="image/*" style="display: none;">
            
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
                         <img id="profilePicPreview" src="<?php echo htmlspecialchars($profile_data['profile_picture'] ? 'Uploads/' . $profile_data['profile_picture'] : 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face'); ?>" class="profile-pic-edit" alt="Profile Picture">
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
// JavaScript to handle gender, blood group, file inputs, and AJAX cover photo upload
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

    // Cover photo input with AJAX upload
    const coverPhotoInput = document.getElementById('coverPhotoInput');
    const coverPhotoPreview = document.getElementById('coverPhotoPreview');
    const coverPhotoAlert = document.getElementById('coverPhotoAlert');
    const coverPhotoMessage = document.getElementById('coverPhotoMessage');

    coverPhotoInput.addEventListener('change', async function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Client-side validation
        if (file.size > 5 * 1024 * 1024) {
            showCoverPhotoAlert('Cover photo must be less than 5MB', 'danger');
            e.target.value = '';
            return;
        }
        if (!file.type.match(/^image\/(jpeg|png|gif)$/)) {
            showCoverPhotoAlert('Please select a valid image file (JPG, PNG, or GIF)', 'danger');
            e.target.value = '';
            return;
        }

        // Preview the image
        const reader = new FileReader();
        reader.onload = function(e) {
            coverPhotoPreview.src = e.target.result;
        };
        reader.readAsDataURL(file);

        // Upload via AJAX
        const formData = new FormData();
        formData.append('cover_photo', file);

        try {
            const response = await fetch('update_cover_photo.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                showCoverPhotoAlert(result.message, 'success');
                coverPhotoPreview.src = result.cover_photo_url;
            } else {
                showCoverPhotoAlert(result.message, 'danger');
                coverPhotoPreview.src = '<?php echo htmlspecialchars($profile_data['cover_photo'] ?? 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=300&fit=crop'); ?>';
            }
        } catch (error) {
            showCoverPhotoAlert('Error uploading cover photo: ' + error.message, 'danger');
            coverPhotoPreview.src = '<?php echo htmlspecialchars($profile_data['cover_photo'] ?? 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=300&fit=crop'); ?>';
        }

        e.target.value = ''; // Reset input
    });

    // Show cover photo alert
    function showCoverPhotoAlert(message, type) {
        coverPhotoAlert.classList.remove('d-none', 'alert-success', 'alert-danger');
        coverPhotoAlert.classList.add(`alert-${type}`);
        coverPhotoMessage.textContent = message;
        setTimeout(() => {
            coverPhotoAlert.classList.add('d-none');
        }, 5000);
    }

/*     // Handle cover photo removal
    window.removeCoverPhoto = async function() {
        try {
            const response = await fetch('update_cover_photo.php', {
                method: 'POST',
                body: new FormData() // Empty FormData to indicate removal
            });
            const result = await response.json();

            if (result.success) {
                coverPhotoPreview.src = 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=300&fit=crop';
                showCoverPhotoAlert(result.message, 'success');
            } else {
                showCoverPhotoAlert(result.message, 'danger');
            }
        } catch (error) {
            showCoverPhotoAlert('Error removing cover photo: ' + error.message, 'danger');
        }
    }; */
    // Update JavaScript to handle cover photo removal
window.removeCoverPhoto = async function() {
    try {
        const response = await fetch('update_cover_photo.php', {
            method: 'POST',
            body: new FormData()
        });
        const result = await response.json();

        if (result.success) {
            coverPhotoPreview.src = 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=300&fit=crop';
            
            showCoverPhotoAlert(result.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 2000);
            
        } else {
            showCoverPhotoAlert(result.message, 'danger');
        }
    } catch (error) {
        showCoverPhotoAlert('Error removing cover photo: ' + error.message, 'danger');
    }
};

    // Handle form reset
    window.resetForm = function() {
        document.getElementById('profileForm').reset();
        genderOptions.forEach(opt => opt.classList.remove('selected'));
        bloodGroupOptions.forEach(opt => opt.classList.remove('selected'));
        genderInput.value = '<?php echo htmlspecialchars($profile_data['gender'] ?? ''); ?>';
        bloodGroupInput.value = '<?php echo htmlspecialchars($profile_data['blood_group'] ?? ''); ?>';
        bioCount.textContent = '<?php echo strlen($profile_data['bio'] ?? ''); ?>';
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