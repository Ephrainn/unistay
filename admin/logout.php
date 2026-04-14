<?php
require_once 'auth.php';
unset($_SESSION['admin_id'], $_SESSION['admin_name']);
session_destroy();
header('Location: login.php');
exit;
