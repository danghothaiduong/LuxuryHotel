<?php
session_start();
include('connect_db.php');
include('header.php');

// Kiểm tra quyền quản lý (giả sử role = 'admin')



$error = '';
$success = '';

// Thêm phòng
if(isset($_POST['them_phong'])){
    $ma_phong = $_POST['ma_phong'];
    $id_loai = $_POST['id_loai'];
    $suc_chua = intval($_POST['suc_chua']);
    $gia_co_ban = floatval($_POST['gia_co_ban']);
    $trang_thai = $_POST['trang_thai'];

    $stmt = $conn->prepare("INSERT INTO phong (ma_phong, id_loai, suc_chua, gia_co_ban, trang_thai) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siids", $ma_phong, $id_loai, $suc_chua, $gia_co_ban, $trang_thai);
    if($stmt->execute()){
        $success = "Thêm phòng thành công!";
    } else {
        $error = "Thêm phòng thất bại!";
    }
}

// Xóa phòng
if(isset($_GET['xoa'])){
    $id_xoa = intval($_GET['xoa']);
    $stmt = $conn->prepare("DELETE FROM phong WHERE id = ?");
    $stmt->bind_param("i", $id_xoa);
    if($stmt->execute()){
        $success = "Xóa phòng thành công!";
    } else {
        $error = "Xóa phòng thất bại!";
    }
}

// Lấy danh sách phòng
$sql = "
    SELECT p.*, lp.ten_loai 
    FROM phong p
    LEFT JOIN loai_phong lp ON p.id_loai = lp.id
";
$result = $conn->query($sql);
$phongs = $result->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách loại phòng cho select
$loai_result = $conn->query("SELECT * FROM loai_phong");
$loai_phongs = $loai_result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">Quản lý phòng</h2>

    <?php if($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Form thêm phòng -->
    <div class="card mb-4">
        <div class="card-header fw-bold">Thêm phòng mới</div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-2">
                    <label>Mã phòng</label>
                    <input type="text" name="ma_phong" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label>Loại phòng</label>
                    <select name="id_loai" class="form-control" required>
                        <?php foreach($loai_phongs as $loai): ?>
                        <option value="<?= $loai['id'] ?>"><?= htmlspecialchars($loai['ten_loai']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Sức chứa</label>
                    <input type="number" name="suc_chua" class="form-control" min="1" required>
                </div>
                <div class="col-md-2">
                    <label>Giá cơ bản</label>
                    <input type="number" name="gia_co_ban" class="form-control" min="0" step="1000" required>
                </div>
                <div class="col-md-2">
                    <label>Trạng thái</label>
                    <select name="trang_thai" class="form-control" required>
                        <option value="san_sang">Sẵn sàng</option>
                        <option value="bao_tri">Bảo trì</option>
                    </select>
                </div>
                <div class="col-md-1 align-self-end">
                    <button type="submit" name="them_phong" class="btn btn-primary w-100">Thêm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách phòng -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Mã phòng</th>
                <th>Loại</th>
                <th>Sức chứa</th>
                <th>Giá cơ bản</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($phongs as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['ma_phong']) ?></td>
                <td><?= htmlspecialchars($p['ten_loai']) ?></td>
                <td><?= $p['suc_chua'] ?></td>
                <td><?= number_format($p['gia_co_ban'],0,',','.') ?>₫</td>
                <td><?= $p['trang_thai'] ?></td>
                <td>
                    <a href="sua_phong.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
					<a href="them_anh_phong.php?id=<?= $p['id'] ?>" class="btn btn-info btn-sm">Thêm ảnh</a>
                    <a href="?xoa=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('footer.php'); ?>
