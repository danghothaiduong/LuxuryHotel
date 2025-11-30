<?php
session_start();
include('connect_db.php');
include('header.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Lấy thông tin hiện tại
$stmt = $conn->prepare("SELECT ho_ten, email, so_dien_thoai FROM nguoi_dung WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Cập nhật thông tin cá nhân
if(isset($_POST['update_info'])){
    $ho_ten = trim($_POST['ho_ten']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']);

    $stmt = $conn->prepare("UPDATE nguoi_dung SET ho_ten=?, so_dien_thoai=? WHERE id=?");
    $stmt->bind_param("ssi", $ho_ten, $so_dien_thoai, $user_id);
    if($stmt->execute()){
        $success = "Cập nhật thông tin thành công!";
        $_SESSION['ho_ten'] = $ho_ten; // Cập nhật session
    } else {
        $error = "Cập nhật thất bại!";
    }
}

// Đổi mật khẩu
if(isset($_POST['change_password'])){
    $mat_khau_cu = $_POST['mat_khau_cu'];
    $mat_khau_moi = $_POST['mat_khau_moi'];
    $mat_khau_xac_nhan = $_POST['mat_khau_xac_nhan'];

    // Lấy mật khẩu cũ từ DB
    $stmt = $conn->prepare("SELECT mat_khau FROM nguoi_dung WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current = $result->fetch_assoc();

    if($current['mat_khau'] !== hash('sha256', $mat_khau_cu)){
        $error = "Mật khẩu cũ không đúng!";
    } elseif ($mat_khau_moi !== $mat_khau_xac_nhan){
        $error = "Mật khẩu mới không trùng khớp!";
    } else {
        $stmt = $conn->prepare("UPDATE nguoi_dung SET mat_khau=? WHERE id=?");
        $new_hash = hash('sha256', $mat_khau_moi);
        $stmt->bind_param("si", $new_hash, $user_id);
        if($stmt->execute()){
            $success = "Đổi mật khẩu thành công!";
        } else {
            $error = "Đổi mật khẩu thất bại!";
        }
    }
}
?>

<div class="container mt-5">
    <h3 class="mb-4">Thông tin cá nhân</h3>

    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Cập nhật thông tin cá nhân -->
        <div class="col-md-6">
            <form method="post">
                <div class="mb-3">
                    <label>Họ tên</label>
                    <input type="text" name="ho_ten" class="form-control" value="<?= htmlspecialchars($user['ho_ten']) ?>" required>
                </div>
                <div class="mb-3">
                    <label>Email (không thể thay đổi)</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label>Số điện thoại</label>
                    <input type="text" name="so_dien_thoai" class="form-control" value="<?= htmlspecialchars($user['so_dien_thoai']) ?>">
                </div>
                <button type="submit" name="update_info" class="btn btn-primary">Cập nhật thông tin</button>
            </form>
        </div>

        <!-- Đổi mật khẩu -->
        <div class="col-md-6">
            <form method="post">
                <div class="mb-3">
                    <label>Mật khẩu cũ</label>
                    <input type="password" name="mat_khau_cu" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Mật khẩu mới</label>
                    <input type="password" name="mat_khau_moi" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Xác nhận mật khẩu mới</label>
                    <input type="password" name="mat_khau_xac_nhan" class="form-control" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-warning">Đổi mật khẩu</button>
            </form>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
