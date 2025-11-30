<?php
include('connect_db.php');
include('header.php');
?>

<div class="container my-5">

    <h2 class="fw-bold text-center mb-4">Danh sách phòng</h2>

    <!-- BỘ LỌC LOẠI PHÒNG -->
    <form method="GET" class="row mb-4">
        <div class="col-md-4 offset-md-4">
            <select name="loai" class="form-select" onchange="this.form.submit()">
                <option value="">-- Tất cả loại phòng --</option>

                <?php
                $sqlLoai = "SELECT * FROM loai_phong ORDER BY ten_loai ASC";
                $rsLoai = $conn->query($sqlLoai);

                while ($lp = $rsLoai->fetch_assoc()):
                ?>
                <option value="<?= $lp['id'] ?>" 
                    <?= (isset($_GET['loai']) && $_GET['loai'] == $lp['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($lp['ten_loai']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
    </form>

    <div class="row g-4">
        <?php
        // ========================
        // Lấy danh sách phòng
        // ========================

        $where = "";

        // Lọc theo loại phòng
        if (isset($_GET['loai']) && $_GET['loai'] != "") {
            $idLoai = intval($_GET['loai']);
            $where = "WHERE p.id_loai = $idLoai";
        }

        $sql = "
            SELECT p.*, lp.ten_loai, a.duong_dan
            FROM phong p
            LEFT JOIN loai_phong lp ON p.id_loai = lp.id
            LEFT JOIN anh_phong a ON p.id = a.id_phong AND a.anh_chinh = 1
            $where
            ORDER BY p.id DESC
        ";

        $result = $conn->query($sql);

        if ($result->num_rows == 0) {
            echo "<p class='text-center text-muted'>Không tìm thấy phòng phù hợp.</p>";
        }

        while ($room = $result->fetch_assoc()):
        ?>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">

                <!-- ẢNH PHÒNG -->
                <img src="images/<?= $room['duong_dan'] ? $room['duong_dan'] : 'default_room.jpg' ?>" 
					class="card-img-top" 
					alt="Phòng <?= htmlspecialchars($room['ma_phong']) ?>" 
					 style="height:250px; object-fit:cover;">


                <div class="card-body d-flex flex-column">

                    <h5 class="card-title fw-bold"><?= htmlspecialchars($room['ma_phong']) ?></h5>
                    <p class="text-muted">Loại: <?= htmlspecialchars($room['ten_loai']) ?></p>

                    <p class="card-text flex-grow-1">
                        <?= htmlspecialchars(substr($room['mo_ta'], 0, 100)) ?>...
                    </p>

                    <p class="fw-bold mb-2 text-primary">
                        <?= number_format($room['gia_co_ban'], 0, ',', '.') ?>₫ / đêm
                    </p>

                    <a href="chi_tiet_phong.php?id=<?= $room['id'] ?>" 
                       class="btn btn-outline-primary mt-auto">
                       Xem chi tiết
                    </a>

                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

</div>

<?php include('footer.php'); ?>
