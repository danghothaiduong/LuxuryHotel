<?php
// Thông tin kết nối
$servername = "localhost";   // Server MySQL (thường là localhost)
$username = "root";          // Tài khoản MySQL
$password = "vertrigo";              // Mật khẩu MySQL (nếu có)
$dbname = "khach_san"; // Tên cơ sở dữ liệu

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối MySQL thất bại: " . $conn->connect_error);
}

// Thiết lập bộ ký tự UTF8
$conn->set_charset("utf8");
?>
