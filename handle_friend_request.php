<?php
require __DIR__ . '/vendor/autoload.php';
session_start();

if (!isset($_SESSION['logged_in'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$db = new MysqliDb();
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['action']) || !isset($input['friendId'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$friendId = $input['friendId'];
$currentUserId = $_SESSION['user_id'];

// Find the friendship record
$db->where('(user1_id = ? AND user2_id = ?)', [$friendId, $currentUserId]);
$db->orWhere('(user1_id = ? AND user2_id = ?)', [$currentUserId, $friendId]);
$friendship = $db->getOne('friendships');

if (!$friendship) {
    echo json_encode(['success' => false, 'message' => 'Friend request not found']);
    exit;
}

if ($input['action'] === 'accept') {
    // Update friendship status to accepted
    $db->where('friendship_id', $friendship['friendship_id']);
    $success = $db->update('friendships', [
        'status' => 'accepted',
        'action_user_id' => $currentUserId,
        'updated_at' => $db->now()
    ]);
    
    // Create a notification for the other user
    if ($success) {
        $notificationData = [
            'user_id' => $friendId,
            'type' => 'friend_request',
            'source_id' => $currentUserId,
            'is_read' => 0
        ];
        $db->insert('notifications', $notificationData);
    }
} elseif ($input['action'] === 'decline') {
    // Delete the friendship record
    $db->where('friendship_id', $friendship['friendship_id']);
    $success = $db->delete('friendships');
} else {
    $success = false;
}

echo json_encode(['success' => $success]);
?>