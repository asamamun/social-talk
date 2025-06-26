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
/* if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
} */
if (isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    $post_id = intval($_POST['post_id']);
    $comment = trim($_POST['comment']);
/*     echo json_encode(['status' => 'success', 'post_id' => $post_id, 'comment' => $comment]);
    exit(); */

    if (!empty($comment)) {
        $comment_data = array(
            'post_id' => $post_id,
            'user_id' => intval($_SESSION['user_id']),
            'content' => $comment       
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

    $comments_query = "
        SELECT 
            c.comment_id,
            c.content,
            c.created_at,
            u.username,
            up.profile_picture
        FROM comments c
        JOIN users u ON c.user_id = u.user_id
        LEFT JOIN user_profile up ON c.user_id = up.user_id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC
        LIMIT 10
    ";
    return $db->rawQuery($comments_query, array($post_id));
    /*
    function getPostComments($db, $post_id)
{
    $comments_query = "
        SELECT 
            c.comment_id,
            c.content,
            c.created_at,
            u.username,
            up.profile_picture
        FROM comments c
        JOIN users u ON c.user_id = u.user_id
        LEFT JOIN user_profile up ON c.user_id = up.user_id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC
        LIMIT 10
    ";
    return $db->rawQuery($comments_query, array($post_id));
}
    */

}
