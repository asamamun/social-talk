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

if (isset($_GET['user_id'])) {
    $db = new MysqliDb();
    $db->where('user_id', $_GET['user_id']);
    $user = $db->getOne('users');
    echo json_encode($user);
}
