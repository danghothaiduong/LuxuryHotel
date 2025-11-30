<?php
session_start();
include('connect_db.php');
include('header.php');



$success = '';
$error = '';

// Lấy danh sách các phòng đang ở hoặc chờ thanh toán
$phongs = [];
$sql_phong = "
    SELECT dp.id AS dat_phong_id, p.ma_phong, nd.ho_ten, dp.ngay_nhan, dp.ngay_tra, dp.so_khach, dp.tong_tien AS tien_phong
    FROM dat_phong dp
    JOIN phong p ON dp.id = p.id
    JOIN nguoi_dung nd ON dp.id_nguoi_dung = nd.id
    WHERE dp.trang_thai IN ('dang_o')
    ORDER BY dp.ngay_nhan DESC
";
$res_phong = $conn->query($sql_phong);
if($res_phong){
    $phongs = $res_phong->fetch_all(MYSQLI_ASSOC);
} else {
    $error = "Lỗi truy vấn phòng: " . $conn->error;
}

// Xử lý submit thanh toán
if(isset($_POST['thanh_toan'])){
    $dat_phong_id = intval($_POST['dat_phong_id']);
    $phuong_thuc = $_POST['phuong_thuc'];

    // Lấy tiền phòng
    $stmt_dp = $conn->prepare("SELECT tong_tien FROM dat_phong WHERE id = ?");
    $stmt_dp->bind_param("i", $dat_phong_id);
    $stmt_dp->execute();
    $tien_phong = $stmt_dp->get_result()->fetch_assoc()['tong_tien'] ?? 0;

    // Lấy tiền dịch vụ
    $stmt_dv = $conn->prepare("SELECT SUM(so_luong * gia) AS tien_dich_vu FROM dat_phong_dich_vu WHERE id_dat_phong = ?");
    $stmt_dv->bind_param("i", $dat_phong_id);
    $stmt_dv->execute();
    $tien_dich_vu = $stmt_dv->get_result()->fetch_assoc()['tien_dich_vu'] ?? 0;

    $tong_tien = $tien_phong + $tien_dich_vu;

    // Sinh mã giao dịch
    $ma_gd = 'GD'.time().rand(100,999);

    // Thêm vào bảng thanh_toan
	$stmt_tt = $conn->prepare("INSERT INTO thanh_toan (id_dat_phong, so_tien, phuong_thuc, trang_thai, ma_giao_dich, ngay_thanh_toan) VALUES (?, ?, ?, 'da_thanh_toan', ?, NOW())");
	$stmt_tt->bind_param("idss", $dat_phong_id, $tong_tien, $phuong_thuc, $ma_gd);
	if($stmt_tt->execute()){
		// Update trạng thái dat_phong -> da_tra
		$stmt_up = $conn->prepare("UPDATE dat_phong SET trang_thai='da_tra' WHERE id = ?");
		$stmt_up->bind_param("i", $dat_phong_id);
		$stmt_up->execute();

		$success = "Thanh toán thành công! Tổng tiền: ".number_format($tong_tien,0,',','.')."₫";
	} else {
		$error = "Thanh toán thất bại: ".$conn->error;
	}

	
}
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Thanh toán</h2>

    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3">
        <div class="col-md-4">
            <label>Chọn phòng</label>
            <select name="dat_phong_id" class="form-control" required onchange="this.form.submit()">
                <option value="">-- Chọn phòng --</option>
                <?php foreach($phongs as $phong): ?>
                    <option value="<?= $phong['dat_phong_id'] ?>" <?= (isset($_POST['dat_phong_id']) && $_POST['dat_phong_id']==$phong['dat_phong_id'])?'selected':'' ?>>
                        <?= htmlspecialchars($phong['ma_phong']) ?> (<?= htmlspecialchars($phong['ho_ten']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if(isset($_POST['dat_phong_id']) && $_POST['dat_phong_id'] != ''): 
            $dat_phong_id = intval($_POST['dat_phong_id']);

            // Lấy tiền phòng
            $tien_phong = 0;
            foreach($phongs as $p) if($p['dat_phong_id']==$dat_phong_id) $tien_phong=$p['tien_phong'];

            // Lấy tiền dịch vụ
            $stmt_dv = $conn->prepare("SELECT d.ten_dich_vu, d.id, dpdv.so_luong, dpdv.gia FROM dat_phong_dich_vu dpdv JOIN dich_vu d ON dpdv.id_dich_vu=d.id WHERE dpdv.id_dat_phong=?");
            $stmt_dv->bind_param("i",$dat_phong_id);
            $stmt_dv->execute();
            $res_dv = $stmt_dv->get_result();
            $dich_vus = $res_dv->fetch_all(MYSQLI_ASSOC);
            $tien_dich_vu = 0;
            foreach($dich_vus as $dv) $tien_dich_vu += $dv['so_luong']*$dv['gia'];
            $tong_tien = $tien_phong + $tien_dich_vu;
        ?>
        <div class="col-md-12">
            <h5>Chi tiết thanh toán</h5>
            <p>Tiền phòng: <?= number_format($tien_phong,0,',','.') ?>₫</p>
            <p>Dịch vụ:</p>
            <ul>
                <?php foreach($dich_vus as $dv): ?>
                    <li><?= htmlspecialchars($dv['ten_dich_vu']) ?> x <?= $dv['so_luong'] ?> = <?= number_format($dv['so_luong']*$dv['gia'],0,',','.') ?>₫</li>
                <?php endforeach; ?>
            </ul>
            <p class="fw-bold text-primary">Tổng tiền: <?= number_format($tong_tien,0,',','.') ?>₫</p>
        </div>

        <div class="col-md-4">
            <label>Phương thức thanh toán</label>
            <select name="phuong_thuc" class="form-control" required>
                <option value="tien_mat">Tiền mặt</option>
                <option value="the">Thẻ</option>
                <option value="chuyen_khoan">Chuyển khoản</option>
                <option value="momo">Momo</option>
            </select>
        </div>
        <div class="col-md-2 align-self-end">
            <button type="submit" name="thanh_toan" class="btn btn-success w-100">Thanh toán</button>
        </div>
        <?php endif; ?>
    </form>
</div>

<?php include('footer.php'); ?>
