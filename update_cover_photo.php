<?php
header('Content-Type: application/json');

require __DIR__ . "/vendor/autoload.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new MysqliDb();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to update your cover photo.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$upload_dir = 'Uploads/';
$response = ['success' => false, 'message' => 'Unknown error occurred.'];

// Ensure upload directory exists
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Fetch current cover photo filename
$current_profile = $db->where('user_id', $user_id)->getOne('user_profile');
$old_cover_photo = $current_profile['cover_photo'] ?? null;

// Handle cover photo upload
if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['cover_photo'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        $response['message'] = 'Invalid file format. Only JPG, PNG, or GIF allowed.';
    } elseif ($file['size'] > $max_size) {
        $response['message'] = 'File size exceeds 5MB limit.';
    } else {
        $new_filename = uniqid() . '_' . basename($file['name']);
        $destination = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Update database with filename only
            $db->where('user_id', $user_id);
            if ($db->update('user_profile', ['cover_photo' => $new_filename])) {
                // Delete old cover photo if it exists
                if ($old_cover_photo && file_exists($upload_dir . $old_cover_photo)) {
                    unlink($upload_dir . $old_cover_photo);
                }
                $response = [
                    'success' => true,
                    'message' => 'Cover photo updated successfully!',
                    'new_photo_url' => $upload_dir . $new_filename // Full path for preview
                ];
            } else {
                $response['message'] = 'Failed to update database: ' . $db->getLastError();
                // Remove uploaded file if database update fails
                if (file_exists($destination)) {
                    unlink($destination);
                }
            }
        } else {
            $response['message'] = 'Failed to move uploaded file.';
        }
    }
// Handle cover photo removal
} elseif (empty($_FILES)) {
    $db->where('user_id', $user_id);
    if ($db->update('user_profile', ['cover_photo' => null])) {
        // Delete old cover photo if it exists
        if ($old_cover_photo && file_exists($upload_dir . $old_cover_photo)) {
            unlink($upload_dir . $old_cover_photo);
        }
        $response = [
            'success' => true,
            'message' => 'Cover photo removed successfully!'
        ];
    } else {
        $response['message'] = 'Failed to remove cover photo: ' . $db->getLastError();
    }
} else {
    $response['message'] = 'No file uploaded or upload error: ' . ($_FILES['cover_photo']['error'] ?? 'Unknown');
}

echo json_encode($response);
exit;
?>