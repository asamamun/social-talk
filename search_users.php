<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

$db = new MysqliDb();
$current_user_id = (int)$_SESSION['user_id'];

// Get POST data
$action = $_POST['action'] ?? '';
$query = trim($_POST['query'] ?? '');

// Validate input
if (empty($query) || strlen($query) < 2) {
    echo json_encode(['users' => []]);
    exit;
}

try {
    if ($action === 'suggestions') {
        // Quick suggestions for dropdown (limit to 5 results)
        $users = searchUsers($db, $query, $current_user_id, 5);
    } elseif ($action === 'full_search') {
        // Full search for modal (limit to 20 results)
        $users = searchUsers($db, $query, $current_user_id, 20, true);
    } else {
        echo json_encode(['error' => 'Invalid action']);
        exit;
    }

    echo json_encode(['users' => $users]);

} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Search failed']);
}

function searchUsers($db, $query, $current_user_id, $limit = 10, $includeProfile = false) {
    // Sanitize query for LIKE search
    $searchQuery = '%' . $query . '%';
    
    // Base query to search users (exclude current user and deleted users)
    $sql = "SELECT u.user_id, u.username, u.email, u.status, u.created_at, u.last_login";
    
    if ($includeProfile) {
        $sql .= ", up.profile_picture, up.first_name, up.last_name, up.bio";
        $sql .= " FROM users u LEFT JOIN user_profile up ON u.user_id = up.user_id";
    } else {
        $sql .= ", up.profile_picture FROM users u LEFT JOIN user_profile up ON u.user_id = up.user_id";
    }
    
    $sql .= " WHERE u.user_id != ? AND u.status != 'deleted'";
    $sql .= " AND (u.username LIKE ? OR u.email LIKE ?";
    
    if ($includeProfile) {
        $sql .= " OR up.first_name LIKE ? OR up.last_name LIKE ?";
    }
    
    $sql .= ") ORDER BY u.username ASC LIMIT ?";
    
    // Prepare parameters
    $params = [$current_user_id, $searchQuery, $searchQuery];
    
    if ($includeProfile) {
        $params[] = $searchQuery;
        $params[] = $searchQuery;
    }
    
    $params[] = $limit;
    
    // Execute query
    $users = $db->rawQuery($sql, $params);
    
    // Process results
    $processedUsers = [];
    foreach ($users as $user) {
        $processedUser = [
            'user_id' => (int)$user['user_id'],
            'username' => htmlspecialchars($user['username']),
            'email' => htmlspecialchars($user['email']),
            'status' => $user['status'],
            'profile_picture' => $user['profile_picture'] ?? 'assets/default-avatar.png',
            'created_at' => $user['created_at'],
            'last_login' => $user['last_login']
        ];
        
        if ($includeProfile) {
            $processedUser['first_name'] = htmlspecialchars($user['first_name'] ?? '');
            $processedUser['last_name'] = htmlspecialchars($user['last_name'] ?? '');
            $processedUser['bio'] = htmlspecialchars($user['bio'] ?? '');
            $processedUser['full_name'] = trim($processedUser['first_name'] . ' ' . $processedUser['last_name']);
        }
        
        $processedUsers[] = $processedUser;
    }
    
    return $processedUsers;
}
?>