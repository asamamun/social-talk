<?php
require __DIR__ . '/vendor/autoload.php';
session_start();

if (!isset($_SESSION['logged_in'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$receiver_id = $input['receiver_id'] ?? null;
$content = $input['content'] ?? null;

if (!$receiver_id || !$content) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$db = new MysqliDb();

// Insert the message
$message_id = $db->insert('messages', [
    'sender_id' => $_SESSION['user_id'],
    'receiver_id' => $receiver_id,
    'content' => $content,
    'is_read' => 0,
    'created_at' => $db->now()
]);

if ($message_id) {
    // Create notification for the receiver
    $db->insert('notifications', [
        'user_id' => $receiver_id,
        'type' => 'message',
        'source_id' => $_SESSION['user_id'],
        'is_read' => 0
    ]);
    
    echo json_encode(['success' => true, 'message_id' => $message_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}
?>