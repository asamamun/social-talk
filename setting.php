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

include_once 'includes/header1.php';


?>

  <!-- Settings -->
  <div class="container">
    <div class="settings-card">
      <ul class="nav nav-tabs mb-4" id="settingsTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#account">Account</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#privacy">Privacy</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#notifications">Notifications</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#security">Security</a></li>
      </ul>

      <div class="tab-content">
        <!-- Account Settings -->
        <div class="tab-pane fade show active" id="account">
          <h5>Account Settings</h5>
          <form>
            <div class="mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" class="form-control" value="John Doe">
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" value="john@example.com">
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </form>
        </div>

        <!-- Privacy -->
        <div class="tab-pane fade" id="privacy">
          <h5>Privacy Settings</h5>
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="showEmail" checked>
            <label class="form-check-label" for="showEmail">Show email on profile</label>
          </div>
        </div>

        <!-- Notifications -->
        <div class="tab-pane fade" id="notifications">
          <h5>Notification Settings</h5>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="emailNotifs" checked>
            <label class="form-check-label" for="emailNotifs">Email Notifications</label>
          </div>
        </div>

        <!-- Security -->
        <div class="tab-pane fade" id="security">
          <h5>Security & Danger Zone</h5>
          <div class="mb-3">
            <label class="form-label">Change Password</label>
            <input type="password" class="form-control" placeholder="New Password">
          </div>
          <button class="btn btn-warning mb-3">Update Password</button>
          <hr>
          <h6 class="text-danger">Delete Account</h6>
          <p class="small">This action cannot be undone. Your profile and all data will be permanently removed.</p>
          <button class="btn btn-danger" onclick="confirmDelete()">Delete My Account</button>
        </div>
      </div>

      <hr class="mt-5">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="darkModeToggle">
        <label class="form-check-label" for="darkModeToggle">Enable Dark Mode</label>
      </div>
    </div>
  </div>


<?php
include_once 'includes/footer1.php';
?>