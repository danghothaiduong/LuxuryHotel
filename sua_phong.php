<?php
session_start();
include('connect_db.php');
include('header.php');



$error = '';
$success = '';

// Lấy id phòng từ GET
if(!isset($_GET['id'])){
    header("Location: quan_ly_phong.php");
    exit();
}
$id = intval($_GET['id']);

// Lấy thông tin phòng hiện tại
$stmt = $conn->prepare("SELECT * FROM phong WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$phong = $res->fetch_assoc();
if(!$phong){
    $_SESSION['error'] = "Phòng không tồn tại!";
    header("Location: quan_ly_phong.php");
    exit();
}

// Lấy danh sách loại phòng
$loai_result = $conn->query("SELECT * FROM loai_phong");
$loai_phongs = $loai_result->fetch_all(MYSQLI_ASSOC);

// Xử lý submit
if(isset($_POST['sua_phong'])){
    $ma_phong = $_POST['ma_phong'];
    $id_loai = $_POST['id_loai'];
    $suc_chua = intval($_POST['suc_chua']);
    $gia_co_ban = floatval($_POST['gia_co_ban']);
    $trang_thai = $_POST['trang_thai'];

    $stmt_update = $conn->prepare("UPDATE phong SET ma_phong=?, id_loai=?, suc_chua=?, gia_co_ban=?, trang_thai=? WHERE id=?");
    $stmt_update->bind_param("siidsi", $ma_phong, $id_loai, $suc_chua, $gia_co_ban, $trang_thai, $id);

    if($stmt_update->execute()){
        $success = "Cập nhật phòng thành công!";
        // Cập nhật lại biến $phong để hiển thị
        $phong['ma_phong'] = $ma_phong;
        $phong['id_loai'] = $id_loai;
        $phong['suc_chua'] = $suc_chua;
        $phong['gia_co_ban'] = $gia_co_ban;
        $phong['trang_thai'] = $trang_thai;
    } else {
        $error = "Cập nhật thất bại!";
    }
}
?>

<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">Sửa thông tin phòng</h2>

    <?php if($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3">
        <div class="col-md-2">
            <label>Mã phòng</label>
            <input type="text" name="ma_phong" class="form-control" value="<?= htmlspecialchars($phong['ma_phong']) ?>" required>
        </div>
        <div class="col-md-3">
            <label>Loại phòng</label>
            <select name="id_loai" class="form-control" required>
                <?php foreach($loai_phongs as $loai): ?>
                <option value="<?= $loai['id'] ?>" <?= $phong['id_loai'] == $loai['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($loai['ten_loai']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label>Sức chứa</label>
            <input type="number" name="suc_chua" class="form-control" min="1" value="<?= $phong['suc_chua'] ?>" required>
        </div>
        <div class="col-md-2">
            <label>Giá cơ bản</label>
            <input type="number" name="gia_co_ban" class="form-control" min="0" step="1000" value="<?= $phong['gia_co_ban'] ?>" required>
        </div>
        <div class="col-md-2">
            <label>Trạng thái</label>
            <select name="trang_thai" class="form-control" required>
                <option value="san_sang" <?= $phong['trang_thai']=='san_sang' ? 'selected' : '' ?>>Sẵn sàng</option>
                <option value="bao_tri" <?= $phong['trang_thai']=='bao_tri' ? 'selected' : '' ?>>Bảo trì</option>
            </select>
        </div>
        <div class="col-md-1 align-self-end">
            <button type="submit" name="sua_phong" class="btn btn-success w-100">Cập nhật</button>
        </div>
    </form>
</div>

<?php include('footer.php'); ?>
