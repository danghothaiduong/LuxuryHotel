<?php
session_start();
include('connect_db.php');
include('header.php');


if(!isset($_GET['id'])){
    header("Location: quan_ly_phong.php");
    exit();
}
$id_phong = intval($_GET['id']);
$stmt = $conn->prepare("SELECT ma_phong FROM phong WHERE id=?");
$stmt->bind_param("i", $id_phong);
$stmt->execute();
$res = $stmt->get_result();
$phong = $res->fetch_assoc();
if(!$phong){
    $_SESSION['error'] = "Phòng không tồn tại!";
    header("Location: quan_ly_phong.php");
    exit();
}

$error = '';
$success = '';
if(isset($_POST['upload_anh'])){
    if(isset($_FILES['anh']) && $_FILES['anh']['error'] == 0){
        $target_dir = "images/";
        $filename = time() . '_' . basename($_FILES['anh']['name']);
        $target_file = $target_dir . $filename;

        if(move_uploaded_file($_FILES['anh']['tmp_name'], $target_file)){
            $anh_chinh = isset($_POST['anh_chinh']) ? 1 : 0;

            if($anh_chinh){
                // Nếu chọn ảnh chính, set các ảnh khác của phòng thành 0
                $stmt_reset = $conn->prepare("UPDATE anh_phong SET anh_chinh=0 WHERE id_phong=?");
                $stmt_reset->bind_param("i", $id_phong);
                $stmt_reset->execute();
            }

            $stmt_insert = $conn->prepare("INSERT INTO anh_phong (id_phong, duong_dan, anh_chinh) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("isi", $id_phong, $filename, $anh_chinh);
            $stmt_insert->execute();

            $success = "Upload ảnh thành công!";
        } else {
            $error = "Upload ảnh thất bại!";
        }
    } else {
        $error = "Vui lòng chọn ảnh!";
    }
}

// Lấy danh sách ảnh của phòng
$stmt_imgs = $conn->prepare("SELECT * FROM anh_phong WHERE id_phong=?");
$stmt_imgs->bind_param("i", $id_phong);
$stmt_imgs->execute();
$res_imgs = $stmt_imgs->get_result();
$imgs = $res_imgs->fetch_all(MYSQLI_ASSOC);
?>

<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">Quản lý ảnh phòng: <?= htmlspecialchars($phong['ma_phong']) ?></h2>

    <?php if($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mb-4">
        <div class="mb-3">
            <label class="form-label fw-bold">Chọn ảnh</label>
            <input type="file" name="anh" class="form-control" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="anh_chinh" class="form-check-input" id="anh_chinh">
            <label class="form-check-label" for="anh_chinh">Đặt làm ảnh chính</label>
        </div>
        <button type="submit" name="upload_anh" class="btn btn-success">Upload</button>
    </form>

    <div class="row g-4">
        <?php foreach($imgs as $img): ?>
            <div class="col-md-3">
                <div class="card">
                    <img src="images/<?= htmlspecialchars($img['duong_dan']) ?>" class="card-img-top" style="height:200px; object-fit:cover;">
                    <div class="card-body text-center">
                        <?php if($img['anh_chinh']): ?>
                            <span class="badge bg-primary">Ảnh chính</span>
                        <?php endif; ?>
                        <a href="xoa_anh.php?id=<?= $img['id'] ?>&id_phong=<?= $id_phong ?>" class="btn btn-danger btn-sm mt-2" onclick="return confirm('Bạn có chắc muốn xóa ảnh này?')">Xóa</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include('footer.php'); ?>
