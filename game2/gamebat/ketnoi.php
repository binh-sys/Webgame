<?php
// Luôn khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Thông tin kết nối
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "webtintuc";

// Kết nối đến MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra lỗi kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập mã hóa tiếng Việt
$conn->set_charset("utf8mb4");
?>
