<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}
$db = new MysqliDb();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = array(
        'site_name' => $_POST['siteName'],
        'max_post_length' => $_POST['maxPostLength'],
        'privacy_policy_url' => $_POST['privacyPolicy']
    );
    // Check if settings exist
    $settings = $db->getOne('settings');
    if ($settings) {
        $db->update('settings', $data);
    } else {
        $db->insert('settings', $data);
    }
}

// Fetch current settings
$settings = $db->getOne('settings');

// If no settings are found, use default values
if (!$settings) {
    $settings = [
        'site_name' => 'SocialTalk',
        'max_post_length' => 1000,
        'privacy_policy_url' => '#',
    ];
}

include('header.php');
?>

<!-- Settings Section -->
<div id="settings-section">
    <h2 class="mb-4">Settings</h2>
    <div class="card">
        <div class="card-header">
            <span>Platform Settings</span>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label for="siteName" class="form-label">Site Name</label>
                    <input type="text" class="form-control" id="siteName" name="siteName" value="<?php echo htmlspecialchars($settings['site_name']); ?>">
                </div>
                <div class="mb-3">
                    <label for="maxPostLength" class="form-label">Maximum Post Length</label>
                    <input type="number" class="form-control" id="maxPostLength" name="maxPostLength" value="<?php echo htmlspecialchars($settings['max_post_length']); ?>">
                </div>
                <div class="mb-3">
                    <label for="privacyPolicy" class="form-label">Privacy Policy URL</label>
                    <input type="url" class="form-control" id="privacyPolicy" name="privacyPolicy" value="<?php echo htmlspecialchars($settings['privacy_policy_url']); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>