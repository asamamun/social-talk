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

if (isset($input['markAllRead']) && $input['markAllRead']) {
    // Mark all notifications as read
    $db->where('user_id', $_SESSION['user_id']);
    $db->where('is_read', 0);
    $success = $db->update('notifications', ['is_read' => 1]);
} elseif (isset($input['notificationId'])) {
    // Mark a specific notification as read
    $db->where('notification_id', $input['notificationId']);
    $db->where('user_id', $_SESSION['user_id']);
    $success = $db->update('notifications', ['is_read' => 1]);
} else {
    $success = false;
}

echo json_encode(['success' => $success]);
?>