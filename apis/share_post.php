<?php
session_start();
require_once '../includes/db.php'; // Adjust path as needed
require_once '../vendor/autoload.php'; // For MysqliDb

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$db = new MysqliDb();

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['post_id'])) {
    $original_post_id = $input['post_id'];
    $user_id = $_SESSION['user_id'];

    // Fetch the original post details
    $db->where('post_id', $original_post_id);
    $original_post = $db->getOne('posts');

    if ($original_post) {
        // Check if the user has already shared this post
        $db->where('user_id', $user_id);
        $db->where('original_post_id', $original_post_id);
        $existing_share = $db->getOne('posts');

        if ($existing_share) {
            echo json_encode(['success' => false, 'message' => 'You have already shared this post.']);
            exit;
        }

        // Create a new post entry for the shared post
        $data = [
            'user_id' => $user_id,
            'content' => $original_post['content'], // Copy original content
            'images' => $original_post['images'],   // Copy original images
            'visibility' => 'public', // Shared posts are public by default, or you can make them follow original visibility
            'original_post_id' => $original_post_id,
            'created_at' => $db->now(),
            'updated_at' => $db->now()
        ];

        if ($db->insert('posts', $data)) {
            $new_post_id = $db->getInsertId();

            // Notify the original post author
            if ($original_post['user_id'] != $user_id) { // Don't notify if sharing own post
                $notification_data = [
                    'user_id' => $original_post['user_id'],
                    'type' => 'share',
                    'source_id' => $new_post_id, // Link to the new shared post
                    'created_at' => $db->now()
                ];
                $db->insert('notifications', $notification_data);
            }

            echo json_encode(['success' => true, 'message' => 'Post shared successfully!', 'new_post_id' => $new_post_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to share post: ' . $db->getLastError()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Original post not found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>