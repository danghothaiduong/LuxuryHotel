<?php
session_start();
include('connect_db.php');
include('header.php');

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$ngay_nhan = $_POST['ngay_nhan'] ?? '';
$ngay_tra = $_POST['ngay_tra'] ?? '';
$so_khach = $_POST['so_khach'] ?? 1;

// Xử lý đặt phòng
if(isset($_POST['dat_phong'])){
    $phong_id = intval($_POST['phong_id']);
    $ngay_nhan_form = $_POST['ngay_nhan'];
    $ngay_tra_form = $_POST['ngay_tra'];
    $so_dem = (strtotime($ngay_tra_form) - strtotime($ngay_nhan_form)) / (60*60*24);
    if($so_dem < 1) $so_dem = 1;

    // Lấy giá phòng
    $stmt = $conn->prepare("SELECT gia_co_ban FROM phong WHERE id = ?");
    $stmt->bind_param("i", $phong_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
    $gia_mot_dem = $room['gia_co_ban'];
    $tong_tien = $gia_mot_dem * $so_dem;
}

// Tìm phòng trống
$phongs = [];
$today = date('Y-m-d'); // ngày hiện tại

// Ràng buộc ngày
if ($ngay_nhan && $ngay_tra) {
    if ($ngay_nhan <= $today) {
        $error = 'Ngày nhận phòng phải sau ngày hôm nay!';
        $ngay_nhan = date('Y-m-d', strtotime('+1 day')); // reset ngày nhận
        $ngay_tra = ''; // reset ngày trả
    } elseif ($ngay_tra <= $ngay_nhan) {
        $error = 'Ngày trả phải sau ngày nhận!';
        $ngay_tra = ''; // reset ngày trả
    }
}

if($ngay_nhan && $ngay_tra && $so_khach && !$error){
    $sql = "
        SELECT p.*, lp.ten_loai, a.duong_dan
        FROM phong p
        LEFT JOIN loai_phong lp ON p.id_loai = lp.id
        LEFT JOIN anh_phong a ON p.id = a.id_phong AND a.anh_chinh = 1
        WHERE p.suc_chua >= ? 
        AND p.trang_thai = 'san_sang'
        AND p.id NOT IN (
            SELECT dct.id_phong 
            FROM dat_phong_chi_tiet dct
            JOIN dat_phong dp ON dct.id_dat_phong = dp.id
            WHERE NOT (dp.ngay_tra <= ? OR dp.ngay_nhan >= ?)
        )
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $so_khach, $ngay_nhan, $ngay_tra);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $phongs[] = $row;
    }
}

?>

<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">Đặt phòng</h2>

    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-3">
            <label>Ngày nhận</label>
            <input type="date" name="ngay_nhan" class="form-control" value="<?= htmlspecialchars($ngay_nhan) ?>" required>
        </div>
        <div class="col-md-3">
            <label>Ngày trả</label>
            <input type="date" name="ngay_tra" class="form-control" value="<?= htmlspecialchars($ngay_tra) ?>" required>
        </div>
        <div class="col-md-2">
            <label>Số khách</label>
            <input type="number" name="so_khach" class="form-control" min="1" value="<?= htmlspecialchars($so_khach) ?>" required>
        </div>
        <div class="col-md-2 align-self-end">
            <button type="submit" class="btn btn-primary">Tìm phòng trống</button>
        </div>
    </form>

    <?php if(count($phongs) == 0 && $ngay_nhan && $ngay_tra): ?>
        <p class="text-danger">Không còn phòng trống cho ngày và số khách này!</p>
    <?php endif; ?>

    <div class="row g-4">
        <?php foreach($phongs as $room): ?>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <img src="images/<?= $room['duong_dan'] ? $room['duong_dan'] : 'default_room.jpg' ?>" 
                     class="card-img-top" 
                     alt="Phòng <?= htmlspecialchars($room['ma_phong']) ?>" 
                     style="height:250px; object-fit:cover;">

                <div class="card-body d-flex flex-column">
                    <h5 class="card-title fw-bold"><?= htmlspecialchars($room['ma_phong']) ?></h5>
                    <p class="text-muted">Loại: <?= htmlspecialchars($room['ten_loai']) ?></p>
					<a href="chi_tiet_phong.php?id=<?= $room['id'] ?>" class="btn btn-outline-primary mb-2 w-100">
						Xem chi tiết
					</a>
                    <p class="fw-bold mb-2 text-primary"><?= number_format($room['gia_co_ban'],0,',','.') ?>₫ / đêm</p>

                    <form method="POST" action="xu_ly_dat_phong.php" class="mt-auto">
						<input type="hidden" name="phong_id" value="<?= $room['id'] ?>">
						<input type="hidden" name="ngay_nhan" value="<?= htmlspecialchars($ngay_nhan) ?>">
						<input type="hidden" name="ngay_tra" value="<?= htmlspecialchars($ngay_tra) ?>">
						<input type="hidden" name="so_khach" value="<?= htmlspecialchars($so_khach) ?>">
						<button type="submit" name="chuyen_sang_ghi_chu" class="btn btn-success w-100">Đặt phòng</button>
					</form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include('footer.php'); ?>
