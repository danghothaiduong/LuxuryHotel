<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nếu chưa đăng nhập → mặc định là khách
$vai_tro = $_SESSION['vai_tro'] ?? 'khach';
$ho_ten = $_SESSION['ho_ten'] ?? 'Khách';

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Khách Sạn</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="images/logo.jpg" alt="Logo" height="60">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarNav" aria-controls="navbarNav" 
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav me-auto">
                <!-- Menu công khai -->
                <li class="nav-item">
                    <a class="nav-link" href="phong.php">Danh sách phòng</a>
                </li>

                <!-- MENU KHÁCH -->
                <?php if ($vai_tro == 'khach'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="lich_su_dat_phong.php">Lịch sử đặt phòng</a>
                    </li>
					<li class="nav-item">
                        <a class="nav-link" href="dat_phong.php">Dặt phòng</a>
                    </li>
                <?php endif; ?>

                <!-- MENU NHÂN VIÊN -->
                <?php if ($vai_tro == 'nhan_vien'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="quan_ly_dat_phong.php">Quản lý đặt phòng</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="su_dung_dich_vu.php">Dịch vụ</a>
                    </li>
					<li class="nav-item">
                        <a class="nav-link" href="thanh_toan.php">Check out</a>
                    </li>
                <?php endif; ?>

                <!-- MENU QUẢN TRỊ -->
                <?php if ($vai_tro == 'quan_ly'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="quan_ly_nguoi_dung.php">Quản lý người dùng</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="quan_ly_phong.php">Quản lý phòng</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="quan_ly_dich_vu.php">Quản lý dịch vụ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bao_cao.php">Báo cáo doanh thu</a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Khu vực tài khoản -->
            <!-- Khu vực tài khoản -->
			<ul class="navbar-nav">
				<?php if (!isset($_SESSION['user_id'])) : ?>
					<!-- Chưa đăng nhập -->
					<li class="nav-item">
						<a class="nav-link" href="login.php">Đăng nhập</a>
					</li>
				<?php else : ?>
					<!-- Đã đăng nhập -->
					<li class="nav-item">
						<span class="nav-link">👤 <?= htmlspecialchars($ho_ten) ?> (<?= htmlspecialchars($vai_tro) ?>)</span>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="profile.php">Hồ sơ</a>
					</li>
					<li class="nav-item">
						<a class="nav-link text-danger" href="logout.php">Đăng xuất</a>
					</li>
				<?php endif; ?>
			</ul>


        </div>
    </div>
</nav>

<!-- Bootstrap 5 JS Bundle (cần cho dropdown) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
