<?php
include('connect_db.php');
include('header.php');
?>

<!-- BANNER CHÍNH -->
<div class="container-fluid p-0">
    <div class="position-relative">
        <img src="images/doi1.jpg" class="img-fluid w-100" style="height: 500px; object-fit: cover;">
        <div class="position-absolute top-50 start-50 translate-middle text-center text-white">
            <h1 class="display-4 fw-bold">Chào mừng đến với Luxury Hotel</h1>
            <p class="lead">Trải nghiệm đẳng cấp – Nghỉ dưỡng hoàn hảo</p>

            <a href="phong.php" class="btn btn-primary btn-lg mt-3">Đặt phòng ngay</a>
        </div>
    </div>
</div>

<!-- PHÒNG NỔI BẬT -->
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">Phòng nổi bật</h2>
    <div class="row g-4">

        <?php
        // Lấy 6 phòng đang sẵn sàng
        $sql = "
            SELECT p.id, p.ma_phong, p.gia_co_ban, p.mo_ta, a.duong_dan
            FROM phong p
            LEFT JOIN anh_phong a ON p.id = a.id_phong AND a.anh_chinh = 1
            WHERE p.trang_thai = 'san_sang'
            ORDER BY p.id ASC
            LIMIT 6
        ";

        $result = $conn->query($sql);

        while ($room = $result->fetch_assoc()):
        ?>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                
                <img src="images/<?= $room['duong_dan'] ? $room['duong_dan'] : 'default_room.jpg' ?>" 
				class="card-img-top" 
				alt="Phòng <?= htmlspecialchars($room['ma_phong']) ?>" 
				style="height:250px; object-fit:cover;">


                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Phòng <?= htmlspecialchars($room['ma_phong']) ?></h5>

                    <p class="card-text flex-grow-1">
                        <?= htmlspecialchars(substr($room['mo_ta'], 0, 80)) ?>...
                    </p>

                    <p class="fw-bold mb-2">
                        <?= number_format($room['gia_co_ban'], 0, ',', '.') ?>₫ / đêm
                    </p>

                    <a href="chi_tiet_phong.php?id=<?= $room['id'] ?>" class="btn btn-primary mt-auto">
                        Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>

    </div>
</div>

<!-- DỊCH VỤ NỔI BẬT -->
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">Dịch vụ nổi bật</h2>
    <div class="row g-4">

        <?php
        // Lấy 4 dịch vụ đang hoạt động
        $sql = "SELECT * FROM dich_vu WHERE trang_thai = 'hoat_dong' ORDER BY id ASC LIMIT 4";
        $result_dv = $conn->query($sql);

        while ($dv = $result_dv->fetch_assoc()):
        ?>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm text-center">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($dv['ten_dich_vu']) ?></h5>
                    <p class="card-text">
                        <?= htmlspecialchars(substr($dv['mo_ta'], 0, 60)) ?>...
                    </p>
                    <p class="fw-bold">
                        <?= number_format($dv['gia'], 0, ',', '.') ?>₫
                    </p>
                </div>
            </div>
        </div>

        <?php endwhile; ?>

    </div>
</div>

<?php include('footer.php'); ?>
