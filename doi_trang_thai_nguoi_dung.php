<?php
session_start();
include('connect_db.php');

if(!isset($_GET['id'], $_GET['status'])){
    header("Location: quan_ly_nguoi_dung.php");
    exit();
}

$id = intval($_GET['id']);
$status = $_GET['status']; // 'hoat_dong' hoặc 'khoa'

$stmt = $conn->prepare("UPDATE nguoi_dung SET trang_thai = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if($stmt->execute()){
    $_SESSION['success'] = "Cập nhật trạng thái người dùng thành công!";
}else{
    $_SESSION['error'] = "Cập nhật thất bại!";
}

header("Location: quan_ly_nguoi_dung.php");
exit();
