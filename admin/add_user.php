<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new MysqliDb();
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $data = [
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'password_hash' => $password,
        'role' => $_POST['role']
    ];
    if ($db->insert('users', $data)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
