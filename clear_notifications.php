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

if (isset($input['clearAll']) && $input['clearAll']) {
    $db->where('user_id', $_SESSION['user_id']);
    $success = $db->delete('notifications');
} else {
    $success = false;
}

echo json_encode(['success' => $success]);
?>