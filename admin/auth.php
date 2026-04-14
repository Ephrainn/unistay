<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function adminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireAdmin() {
    if (!adminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
