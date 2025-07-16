<?php
// Ensure no output before JSON
ob_start();
session_start();
require __DIR__ . '/../vendor/autoload.php';

// Set error reporting to log errors, not display them
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_error.log'); // Log errors to a file

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Clear any accidental output before sending JSON
ob_clean();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    $response['message'] = 'User not logged in.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
    $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
    $reporter_id = $_SESSION['user_id'];

    if (!$post_id) {
        $response['message'] = 'Invalid post ID.';
        echo json_encode($response);
        exit;
    }

    if (empty($reason)) {
        $response['message'] = 'Report reason cannot be empty.';
        echo json_encode($response);
        exit;
    }

    $db = new MysqliDb();

    // Check if the post exists
    $db->where('post_id', $post_id);
    $post = $db->getOne('posts');
    if (!$post) {
        $response['message'] = 'Post not found.';
        echo json_encode($response);
        exit;
    }
    $reported_user_id = $post['user_id'];

    // Prevent reporting own post
    if ($reporter_id == $reported_user_id) {
        $response['message'] = 'You cannot report your own post.';
        echo json_encode($response);
        exit;
    }

    // Check if the user has already reported this post
    $db->where('reported_post_id', $post_id);
    $db->where('reporter_id', $reporter_id);
    $existing_report = $db->getOne('reports');

    if ($existing_report) {
        $response['message'] = 'You have already reported this post.';
        echo json_encode($response);
        exit;
    }

    $data = [
        'reported_post_id' => $post_id,
        'reporter_id' => $reporter_id,
        'reported_user_id' => $reported_user_id,
        'reason' => $reason
    ];

    if ($db->insert('reports', $data)) {
        $response['success'] = true;
        $response['message'] = 'Post reported successfully.';
    } else {
        $response['message'] = 'Database error: ' . $db->getLastError();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>