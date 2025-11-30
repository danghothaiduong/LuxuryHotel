<?php
include('connect_db.php');
include('header.php');

// Kiểm tra id phòng
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='container py-5 text-center'><h4>Phòng không tồn tại.</h4></div>";
    include('footer.php');
    exit;
}

$id = intval($_GET['id']);

// ===============================
// LẤY THÔNG TIN PHÒNG
// ===============================
$sql = "
    SELECT p.*, lp.ten_loai
    FROM phong p
    LEFT JOIN loai_phong lp ON p.id_loai = lp.id
    WHERE p.id = $id
";
$result = $conn->query($sql);
$room = $result->fetch_assoc();

if (!$room) {
    echo "<div class='container py-5 text-center'><h4>Phòng không tồn tại.</h4></div>";
    include('footer.php');
    exit;
}

// ===============================
// LẤY ẢNH PHÒNG
// ===============================
$sqlImg = "SELECT * FROM anh_phong WHERE id_phong = $id ORDER BY anh_chinh DESC";
$images = $conn->query($sqlImg);


?>

<div class="container my-5">

    <!-- TÊN PHÒNG -->
    <h2 class="fw-bold mb-3">Phòng <?= htmlspecialchars($room['ma_phong']) ?></h2>
    <p class="text-muted">Loại phòng: <strong><?= htmlspecialchars($room['ten_loai']) ?></strong></p>

    <div class="row g-4">

        <!-- ẢNH PHÒNG -->
        <div class="col-md-7">
			<?php if ($images->num_rows > 0): ?>
				<div id="roomCarousel" class="carousel slide" data-bs-ride="carousel">
					<div class="carousel-inner">

						<?php 
						$activeSet = false;
						while ($img = $images->fetch_assoc()):
						?>
						<div class="carousel-item <?= !$activeSet ? 'active' : '' ?>">
							<img src="images/<?= $img['duong_dan'] ?>" 
								 class="d-block w-100"
								 style="height:400px; object-fit:cover;"
								 alt="Ảnh phòng <?= htmlspecialchars($room['ma_phong']) ?>">
						</div>

						<?php $activeSet = true; endwhile; ?>
						
					</div>

					<button class="carousel-control-prev" type="button" data-bs-target="#roomCarousel" data-bs-slide="prev">
						<span class="carousel-control-prev-icon"></span>
					</button>

					<button class="carousel-control-next" type="button" data-bs-target="#roomCarousel" data-bs-slide="next">
						<span class="carousel-control-next-icon"></span>
					</button>

					<!-- Indicators -->
					<div class="carousel-indicators mt-2">
						<?php
						$images->data_seek(0); // reset pointer để lấy lại số lượng ảnh
						$i = 0;
						while ($img = $images->fetch_assoc()): ?>
							<button type="button" data-bs-target="#roomCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i==0?'active':'' ?>"></button>
						<?php $i++; endwhile; ?>
					</div>

				</div>
			<?php else: ?>
				<img src="images/default_room.jpg" class="img-fluid rounded" alt="No image">
			<?php endif; ?>
		</div>


        <!-- THÔNG TIN PHÒNG -->
        <div class="col-md-5">
            <h4 class="fw-bold text-primary">
                <?= number_format($room['gia_co_ban'], 0, ',', '.') ?>₫ / đêm
            </h4>

            <p><strong>Sức chứa:</strong> <?= $room['suc_chua'] ?> khách</p>
            <p><strong>Số giường:</strong> <?= $room['so_giuong'] ?></p>
            <p><strong>Trạng thái:</strong> 
                <?php if ($room['trang_thai'] == 'san_sang'): ?>
                    <span class="badge bg-success">Sẵn sàng</span>
                <?php elseif ($room['trang_thai'] == 'da_dat'): ?>
                    <span class="badge bg-warning text-dark">Đã đặt</span>
                <?php else: ?>
                    <span class="badge bg-danger">Bảo trì</span>
                <?php endif; ?>
            </p>

            <hr>

            <!-- NÚT ĐẶT PHÒNG -->
            <?php if ($room['trang_thai'] == 'san_sang'): ?>
                <a href="dat_phong.php?id_phong=<?= $room['id'] ?>" class="btn btn-primary btn-lg w-100">
                    Đặt phòng ngay
                </a>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg w-100" disabled>Không thể đặt</button>
            <?php endif; ?>

        </div>
    </div>

    <!-- MÔ TẢ -->
    <div class="mt-5">
        <h4 class="fw-bold">Mô tả chi tiết</h4>
        <p><?= nl2br(htmlspecialchars($room['mo_ta'])) ?></p>
    </div>


</div>

<?php include('footer.php'); ?>
