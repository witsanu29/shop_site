<?php
session_start();

//include 'auth/check_login.php'; // ✅ แก้ path ไม่ต้องมี ../
include 'config/db.php';
include 'log_helper.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo "<div class='alert alert-danger p-3'>ไม่พบสินค้านี้</div>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<div class='alert alert-danger p-3'>ไม่พบสินค้านี้</div>";
    exit;
}

$imgs = explode(',', $product['image']);
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดสินค้า - <?= htmlspecialchars($product['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-img {
            max-width: 100%;
            max-height: 350px;
            object-fit: cover;
        }
    </style>
	<style>
    .main-image {
        max-height: 400px;
        object-fit: contain;
        width: 100%;
    }

    .thumb-image {
        aspect-ratio: 1 / 1;
        object-fit: cover;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .thumb-image:hover {
        transform: scale(1.05);
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
    }

    @media (max-width: 768px) {
        .main-image {
            max-height: 250px;
        }
    }
	</style>


</head>
<body>
<div class="container mt-5">
    <a href="index.php" class="btn btn-danger mb-3">← กลับหน้าหลัก</a>

    <div class="card mb-4 shadow">
        <div class="row g-0">
            <div class="col-md-5 p-3">
                <?php
                $imgs = explode(',', $product['image']);
if (!empty($imgs) && file_exists("uploads/" . $imgs[0])) {
    echo "<img src='uploads/" . htmlspecialchars($imgs[0]) . "' class='main-image mb-3'>";
}

if (count($imgs) > 1) {
    echo '<div class="d-flex flex-wrap gap-2">';
    foreach ($imgs as $i => $img) {
        if ($i === 0) continue; // ข้ามภาพหลัก
        if (file_exists("uploads/" . $img)) {
            echo "<img src='uploads/" . htmlspecialchars($img) . "' class='thumb-image'>";
        }
    }
    echo '</div>';
}

                ?>
            </div>
        <div class="col-md-7 p-4">
    <h3><?= htmlspecialchars($product['name']) ?></h3>
    <p class="lead text-success">ราคา <?= number_format($product['price'], 2) ?> บาท</p>
    <p class="text-muted">สินค้า ID: <?= $product['id'] ?></p>
    <hr>
    <p>รายละเอียดสินค้าเพิ่มเติม (สามารถใส่คำอธิบายได้ที่นี่)</p>

    <!-- 🔘 แบบฟอร์มเพิ่มลงตะกร้า -->
    <form method="POST" action="../admin/cart_add.php" class="mt-4">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

        <div class="input-group mb-3" style="max-width: 200px;">
            <span class="input-group-text">จำนวน</span>
            <input type="number" name="quantity" value="1" min="1" class="form-control text-center">
        </div>

        <button type="submit" class="btn btn-primary btn-lg">
            🛒 เพิ่มลงในตะกร้า
        </button>
    </form>
	</div>

       </div>
    </div>
</div>
</body>
</html>
