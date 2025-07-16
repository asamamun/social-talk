<?php
require __DIR__ . '/vendor/autoload.php';
session_start();

if (!isset($_SESSION['logged_in'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$conversation_id = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : null;
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

if (!$conversation_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid conversation']);
    exit;
}

$db = new MysqliDb();

// Get new messages
$db->where('(sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)', 
          [$conversation_id, $_SESSION['user_id'], $_SESSION['user_id'], $conversation_id]);
$db->where('message_id', $last_id, '>');
$db->orderBy('created_at', 'ASC');
$messages = $db->get('messages');

// Mark messages as read if there are new ones
if (!empty($messages)) {
    $db->where('receiver_id', $_SESSION['user_id']);
    $db->where('sender_id', $conversation_id);
    $db->where('is_read', 0);
    $db->where('message_id', $last_id, '>');
    $db->update('messages', ['is_read' => 1]);
}

echo json_encode([
    'success' => true,
    'messages' => $messages
]);
?>