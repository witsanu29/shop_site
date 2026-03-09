<?php
session_start(); // เรียกได้เลยถ้ายังไม่ใช้ check_login.php

include '../auth/check_login.php'; // check_login จะไม่ซ้ำซ้อน session อีก
include '../config/db.php';
include '../log_helper.php';

$cart = $_SESSION['cart'] ?? [];
$total = 0;

// ... โค้ดอื่นตามเดิม ...

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // อัปเดตจำนวน
    foreach ($_POST['quantities'] as $id => $qty) {
        if (isset($cart[$id])) {
            if ($qty <= 0) {
                unset($cart[$id]); // ถ้าน้อยกว่าหรือเท่ากับ 0 ให้ลบ
            } else {
                $cart[$id]['quantity'] = $qty;
            }
        }
    }
    $_SESSION['cart'] = $cart;
    header('Location: cart.php');
    exit;
}

if (isset($_GET['remove'])) {
    $id = $_GET['remove'];
    unset($_SESSION['cart'][$id]);
    header('Location: cart.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>🛒 ตะกร้าสินค้า</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
body {
    background: linear-gradient(135deg, #e0eafc, #cfdef3);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
    padding: 40px 20px;
}

.container {
    max-width: 960px;
    margin: auto;
}

h3 {
    color: #1f2937;
    font-weight: 700;
    letter-spacing: 0.05em;
    margin-bottom: 30px;
    text-align: center;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.cart-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 12px 30px rgba(0,0,0,0.1);
    padding: 2rem;
}

.table {
    border-radius: 0.8rem;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}

.table th {
    background-color: #2563eb;
    color: white;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.table tbody tr:hover {
    background-color: #dbeafe;
    transition: background-color 0.3s ease;
}

.table td, .table th {
    vertical-align: middle;
    padding: 1rem 1.25rem;
}

input[type=number] {
    width: 80px;
    padding: 6px 10px;
    border-radius: 0.4rem;
    border: 1.5px solid #cbd5e1;
    transition: border-color 0.3s ease;
    font-weight: 600;
}

input[type=number]:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 6px rgba(37, 99, 235, 0.6);
}

.btn-primary, .btn-success, .btn-danger, .btn-secondary {
    font-weight: 700;
    padding: 0.6rem 1.2rem;
    border-radius: 0.6rem;
    box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3);
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #1e40af;
    box-shadow: 0 8px 25px rgba(30, 64, 175, 0.6);
}

.btn-success {
    background-color: #16a34a;
    box-shadow: 0 6px 15px rgba(22, 163, 74, 0.4);
}

.btn-success:hover {
    background-color: #15803d;
    box-shadow: 0 8px 25px rgba(21, 128, 61, 0.6);
}

.btn-danger {
    background-color: #dc2626;
    box-shadow: 0 6px 15px rgba(220, 38, 38, 0.4);
}

.btn-danger:hover {
    background-color: #b91c1c;
    box-shadow: 0 8px 25px rgba(185, 28, 28, 0.6);
}

.btn-secondary {
    background-color: #64748b;
    box-shadow: 0 6px 15px rgba(100, 116, 139, 0.3);
    color: white;
}

.btn-secondary:hover {
    background-color: #475569;
    box-shadow: 0 8px 25px rgba(71, 85, 105, 0.6);
}

.d-flex > div {
    display: flex;
    gap: 10px;
}

.alert-warning {
    text-align: center;
    font-size: 1.2rem;
    font-weight: 600;
    color: #854d0e;
    background-color: #fff7cd;
    border-radius: 1rem;
    padding: 1.2rem 2rem;
    box-shadow: 0 6px 15px rgba(255, 223, 89, 0.3);
}

@media (max-width: 576px) {
    input[type=number] {
        width: 60px;
        font-size: 0.9rem;
    }
    .table th, .table td {
        padding: 0.6rem 0.8rem;
        font-size: 0.9rem;
    }
    .d-flex > div {
        flex-direction: column;
    }
}


    </style>
</head>
<body>
<div class="container mt-5">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h3>🛒 ตะกร้าสินค้า</h3>
        <a href="../index.php" class="btn btn-primary btn-sm">🏠 กลับสู่หน้ารายการสินค้า</a>
    </div>
    <!-- ...เนื้อหาอื่น ๆ ต่อจากนี้ -->

    <?php if (empty($cart)): ?>
        <div class="alert alert-warning">ไม่มีสินค้าในตะกร้า</div>
    <?php else: ?>
        <form method="post">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>ชื่อสินค้า</th>
                        <th>จำนวน</th>
                        <th>ราคา/ชิ้น</th>
                        <th>รวม</th>
                        <th>ลบ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $id => $item): 
                        $sum = $item['price'] * $item['quantity'];
                        $total += $sum;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td>
                                <input type="number" name="quantities[<?= $id ?>]" value="<?= $item['quantity'] ?>" class="form-control" min="1">
                            </td>
                            <td><?= number_format($item['price'], 2) ?> ฿</td>
                            <td><?= number_format($sum, 2) ?> ฿</td>
                            <td><a href="?remove=<?= $id ?>" class="btn btn-sm btn-danger">🗑 ลบ</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">รวมทั้งหมด</td>
                        <td colspan="2" class="fw-bold"><?= number_format($total, 2) ?> บาท</td>
                    </tr>
                </tbody>
            </table>
            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-secondary">← เลือกสินค้าต่อ</a>
                <div>
                    <button type="submit" class="btn btn-primary">🔄 อัปเดตจำนวน</button>
                    <a href="checkout.php" class="btn btn-success">✅ ชำระเงิน</a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
