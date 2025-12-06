<?php
session_start();
include 'ketnoi.php';

// Xóa remember token trong database
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
}

// Xóa cookie remember_token
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Hủy session
session_destroy();

header('Location: index.php');
exit();
?>
