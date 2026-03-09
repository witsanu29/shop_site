<?php

include 'auth/check_login.php';
include 'config/db.php';
include 'log_helper.php';

// ตรวจสอบว่ามีข้อมูลตะกร้าและข้อมูลลูกค้า
$cart = $_SESSION['checkout_cart'] ?? [];
if (empty($cart)) {
    die("<div class='alert alert-warning'>ไม่พบข้อมูลในตะกร้า</div>");
}

$customer_name = $_SESSION['customer_name'] ?? 'ลูกค้าไม่ระบุชื่อ';
$customer_email = $_SESSION['customer_email'] ?? '-';
$customer_phone = $_SESSION['customer_phone'] ?? '-';

// คำนวณราคารวม
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

// ✅ เพิ่มคำสั่งซื้อในตาราง orders
$stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, total) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssd", $customer_name, $customer_email, $customer_phone, $total);
$stmt->execute();
$order_id = $stmt->insert_id;

// ✅ เพิ่มสินค้าแต่ละรายการในตาราง order_items
foreach ($cart as $item) {
    $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("isid", $order_id, $item['name'], $item['quantity'], $item['price']);
    $stmt2->execute();
}

// ✅ เคลียร์ตะกร้า
unset($_SESSION['cart']);
unset($_SESSION['checkout_cart']);

// เก็บ order_id เพื่อใช้กับ receipt
$_SESSION['order_id'] = $order_id;

session_start();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// บันทึกข้อมูลลงฐานข้อมูลหรือไฟล์ (แล้วแต่ระบบ)
// ...

unset($_SESSION['cart']); // ล้างตะกร้า

echo "<div style='padding:2rem;font-family:sans-serif;'>
<h2>✅ ขอบคุณที่สั่งซื้อ!</h2>
<p>ระบบได้รับคำสั่งซื้อของคุณแล้ว</p>
<a href='index.php'>🔙 กลับหน้าหลัก</a>
</div>";
?>

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>✅ คำสั่งซื้อสำเร็จ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .order-success {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.08);
            padding: 30px;
        }
    </style>
</head>
<body>
    <div class="order-success text-center">
        <h2 class="text-success">✅ คำสั่งซื้อของคุณสำเร็จแล้ว!</h2>
        <p class="lead">ขอบคุณที่ใช้บริการของเรา</p>
        <p>เลขที่ใบสั่งซื้อ: <strong>#<?= $order_id ?></strong></p>
        <a href="receipt.php" class="btn btn-primary mt-3">🧾 ดูใบเสร็จ</a>
        <a href="index.php" class="btn btn-secondary mt-3">🏠 กลับหน้าแรก</a>
    </div>
</body>
</html>
