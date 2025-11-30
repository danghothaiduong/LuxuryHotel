<?php
session_start();
include('connect_db.php');
include('header.php');


// Xóa người dùng
if(isset($_GET['xoa_id'])){
    $xoa_id = intval($_GET['xoa_id']);
    $stmt_del = $conn->prepare("DELETE FROM nguoi_dung WHERE id = ?");
    $stmt_del->bind_param("i", $xoa_id);
    $stmt_del->execute();
    $_SESSION['success'] = "Xóa người dùng thành công!";
    header("Location: quan_ly_nguoi_dung.php");
    exit();
}

// Lấy danh sách người dùng
$stmt = $conn->prepare("SELECT * FROM nguoi_dung ORDER BY ngay_tao DESC");
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Quản lý người dùng</h2>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <a href="them_nguoi_dung.php" class="btn btn-primary mb-3">Thêm người dùng</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>SĐT</th>
                <th>Role</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['ho_ten']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['so_dien_thoai']) ?></td>
                <td><?= $user['vai_tro'] ?></td>
                <td><?= $user['ngay_tao'] ?></td>
                
                <td>
					<?php if($user['trang_thai'] == 'hoat_dong'): ?>
						<a href="doi_trang_thai_nguoi_dung.php?id=<?= $user['id'] ?>&status=khoa" 
						   class="btn btn-sm btn-success"
						   onclick="return confirm('Bạn có chắc muốn khóa tài khoản này?');">🔓
						   <i class="bi bi-lock-fill"></i>
						</a>
					<?php else: ?>
						<a href="doi_trang_thai_nguoi_dung.php?id=<?= $user['id'] ?>&status=hoat_dong" 
						   class="btn btn-sm btn-danger"
						   onclick="return confirm('Bạn có chắc muốn mở khóa tài khoản này?');">🔒
						   <i class="bi bi-unlock-fill"></i>
						</a>
					<?php endif; ?>
				
                    <a href="quan_ly_nguoi_dung.php?xoa_id=<?= $user['id'] ?>" 
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Bạn có chắc muốn xóa người dùng này?');">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('footer.php'); ?>
