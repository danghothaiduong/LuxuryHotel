<?php
session_start();
include('connect_db.php');
include('header.php');


$error = '';
if(isset($_POST['them_nguoi_dung'])){
    $ho_ten = $_POST['ho_ten'];
    $email = $_POST['email'];
    $sdt = $_POST['so_dien_thoai'];
    $mat_khau = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
    $vai_tro = $_POST['vai_tro'];

    $stmt = $conn->prepare("INSERT INTO nguoi_dung (ho_ten, email, so_dien_thoai, mat_khau, vai_tro, ngay_tao) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $ho_ten, $email, $sdt, $mat_khau, $vai_tro);
    if($stmt->execute()){
        $_SESSION['success'] = "Thêm người dùng thành công!";
        header("Location: quan_ly_nguoi_dung.php");
        exit();
    } else {
        $error = "Thêm người dùng thất bại!";
    }
}
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Thêm người dùng</h2>
    <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Họ tên</label>
            <input type="text" name="ho_ten" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Số điện thoại</label>
            <input type="text" name="so_dien_thoai" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Mật khẩu</label>
            <input type="password" name="mat_khau" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Vai trò</label>
            <select name="vai_tro" class="form-select">
                <option value="nhan_vien">Nhân viên</option>
                <option value="admin">Quản lý</option>
            </select>
        </div>
        <button type="submit" name="them_nguoi_dung" class="btn btn-success">Thêm người dùng</button>
    </form>
</div>

<?php include('footer.php'); ?>
