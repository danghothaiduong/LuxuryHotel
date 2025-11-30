<?php
session_start();
include('connect_db.php');
include('header.php');


$success = '';
$error = '';

// Thêm dịch vụ
if(isset($_POST['them_dich_vu'])){
    $ten = $_POST['ten_dich_vu'];
    $mo_ta = $_POST['mo_ta'];
    $gia = floatval($_POST['gia']);
    $trang_thai = $_POST['trang_thai'];

    $stmt = $conn->prepare("INSERT INTO dich_vu (ten_dich_vu, mo_ta, gia, trang_thai, ngay_tao) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssds", $ten, $mo_ta, $gia, $trang_thai);

    if($stmt->execute()){
        $success = "Thêm dịch vụ thành công!";
    } else {
        $error = "Lỗi thêm dịch vụ: ".$conn->error;
    }
}

// Sửa dịch vụ
if(isset($_POST['sua_dich_vu'])){
    $id = intval($_POST['dich_vu_id']);
    $ten = $_POST['ten_dich_vu'];
    $mo_ta = $_POST['mo_ta'];
    $gia = floatval($_POST['gia']);
    $trang_thai = $_POST['trang_thai'];

    $stmt = $conn->prepare("UPDATE dich_vu SET ten_dich_vu=?, mo_ta=?, gia=?, trang_thai=? WHERE id=?");
    $stmt->bind_param("ssdsi", $ten, $mo_ta, $gia, $trang_thai, $id);

    if($stmt->execute()){
        $success = "Cập nhật dịch vụ thành công!";
    } else {
        $error = "Lỗi cập nhật dịch vụ: ".$conn->error;
    }
}

// Xóa dịch vụ
if(isset($_GET['xoa_id'])){
    $id = intval($_GET['xoa_id']);
    $stmt = $conn->prepare("DELETE FROM dich_vu WHERE id=?");
    $stmt->bind_param("i", $id);

    if($stmt->execute()){
        $success = "Xóa dịch vụ thành công!";
    } else {
        $error = "Lỗi xóa dịch vụ: ".$conn->error;
    }
}

// Lấy danh sách dịch vụ
$res = $conn->query("SELECT * FROM dich_vu ORDER BY ngay_tao DESC");
$services = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Quản lý dịch vụ</h2>

    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Form thêm dịch vụ -->
    <div class="card mb-4">
        <div class="card-header">Thêm dịch vụ mới</div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-4">
                    <label>Tên dịch vụ</label>
                    <input type="text" name="ten_dich_vu" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Mô tả</label>
                    <input type="text" name="mo_ta" class="form-control">
                </div>
                <div class="col-md-2">
                    <label>Giá</label>
                    <input type="number" name="gia" class="form-control" min="0" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label>Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="hoat_dong">Hoạt động</option>
                        <option value="khong_hoat_dong">Không hoạt động</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" name="them_dich_vu" class="btn btn-success">Thêm dịch vụ</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách dịch vụ -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên dịch vụ</th>
                <th>Mô tả</th>
                <th>Giá</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($services as $service): ?>
            <tr>
                <form method="POST">
                    <td><?= $service['id'] ?></td>
                    <td><input type="text" name="ten_dich_vu" value="<?= htmlspecialchars($service['ten_dich_vu']) ?>" class="form-control"></td>
                    <td><input type="text" name="mo_ta" value="<?= htmlspecialchars($service['mo_ta']) ?>" class="form-control"></td>
                    <td><input type="number" name="gia" value="<?= $service['gia'] ?>" class="form-control" min="0" step="0.01"></td>
                    <td>
                        <select name="trang_thai" class="form-select">
                            <option value="hoat_dong" <?= $service['trang_thai']=='hoat_dong'?'selected':'' ?>>Hoạt động</option>
                            <option value="khong_hoat_dong" <?= $service['trang_thai']=='khong_hoat_dong'?'selected':'' ?>>Không hoạt động</option>
                        </select>
                    </td>
                    <td><?= $service['ngay_tao'] ?></td>
                    <td>
                        <input type="hidden" name="dich_vu_id" value="<?= $service['id'] ?>">
                        <button type="submit" name="sua_dich_vu" class="btn btn-sm btn-warning mb-1 w-100">Sửa</button>
                        <a href="quan_ly_dich_vu.php?xoa_id=<?= $service['id'] ?>" class="btn btn-sm btn-danger w-100" onclick="return confirm('Bạn có chắc muốn xóa dịch vụ này?');">Xóa</a>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('footer.php'); ?>
