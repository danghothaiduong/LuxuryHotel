<?php
session_start();
include('connect_db.php');
include('header.php');

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy lịch sử đặt phòng
$sql = "
    SELECT dp.id, dp.ngay_tao, dp.ngay_nhan, dp.ngay_tra, dp.so_khach, dp.tong_tien, dp.trang_thai, dp.ghi_chu,
           GROUP_CONCAT(p.ma_phong SEPARATOR ', ') AS phong_dat
    FROM dat_phong dp
    JOIN dat_phong_chi_tiet dct ON dp.id = dct.id_dat_phong
    JOIN phong p ON dct.id_phong = p.id
    WHERE dp.id_nguoi_dung = ?
    GROUP BY dp.id
    ORDER BY dp.ngay_tao DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$dat_phongs = [];
while($row = $result->fetch_assoc()){
    $dat_phongs[] = $row;
}
?>

<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">Lịch sử đặt phòng của bạn</h2>

    <?php if(count($dat_phongs) == 0): ?>
        <p class="text-center text-muted">Bạn chưa có đặt phòng nào.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Ngày đặt</th>
                        <th>Phòng</th>
                        <th>Ngày nhận</th>
                        <th>Ngày trả</th>
                        <th>Số khách</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dat_phongs as $index => $dp): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($dp['ngay_tao'])) ?></td>
                            <td><?= htmlspecialchars($dp['phong_dat']) ?></td>
                            <td><?= date('d/m/Y', strtotime($dp['ngay_nhan'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($dp['ngay_tra'])) ?></td>
                            <td><?= $dp['so_khach'] ?></td>
                            <td><?= number_format($dp['tong_tien'],0,',','.') ?>₫</td>
                            <td><?= htmlspecialchars($dp['trang_thai']) ?></td>
                            <td><?= htmlspecialchars($dp['ghi_chu']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include('footer.php'); ?>
