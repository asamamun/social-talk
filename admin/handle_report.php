<?php
$logFile = 'handle_report.log';
file_put_contents($logFile, "Script accessed at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../vendor/autoload.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reportId = $_POST['report_id'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($reportId && $status) {
        $db = new MysqliDb();
        $db->where('report_id', $reportId);
        $result = $db->update('reports', ['status' => $status]);

        $logFile = 'handle_report.log';
        $logData = "Report ID: $reportId, Status: $status, Result: " . ($result ? 'Success' : 'Failure') . "\n";
        file_put_contents($logFile, $logData, FILE_APPEND);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update report.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}
