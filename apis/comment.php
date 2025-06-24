<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';
$db = new MysqliDb();
// Set JSON content type header
header('Content-Type: application/json; charset=UTF-8');

// Stop if not an AJAX call
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}
if (isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    $post_id = intval($_POST['post_id']);
    $comment = trim($_POST['comment']);

    if (!empty($comment)) {
        $comment_data = array(
            'post_id' => $post_id,
            'user_id' => intval($_SESSION['user_id']),
            'content' => $comment,
            'created_at' => date('Y-m-d H:i:s')
        );
        $db->insert('comments', $comment_data);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Comment cannot be empty']);
    }
    exit();
}

if (isset($_POST['action']) && $_POST['action'] === 'load_comments') {
    $post_id = intval($_POST['post_id']);
    $comments = getPostComments($db, $post_id);
    echo json_encode(['status' => 'success', 'comments' => $comments]);
    exit();
}

function getPostComments($db, $post_id){

}
