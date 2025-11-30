<?php
session_start();
include('connect_db.php');
include('header.php'); // include header

// Nếu người dùng đã đăng nhập, chuyển hướng sang trang index
if(isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$error = '';
if(isset($_POST['login'])){
    $email = $_POST['email'];
    $mat_khau = $_POST['mat_khau'];

    // Lấy thông tin người dùng theo email
    $stmt = $conn->prepare("SELECT * FROM nguoi_dung WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if($user){
        // Kiểm tra mật khẩu SHA-256
        if($user['mat_khau'] === hash('sha256', $mat_khau)){
            if($user['trang_thai'] === 'khoa'){
                $error = "Tài khoản đang bị khóa!";
            } else {
                // Lưu session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['ho_ten'] = $user['ho_ten'];
                $_SESSION['vai_tro'] = $user['vai_tro'];

                // Chuyển hướng về trang index, header sẽ hiển thị menu theo vai trò
                header("Location: index.php");
                exit();
            }
        } else {
            $error = "Sai mật khẩu!";
        }
    } else {
        $error = "Email không tồn tại!";
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h3 class="text-center mb-3">Đăng nhập</h3>
            <?php if($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Mật khẩu</label>
                    <input type="password" name="mat_khau" class="form-control" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary w-100">Đăng nhập</button>
            </form>
            <p class="mt-3 text-center">
                Chưa có tài khoản? <a href="dangky.php">Đăng ký</a>
            </p>
        </div>
    </div>
</div>

<?php include('footer.php'); // include footer ?>
