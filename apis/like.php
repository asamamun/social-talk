<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to prevent unintended output
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';

// Set JSON content type header
header('Content-Type: application/json; charset=UTF-8');

// Stop if not an AJAX call
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    ob_end_flush();
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You need to login first']);
    ob_end_flush();
    exit;
}

// Read POST data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['post_id']) || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    ob_end_flush();
    exit;
}

try {
    // Initialize MySQLi (replace with your database credentials)
    $mysqli = new mysqli(settings()['hostname'], settings()['user'], settings()['password'], settings()['database']);
    
    // Check for connection errors
    if ($mysqli->connect_error) {
        throw new Exception('Database connection failed: ' . $mysqli->connect_error);
    }

    $user_id = (int)$_SESSION['user_id'];
    $post_id = (int)$input['post_id'];

    // Check if the user has already liked the post
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM likes WHERE user_id = ? AND post_id = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
    }
    
    $stmt->bind_param('ii', $user_id, $post_id);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    // Fetch the count (equivalent to PDO's fetchColumn)
    $result = $stmt->get_result();
    $exists = $result->fetch_row()[0]; // Get the first column of the first row

    if ($exists) {
        // Unlike: Delete the like
        $stmt = $mysqli->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $mysqli->error);
        }
        $stmt->bind_param('ii', $user_id, $post_id);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        echo json_encode(['success' => true, 'status' => 'unliked', 'message' => 'Unliked']);
    } else {
        // Like: Insert the like
        $stmt = $mysqli->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $mysqli->error);
        }
        $stmt->bind_param('ii', $user_id, $post_id);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        echo json_encode(['success' => true, 'status' => 'liked', 'message' => 'Liked']);
    }

    $stmt->close();
    $mysqli->close();
} catch (Exception $e) {
    // Handle errors
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Clear buffer and send response
ob_end_flush();
?>