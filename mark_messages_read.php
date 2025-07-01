<?php
require __DIR__ . '/vendor/autoload.php';
session_start();

if (!isset($_SESSION['logged_in'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$conversation_id = $input['conversation_id'] ?? null;

if (!$conversation_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid conversation']);
    exit;
}

$db = new MysqliDb();

// Mark messages as read
$db->where('receiver_id', $_SESSION['user_id']);
$db->where('sender_id', $conversation_id);
$db->where('is_read', 0);
$success = $db->update('messages', ['is_read' => 1]);

echo json_encode(['success' => (bool)$success]);
?>