<?php
include '../auth/check_login.php';
include '../config/db.php';
include '../log_helper.php';

log_page_view(basename(__FILE__), $_SESSION['user'] ?? 'guest');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);

    $images = [];
    $max_files = 20;
    $max_size = 40 * 1024 * 1024;
    $allowed_ext = ['jpg', 'jpeg', 'png'];

    if (isset($_FILES['images'])) {
        if (count($_FILES['images']['name']) > $max_files) {
            $error = "อัปโหลดได้ไม่เกิน 20 รูป";
        } else {
            for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                $file_name = $_FILES['images']['name'][$i];
                $file_tmp = $_FILES['images']['tmp_name'][$i];
                $file_size = $_FILES['images']['size'][$i];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                if (!in_array($file_ext, $allowed_ext)) {
                    $error = "เฉพาะไฟล์ .jpg, .jpeg, .png เท่านั้น";
                    break;
                }

                if ($file_size > $max_size) {
                    $error = "แต่ละรูปต้องไม่เกิน 40MB";
                    break;
                }

                $new_filename = uniqid('img_') . '.' . $file_ext;
                $upload_path = '../uploads/' . $new_filename;

                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $images[] = $new_filename;
                } else {
                    $error = "อัปโหลด $file_name ล้มเหลว";
                    break;
                }
            }
        }
    }

    if (!$error && $name && $price > 0 && count($images) > 0) {
    $image_string = implode(',', $images);
    $sort_order = intval($_POST['sort_order']);

    $stmt = $conn->prepare("INSERT INTO products (name, price, image, sort_order) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdsi", $name, $price, $image_string, $sort_order);


        if ($stmt->execute()) {
            foreach ($images as $imgName) {
                log_file_upload($imgName, $_SESSION['user'] ?? 'guest', filesize('../uploads/' . $imgName));
            }
            header("Location: dashboard.php?msg=added");
            exit();
        } else {
            $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
        }
    } elseif (!$error) {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>📦 เพิ่มสินค้าใหม่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f7f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            max-width: 600px;
            margin: auto;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="form-container">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-primary text-white rounded-top-4">
                <h4 class="mb-0">📦 เพิ่มสินค้าใหม่</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data" action="">
					<div class="mb-3">
						<label>ลำดับการแสดงผล</label>
						<input type="number" name="sort_order" class="form-control" required min="0" value="0">
					</div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อสินค้า</label>
                        <input type="text" name="name" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ราคา (บาท)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required min="0">
                    </div>
                    <div class="mb-3">
                        <input type="file" name="images[]" class="form-control" accept=".jpg,.jpeg,.png" multiple required>
                        <small class="text-muted">* อัปโหลดได้สูงสุด 20 รูป </small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="dashboard.php" class="btn btn-secondary">ย้อนกลับ</a>
                        <button type="submit" class="btn btn-success">💾 บันทึกสินค้า</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
