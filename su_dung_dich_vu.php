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

// Xử lý xóa dịch vụ
if(isset($_GET['xoa_id'])){
    $xoa_id = intval($_GET['xoa_id']);
    $stmt_del = $conn->prepare("DELETE FROM dat_phong_dich_vu WHERE id = ?");
    $stmt_del->bind_param("i", $xoa_id);
    if($stmt_del->execute()){
        $success = "Xóa dịch vụ thành công!";
    } else {
        $error = "Xóa dịch vụ thất bại: " . $conn->error;
    }
}

// Lấy danh sách các phòng đang được đặt
$phongs = [];
$sql_phong = "
    SELECT dp.id AS dat_phong_id, p.ma_phong, dp.ngay_nhan, dp.ngay_tra, nd.ho_ten
    FROM dat_phong dp
    JOIN phong p ON dp.id = p.id
    JOIN nguoi_dung nd ON dp.id_nguoi_dung = nd.id
    WHERE dp.trang_thai IN ('cho_xac_nhan','dang_o')
    ORDER BY dp.ngay_nhan DESC
";
$res_phong = $conn->query($sql_phong);
if($res_phong) $phongs = $res_phong->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách dịch vụ đang hoạt động
$services = [];
$res_services = $conn->query("SELECT * FROM dich_vu WHERE trang_thai='hoat_dong' ORDER BY ten_dich_vu ASC");
if($res_services) $services = $res_services->fetch_all(MYSQLI_ASSOC);

// Xử lý submit form thêm dịch vụ
if(isset($_POST['su_dung_dich_vu'])){
    $dat_phong_id = intval($_POST['dat_phong_id']);
    $dich_vu_id = intval($_POST['dich_vu_id']);
    $so_luong = intval($_POST['so_luong']);

    // Lấy giá dịch vụ
    $stmt_price = $conn->prepare("SELECT gia FROM dich_vu WHERE id = ?");
    $stmt_price->bind_param("i", $dich_vu_id);
    $stmt_price->execute();
    $res_price = $stmt_price->get_result()->fetch_assoc();
    $gia = $res_price['gia'] ?? 0;

    // Insert vào dat_phong_dich_vu
    $stmt_insert = $conn->prepare("INSERT INTO dat_phong_dich_vu (id_dat_phong, id_dich_vu, so_luong, gia) VALUES (?, ?, ?, ?)");
    $stmt_insert->bind_param("iiid", $dat_phong_id, $dich_vu_id, $so_luong, $gia);
    if($stmt_insert->execute()){
        $success = "Đã thêm dịch vụ thành công!";
    } else {
        $error = "Có lỗi xảy ra: " . $conn->error;
    }
}

// Lấy danh sách dịch vụ đã sử dụng
$used_services = [];
$sql_used = "
    SELECT dpdv.id, dpdv.id_dat_phong, dpdv.id_dich_vu, dpdv.so_luong, dpdv.gia,
           p.ma_phong, dv.ten_dich_vu
    FROM dat_phong_dich_vu dpdv
    JOIN dat_phong dp ON dpdv.id_dat_phong = dp.id
    JOIN phong p ON dp.id = p.id
    JOIN dich_vu dv ON dpdv.id_dich_vu = dv.id
    ORDER BY dpdv.id DESC
";
$res_used = $conn->query($sql_used);
if($res_used) $used_services = $res_used->fetch_all(MYSQLI_ASSOC);

?>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Sử dụng dịch vụ</h2>

    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-4">
            <label>Chọn phòng</label>
            <select name="dat_phong_id" class="form-control" required>
                <option value="">-- Chọn phòng --</option>
                <?php foreach($phongs as $phong): ?>
                    <option value="<?= $phong['dat_phong_id'] ?>">
                        <?= htmlspecialchars($phong['ma_phong']) ?> (<?= htmlspecialchars($phong['ho_ten']) ?>) 
                        <?= $phong['ngay_nhan'] ?> → <?= $phong['ngay_tra'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label>Chọn dịch vụ</label>
            <select name="dich_vu_id" class="form-control" required>
                <option value="">-- Chọn dịch vụ --</option>
                <?php foreach($services as $service): ?>
                    <option value="<?= $service['id'] ?>">
                        <?= htmlspecialchars($service['ten_dich_vu']) ?> - <?= number_format($service['gia'],0,',','.') ?>₫
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label>Số lượng</label>
            <input type="number" name="so_luong" class="form-control" value="1" min="1" required>
        </div>
        <div class="col-md-2 align-self-end">
            <button type="submit" name="su_dung_dich_vu" class="btn btn-success w-100">Thêm dịch vụ</button>
        </div>
    </form>

    <h4 class="mt-5 mb-3">Danh sách dịch vụ đã thêm</h4>
    <?php if(count($used_services) > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Phòng</th>
                    <th>Dịch vụ</th>
                    <th>Số lượng</th>
                    <th>Giá 1 đơn vị</th>
                    <th>Thành tiền</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($used_services as $us): ?>
                    <tr>
                        <td><?= htmlspecialchars($us['ma_phong']) ?></td>
                        <td><?= htmlspecialchars($us['ten_dich_vu']) ?></td>
                        <td><?= $us['so_luong'] ?></td>
                        <td><?= number_format($us['gia'],0,',','.') ?>₫</td>
                        <td><?= number_format($us['so_luong'] * $us['gia'],0,',','.') ?>₫</td>
                        <td>
                            <a href="?xoa_id=<?= $us['id'] ?>" 
                               onclick="return confirm('Bạn có chắc muốn xóa dịch vụ này?');" 
                               class="btn btn-sm btn-danger">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Chưa có dịch vụ nào được thêm.</p>
    <?php endif; ?>
</div>

<?php include('footer.php'); ?>
