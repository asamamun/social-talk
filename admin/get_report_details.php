<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = new MysqliDb();

$reportId = $_GET['report_id'] ?? null;

if (!$reportId) {
    echo json_encode(['success' => false, 'message' => 'Report ID is required']);
    exit;
}

$db->where('report_id', $reportId);
$report = $db->getOne('reports');

if ($report) {
    $additionalDetails = [];
    if ($report['reported_post_id']) {
        $db->where('post_id', $report['reported_post_id']);
        $post = $db->getOne('posts');
        if ($post) {
            $additionalDetails['post'] = $post;
        }
    } elseif ($report['reported_user_id']) {
        $db->where('user_id', $report['reported_user_id']);
        $user = $db->getOne('users');
        if ($user) {
            $additionalDetails['user'] = $user;
        }
    }
    echo json_encode(['success' => true, 'report' => $report, 'additional_details' => $additionalDetails]);
} else {
    echo json_encode(['success' => false, 'message' => 'Report not found']);
}
?>