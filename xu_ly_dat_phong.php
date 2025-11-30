<?php
session_start();
include('connect_db.php');
include('header.php');

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$ho_ten = $_SESSION['ho_ten'] ?? 'Khách';
$stmt_user = $conn->prepare("SELECT ho_ten, so_dien_thoai FROM nguoi_dung WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$res_user = $stmt_user->get_result();
$user = $res_user->fetch_assoc();

$sdt_lien_lac = $user['so_dien_thoai'] ?? 'Chưa có';

if(isset($_POST['dat_phong_xac_nhan'])){
    $phong_id = intval($_POST['phong_id']);
    $ngay_nhan = $_POST['ngay_nhan'];
    $ngay_tra = $_POST['ngay_tra'];
    $so_khach = intval($_POST['so_khach']);
    $ghi_chu = $_POST['ghi_chu'] ?? '';

    $stmt_room = $conn->prepare("SELECT * FROM phong WHERE id = ?");
    $stmt_room->bind_param("i", $phong_id);
    $stmt_room->execute();
    $res_room = $stmt_room->get_result();
    $room = $res_room->fetch_assoc();

    if(!$room){
        $_SESSION['error'] = "Phòng không tồn tại!";
        header("Location: dat_phong.php");
        exit();
    }

    $so_dem = (strtotime($ngay_tra) - strtotime($ngay_nhan)) / (60*60*24);
    if($so_dem < 1) $so_dem = 1;
    $tong_tien = $room['gia_co_ban'] * $so_dem;

    $stmt_insert = $conn->prepare("INSERT INTO dat_phong (id_nguoi_dung, ngay_nhan, ngay_tra, so_khach, tong_tien, ghi_chu, trang_thai, ngay_tao) VALUES (?, ?, ?, ?, ?, ?, 'cho_xac_nhan', NOW())");
    $stmt_insert->bind_param("issids", $user_id, $ngay_nhan, $ngay_tra, $so_khach, $tong_tien, $ghi_chu);
    if($stmt_insert->execute()){
        $dat_phong_id = $stmt_insert->insert_id;
        $stmt_detail = $conn->prepare("INSERT INTO dat_phong_chi_tiet (id_dat_phong, id_phong, so_dem, gia_mot_dem) VALUES (?, ?, ?, ?)");
        $gia_mot_dem = $room['gia_co_ban'];
        $stmt_detail->bind_param("iiid", $dat_phong_id, $phong_id, $so_dem, $gia_mot_dem);
        $stmt_detail->execute();

        
		echo "<script>
            alert('Đặt phòng thành công! Vui lòng chờ xác nhận.');
            window.location.href='dat_phong.php';
        </script>";
        exit();
    } else {
        echo "<script>
            alert('Đặt phòng thất bại!');
            window.location.href='dat_phong.php';
        </script>";
        exit();
    }
}


if(!isset($_POST['phong_id'], $_POST['ngay_nhan'], $_POST['ngay_tra'], $_POST['so_khach'])){
    $_SESSION['error'] = "Dữ liệu không hợp lệ!";
    header("Location: dat_phong.php");
    exit();
}

$phong_id = intval($_POST['phong_id']);
$ngay_nhan = $_POST['ngay_nhan'];
$ngay_tra = $_POST['ngay_tra'];
$so_khach = intval($_POST['so_khach']);

$stmt = $conn->prepare("
    SELECT p.*, lp.ten_loai, a.duong_dan 
    FROM phong p
    LEFT JOIN loai_phong lp ON p.id_loai = lp.id
    LEFT JOIN anh_phong a ON p.id = a.id_phong AND a.anh_chinh = 1
    WHERE p.id = ?
");
$stmt->bind_param("i", $phong_id);
$stmt->execute();
$res = $stmt->get_result();
$room = $res->fetch_assoc();

if(!$room){
    $_SESSION['error'] = "Phòng không tồn tại!";
    header("Location: dat_phong.php");
    exit();
}

$so_dem = (strtotime($ngay_tra) - strtotime($ngay_nhan)) / (60*60*24);
if($so_dem < 1) $so_dem = 1;
$tong_tien = $room['gia_co_ban'] * $so_dem;

?>

<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">Xác nhận đặt phòng</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Thông tin khách hàng</h5>
            <p><strong>Người đặt:</strong> <?= htmlspecialchars($ho_ten) ?></p>
            <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($sdt_lien_lac) ?></p>
            <p><strong>Phòng:</strong> <?= htmlspecialchars($room['ma_phong']) ?> (<?= htmlspecialchars($room['ten_loai']) ?>)</p>
            <p><strong>Ngày nhận:</strong> <?= htmlspecialchars($ngay_nhan) ?></p>
            <p><strong>Ngày trả:</strong> <?= htmlspecialchars($ngay_tra) ?></p>
            <p><strong>Số đêm:</strong> <?= $so_dem ?></p>
            <p class="fw-bold text-primary">Tổng tiền: <?= number_format($tong_tien,0,',','.') ?>₫ (Chưa bao gồm phụ thu và các chi phí khác)</p>

            <hr>
            <form method="POST" action="xu_ly_dat_phong.php">
                <input type="hidden" name="phong_id" value="<?= $phong_id ?>">
                <input type="hidden" name="ngay_nhan" value="<?= htmlspecialchars($ngay_nhan) ?>">
                <input type="hidden" name="ngay_tra" value="<?= htmlspecialchars($ngay_tra) ?>">
                <input type="hidden" name="so_khach" value="<?= htmlspecialchars($so_khach) ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold">Ghi chú (nếu có)</label>
                    <textarea name="ghi_chu" class="form-control" rows="4" placeholder=""></textarea>
                </div>

                <button type="submit" name="dat_phong_xac_nhan" class="btn btn-success w-100">
                    Xác nhận đặt phòng
                </button>
            </form>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
