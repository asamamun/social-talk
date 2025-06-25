<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

if (!isset($_SESSION['logged_in']) || !isset($_POST['recipient_id'])) {
    header("Location: login.php");
    exit;
}

$db = new MysqliDb();
$sender_id = $_SESSION['user_id'];
$recipient_id = (int)$_POST['recipient_id'];

// Check if friendship already exists
$db->where('(user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)', 
          [$sender_id, $recipient_id, $recipient_id, $sender_id]);
$existing = $db->getOne('friendships');

if (!$existing) {
    $data = [
        'user1_id' => $sender_id,
        'user2_id' => $recipient_id,
        'status' => 'pending',
        'action_user_id' => $sender_id
    ];
    $db->insert('friendships', $data);
    
    // Create notification
    $notification_data = [
        'user_id' => $recipient_id,
        'type' => 'friend_request',
        'source_id' => $sender_id,
        'created_at' => $db->now()
    ];
    $db->insert('notifications', $notification_data);
}

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'find-friend.php'));
exit;
?>