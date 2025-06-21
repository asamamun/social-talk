<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
require __DIR__ . "/vendor/autoload.php";

header("Response-Type: application/json");
if($_SERVER["REQUEST_METHOD"] != "POST") {
die(json_encode(array("data" => false, "ad1" => "Invalid Request")));
}



// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = $_SESSION['user_id'];
/*     echo json_encode(array("user_id" => $user_id));
    exit; */

    $db = new MysqliDb();
    // Sanitize input data
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'blood_group' => !empty($_POST['blood_group']) ? $_POST['blood_group'] : null,
        'country' => trim($_POST['country'] ?? ''),
        'address_line1' => trim($_POST['address_line1'] ?? ''),
        'address_line2' => trim($_POST['address_line2'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'state' => trim($_POST['state'] ?? ''),
        'postal_code' => trim($_POST['postal_code'] ?? ''),
        'phone_number' => trim($_POST['phone_number'] ?? ''),
        'bio' => trim($_POST['bio'] ?? ''),
        'date_of_birth' => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
        'gender' => !empty($_POST['gender']) ? $_POST['gender'] : null
    ];

    // Validate required fields
    if (empty($data['first_name']) || empty($data['last_name'])) {
        $error = "First name and last name are required.";
    } else {
        // Handle file uploads
        $upload_dir = "assets/contentimages/{$user_id}/uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                    if (!empty($profile['profile_picture']) && file_exists($profile['profile_picture']) && $profile['profile_picture'] !== $defaultProfilePic) {
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
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $filename = 'cover_' . $user_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['cover_photo']['tmp_name'], $upload_path)) {
                    if (!empty($profile['cover_photo']) && file_exists($profile['cover_photo']) && $profile['cover_photo'] !== $defaultCoverPhoto) {
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

        // Save to database if no errors
        if (empty($error)) {
            try {
                $db->where("user_id", $user_id);
                $existing_profile = $db->getOne("user_profile");

                if ($existing_profile) {
                    $db->where("user_id", $user_id);
                    if ($db->update("user_profile", $data)) {
                        $message = "Profile updated successfully! for user id " . $user_id;
                        echo json_encode(array("status" => "success", "message" => $message));
                       /*  $db->where("user_id", $user_id);
                        $profile = $db->getOne("user_profile");
                        $userProfilePic = !empty($profile['profile_picture']) ? $profile['profile_picture'] : $defaultProfilePic;
                        $userCoverPhoto = !empty($profile['cover_photo']) ? $profile['cover_photo'] : $defaultCoverPhoto;
                        $completionPercentage = calculateCompletionPercentage($profile); */
                    } else {
                        $error = "Failed to update profile. Database error: " . $db->getLastError();
                        echo json_encode(array("status" => "error", "message" => $error));
                    }
                } else {
                    $data['user_id'] = $user_id;
                    if ($db->insert("user_profile", $data)) {
                        $message = "Profile created successfully!";
                        echo json_encode(array("status" => "success", "message" => $message));
                       /*  $db->where("user_id", $user_id);
                        $profile = $db->getOne("user_profile");
                        $userProfilePic = !empty($profile['profile_picture']) ? $profile['profile_picture'] : $defaultProfilePic;
                        $userCoverPhoto = !empty($profile['cover_photo']) ? $profile['cover_photo'] : $defaultCoverPhoto;
                        $completionPercentage = calculateCompletionPercentage($profile); */
                    } else {
                        $error = "Failed to create profile. Database error: " . $db->getLastError();
                        echo json_encode(array("status" => "error", "message" => $error));
                    }
                }
            } catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
                echo json_encode(array("status" => "error", "message" => $error));
            }
        }
    }
}

?>