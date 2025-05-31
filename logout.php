<?php
//session_start(); if no session found
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
session_unset();
session_destroy();
header("Location: login.php");
exit;
?>