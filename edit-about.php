<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'includes/header1.php';
include_once 'includes/db.php';

?>


   

    <div class="container">
        <!-- Progress Bar -->
        <div class="progress-bar"></div>

        <!-- Success/Error Messages -->
        <div id="successAlert" class="alert alert-success d-none">
            <i class="fas fa-check-circle me-2"></i>
            <span id="successMessage">Profile updated successfully!</span>
        </div>

        <div id="errorAlert" class="alert alert-danger d-none">
            <i class="fas fa-exclamation-circle me-2"></i>
            <span id="errorMessage"></span>
        </div>

        <!-- Cover Photo Section -->
        <div class="cover-photo-section">
            <div class="cover-photo-container">
                <img id="coverPhotoPreview" src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=300&fit=crop" class="cover-photo-preview" alt="Cover Photo">
                <div class="cover-photo-overlay" onclick="document.getElementById('coverPhotoInput').click()">
                    <button class="cover-upload-btn">
                        <i class="fas fa-camera me-2"></i>Change Cover Photo
                    </button>
                </div>
                <div class="cover-actions">
                    <button class="cover-action-btn" onclick="document.getElementById('coverPhotoInput').click()" title="Upload Cover Photo">
                        <i class="fas fa-camera"></i>
                    </button>
                    <button class="cover-action-btn" onclick="removeCoverPhoto()" title="Remove Cover Photo">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
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

        <form id="profileForm" enctype="multipart/form-data">
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-4">
                    <!-- Profile Picture Section -->
                    <div class="form-section text-center">
                        <h4 class="section-title">Profile Picture</h4>
                        <div class="profile-pic-container mb-3">
                            <img id="profilePicPreview" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face" class="profile-pic-edit" alt="Profile Picture">
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
                                    <input type="text" class="form-control" id="firstName" name="first_name" placeholder="First Name">
                                    <label for="firstName" class="required-field">First Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Last Name">
                                    <label for="lastName" class="required-field">Last Name</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth">
                                    <label for="dateOfBirth">Date of Birth</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="tel" class="form-control" id="phoneNumber" name="phone_number" placeholder="Phone Number">
                                    <label for="phoneNumber">Phone Number</label>
                                </div>
                            </div>
                        </div>

                        <!-- Gender Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Gender</label>
                            <div class="gender-selector">
                                <div class="gender-option" data-value="Male">
                                    <i class="fas fa-mars fa-2x mb-2"></i>
                                    <div>Male</div>
                                </div>
                                <div class="gender-option" data-value="Female">
                                    <i class="fas fa-venus fa-2x mb-2"></i>
                                    <div>Female</div>
                                </div>
                                <div class="gender-option" data-value="Other">
                                    <i class="fas fa-genderless fa-2x mb-2"></i>
                                    <div>Other</div>
                                </div>
                            </div>
                            <input type="hidden" id="genderInput" name="gender">
                        </div>

                        <!-- Blood Group Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Blood Group</label>
                            <div class="blood-group-selector">
                                <div class="blood-group-option" data-value="A+">A+</div>
                                <div class="blood-group-option" data-value="A-">A-</div>
                                <div class="blood-group-option" data-value="B+">B+</div>
                                <div class="blood-group-option" data-value="B-">B-</div>
                                <div class="blood-group-option" data-value="AB+">AB+</div>
                                <div class="blood-group-option" data-value="AB-">AB-</div>
                                <div class="blood-group-option" data-value="O+">O+</div>
                                <div class="blood-group-option" data-value="O-">O-</div>
                            </div>
                            <input type="hidden" id="bloodGroupInput" name="blood_group">
                        </div>

                        <!-- Bio Section -->
                        <div class="form-floating">
                            <textarea class="form-control" id="bio" name="bio" style="height: 120px" placeholder="Tell us about yourself..." maxlength="500"></textarea>
                            <label for="bio">Bio</label>
                            <div class="character-count">
                                <span id="bioCount">0</span>/500 characters
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="form-section">
                        <h4 class="section-title">Address Information</h4>
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="addressLine1" name="address_line1" placeholder="Address Line 1">
                            <label for="addressLine1">Address Line 1</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="addressLine2" name="address_line2" placeholder="Address Line 2">
                            <label for="addressLine2">Address Line 2 (Optional)</label>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="city" name="city" placeholder="City">
                                    <label for="city">City</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="state" name="state" placeholder="State/Province">
                                    <label for="state">State/Province</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="postalCode" name="postal_code" placeholder="Postal Code">
                                    <label for="postalCode">Postal Code</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="country" name="country">
                                        <option value="">Select Country</option>
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="UK">United Kingdom</option>
                                        <option value="AU">Australia</option>
                                        <option value="DE">Germany</option>
                                        <option value="FR">France</option>
                                        <option value="IN">India</option>
                                        <option value="JP">Japan</option>
                                        <option value="BR">Brazil</option>
                                        <option value="MX">Mexico</option>
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

    


<?php
include_once '../includes/footer1.php';
?>