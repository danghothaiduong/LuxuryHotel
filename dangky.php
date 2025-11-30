<?php
session_start();
include('connect_db.php');
include('header.php');

$error = '';
$success = '';

if(isset($_POST['register'])){
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']);
    $mat_khau = $_POST['mat_khau'];
    $mat_khau2 = $_POST['mat_khau2'];

    // Kiểm tra mật khẩu trùng khớp
    if($mat_khau !== $mat_khau2){
        $error = "Mật khẩu không khớp!";
    } else {
        // Kiểm tra email đã tồn tại chưa
        $stmt = $conn->prepare("SELECT * FROM nguoi_dung WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){
            $error = "Email này đã được đăng ký!";
        } else {
            // Hash mật khẩu SHA-256
            $hashed_password = hash('sha256', $mat_khau);

            // Chèn dữ liệu
            $stmt = $conn->prepare("INSERT INTO nguoi_dung (ho_ten, email, so_dien_thoai, mat_khau, vai_tro, trang_thai) VALUES (?, ?, ?, ?, 'khach', 'hoat_dong')");
            $stmt->bind_param("ssss", $ho_ten, $email, $so_dien_thoai, $hashed_password);

            if($stmt->execute()){
                $success = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
            } else {
                $error = "Đăng ký thất bại! Vui lòng thử lại.";
            }
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h3 class="text-center mb-3">Đăng ký tài khoản</h3>

            <?php if($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label>Họ và tên</label>
                    <input type="text" name="ho_ten" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Số điện thoại</label>
                    <input type="text" name="so_dien_thoai" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Mật khẩu</label>
                    <input type="password" name="mat_khau" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Nhập lại mật khẩu</label>
                    <input type="password" name="mat_khau2" class="form-control" required>
                </div>
                <button type="submit" name="register" class="btn btn-success w-100">Đăng ký</button>
            </form>

            <p class="mt-3 text-center">
                Đã có tài khoản? <a href="login.php">Đăng nhập</a>
            </p>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
