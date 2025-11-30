<?php
session_start();
include('connect_db.php');
include('header.php');

// Kiểm tra quyền nhân viên
if(!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] != 'nhan_vien'){
    echo "<script>alert('Chỉ nhân viên mới được truy cập trang này'); window.location.href='index.php';</script>";
    exit();
}

$success = '';
$error = '';

// Xử lý thay đổi trạng thái
if(isset($_POST['update_trang_thai'])){
    $dp_id = intval($_POST['dat_phong_id']);
    $trang_thai_moi = $_POST['trang_thai'];

    // Lấy trạng thái hiện tại từ DB
    $stmt_check = $conn->prepare("SELECT trang_thai FROM dat_phong WHERE id=?");
    $stmt_check->bind_param("i", $dp_id);
    $stmt_check->execute();
    $current = $stmt_check->get_result()->fetch_assoc();
    $trang_thai_hien_tai = $current['trang_thai'];

    // Quy tắc: không đổi từ da_tra và không đổi sang da_tra
    if($trang_thai_hien_tai == 'da_tra' || $trang_thai_moi == 'da_tra'){
        $error = "Không được thay đổi sang hoặc từ trạng thái 'Đã trả'";
    } else {
        $stmt = $conn->prepare("UPDATE dat_phong SET trang_thai=? WHERE id=?");
        $stmt->bind_param("si", $trang_thai_moi, $dp_id);
        if($stmt->execute()){
            $success = "Cập nhật trạng thái thành công!";
        } else {
            $error = "Cập nhật thất bại: " . $conn->error;
        }
    }
}


// Xóa đặt phòng
if(isset($_GET['xoa_id'])){
    $xoa_id = intval($_GET['xoa_id']);
    $stmt_del = $conn->prepare("DELETE FROM dat_phong WHERE id = ?");
    $stmt_del->bind_param("i", $xoa_id);
    if($stmt_del->execute()){
        $success = "Xóa đặt phòng thành công!";
    } else {
        $error = "Xóa thất bại: ".$conn->error;
    }
}

// Lấy danh sách đặt phòng
$sql = "
    SELECT dp.id, nd.ho_ten, p.ma_phong, dp.ngay_nhan, dp.ngay_tra, dp.so_khach, dp.tong_tien, dp.trang_thai, dp.ngay_tao
    FROM dat_phong dp
    JOIN nguoi_dung nd ON dp.id_nguoi_dung = nd.id
    JOIN phong p ON p.id = dp.id
    ORDER BY dp.ngay_tao DESC
";
$res = $conn->query($sql);
$dat_phongs = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Quản lý đặt phòng</h2>

    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Khách hàng</th>
                <th>Phòng</th>
                <th>Ngày nhận</th>
                <th>Ngày trả</th>
                <th>Số khách</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dat_phongs as $dp): ?>
            <tr>
                <td><?= $dp['id'] ?></td>
                <td><?= htmlspecialchars($dp['ho_ten']) ?></td>
                <td><?= htmlspecialchars($dp['ma_phong']) ?></td>
                <td><?= $dp['ngay_nhan'] ?></td>
                <td><?= $dp['ngay_tra'] ?></td>
                <td><?= $dp['so_khach'] ?></td>
                <td><?= number_format($dp['tong_tien'],0,',','.') ?>₫</td>
                <td>
					<form method="POST" style="display:inline-block;">
						<input type="hidden" name="dat_phong_id" value="<?= $dp['id'] ?>">
						<input type="hidden" name="update_trang_thai" value="1"> <!-- Thêm dòng này -->
						<select name="trang_thai" class="form-select form-select-sm" 
							<?= $dp['trang_thai']=='da_tra' ? 'disabled' : '' ?> 
							onchange="this.form.submit()">
							<option value="cho_xac_nhan" <?= $dp['trang_thai']=='cho_xac_nhan'?'selected':'' ?>>Chờ xác nhận</option>
							<option value="da_xac_nhan" <?= $dp['trang_thai']=='da_xac_nhan'?'selected':'' ?>>Đã xác nhận</option>
							<option value="dang_o" <?= $dp['trang_thai']=='dang_o'?'selected':'' ?>>Đang ở</option>
							<option value="huy" <?= $dp['trang_thai']=='huy'?'selected':'' ?>>Hủy</option>
							<option value="da_tra" <?= $dp['trang_thai']=='da_tra'?'selected':'' ?>>Đã trả</option>
						</select>
					</form>
				</td>
                <td><?= $dp['ngay_tao'] ?></td>
                <td>
                    <a href="quan_ly_dat_phong.php?xoa_id=<?= $dp['id'] ?>" 
                       class="btn btn-sm btn-danger" 
                       onclick="return confirm('Bạn có chắc muốn xóa đặt phòng này?');">
                       Xóa
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('footer.php'); ?>
