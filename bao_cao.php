<?php
session_start();
include('connect_db.php');
include('header.php');



// Tổng doanh thu từ đặt phòng (trạng thái đã trả)
$res_dp = $conn->query("SELECT SUM(tong_tien) AS doanh_thu_phong FROM dat_phong WHERE trang_thai='da_tra'");
$dp_row = $res_dp->fetch_assoc();
$doanh_thu_phong = $dp_row['doanh_thu_phong'] ?? 0;

// Tổng doanh thu từ dịch vụ (chỉ tính cho các đặt phòng đã trả)
$res_dv = $conn->query("
    SELECT SUM(dpd.so_luong * dpd.gia) AS doanh_thu_dich_vu
    FROM dat_phong_dich_vu dpd
    JOIN dat_phong dp ON dpd.id_dat_phong = dp.id
    WHERE dp.trang_thai='da_tra'
");
$dv_row = $res_dv->fetch_assoc();
$doanh_thu_dich_vu = $dv_row['doanh_thu_dich_vu'] ?? 0;

// Tổng doanh thu
$tong_doanh_thu = $doanh_thu_phong + $doanh_thu_dich_vu;

// Thống kê số lượt sử dụng dịch vụ
$res_luot = $conn->query("
    SELECT dv.ten_dich_vu, SUM(dpd.so_luong) AS so_luot
    FROM dat_phong_dich_vu dpd
    JOIN dich_vu dv ON dpd.id_dich_vu = dv.id
    JOIN dat_phong dp ON dpd.id_dat_phong = dp.id
    WHERE dp.trang_thai='da_tra'
    GROUP BY dpd.id_dich_vu
    ORDER BY so_luot DESC
");
$luot_su_dung = $res_luot ? $res_luot->fetch_all(MYSQLI_ASSOC) : [];

?>
<div class="container my-5">
    <h2 class="fw-bold mb-4">Báo cáo thống kê</h2>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card p-3 text-center bg-light">
                <h5>Doanh thu đặt phòng</h5>
                <p class="fs-4 fw-bold text-success"><?= number_format($doanh_thu_phong,0,',','.') ?>₫</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 text-center bg-light">
                <h5>Doanh thu dịch vụ</h5>
                <p class="fs-4 fw-bold text-primary"><?= number_format($doanh_thu_dich_vu,0,',','.') ?>₫</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 text-center bg-light">
                <h5>Tổng doanh thu</h5>
                <p class="fs-4 fw-bold text-danger"><?= number_format($tong_doanh_thu,0,',','.') ?>₫</p>
            </div>
        </div>
    </div>

    <h4 class="fw-bold mb-3">Số lượt sử dụng dịch vụ</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Dịch vụ</th>
                <th>Số lượt sử dụng</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($luot_su_dung as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['ten_dich_vu']) ?></td>
                <td><?= $row['so_luot'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h4 class="fw-bold mt-5 mb-3">Biểu đồ số lượt sử dụng dịch vụ</h4>
    <canvas id="chartDichVu" height="150"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('chartDichVu').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($luot_su_dung,'ten_dich_vu')) ?>,
        datasets: [{
            label: 'Số lượt sử dụng',
            data: <?= json_encode(array_column($luot_su_dung,'so_luot')) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero:true,
                precision:0
            }
        }
    }
});
</script>

<?php include('footer.php'); ?>
