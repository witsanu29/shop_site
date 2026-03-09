<?php
session_start();

$cart_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_data'])) {
    $cart_data = json_decode($_POST['cart_data'], true);

    if (is_array($cart_data) && !empty($cart_data)) {
        $_SESSION['checkout_cart'] = $cart_data;
    } else {
        $error = "ไม่พบข้อมูลในตะกร้า";
    }
} else {
    $error = "การเข้าถึงไม่ถูกต้อง";
}

$cart = $_SESSION['checkout_cart'] ?? $cart_data ?? [];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ยืนยันคำสั่งซื้อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .checkout-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
            max-width: 600px;
            width: 100%;
            padding: 2.5rem 3rem;
            transition: transform 0.3s ease;
        }
        .checkout-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        h3 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
            letter-spacing: 0.07em;
        }
        .list-group-item {
            border: none;
            border-bottom: 1px solid #e1e8ed;
            padding: 1rem 1.25rem;
            font-size: 1rem;
            color: #34495e;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .list-group-item:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.1rem;
            color: #27ae60;
        }
        .btn-confirm {
            background-color: #27ae60;
            border: none;
            width: 100%;
            padding: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            border-radius: 0.6rem;
            box-shadow: 0 6px 15px rgba(39, 174, 96, 0.5);
            transition: background-color 0.3s ease;
        }
        .btn-confirm:hover {
            background-color: #219150;
            box-shadow: 0 8px 20px rgba(33, 145, 80, 0.7);
        }
        .btn-cancel {
            background-color: #bdc3c7;
            border: none;
            padding: 12px 0;
            font-weight: 600;
            border-radius: 0.6rem;
            color: #2c3e50;
            width: 100%;
            margin-top: 10px;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-cancel:hover {
            background-color: #a6acaf;
            color: #1c2833;
        }
        .btn-group {
            margin-top: 1.8rem;
            display: flex;
            gap: 15px;
        }
        /* Responsive */
        @media (max-width: 480px) {
            .checkout-container {
                padding: 2rem 1.5rem;
            }
            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="checkout-container shadow-sm">
    <h3>✅ ยืนยันการสั่งซื้อ</h3>
    <?php if (!empty($cart)): ?>
    <ul class="list-group mb-4">
        <?php $total = 0; ?>
        <?php foreach ($cart as $item): ?>
            <li class="list-group-item">
                <?= htmlspecialchars($item['name']) ?> <small class="text-muted">(<?= $item['quantity'] ?> ชิ้น)</small>
                <span><?= number_format($item['price'] * $item['quantity'], 2) ?> ฿</span>
            </li>
            <?php $total += $item['price'] * $item['quantity']; ?>
        <?php endforeach; ?>
        <li class="list-group-item">
            รวมทั้งหมด
            <span><?= number_format($total, 2) ?> ฿</span>
        </li>
    </ul>
    <div class="btn-group">
        <form action="confirm_order.php" method="post" style="flex-grow:1;">
            <button type="submit" class="btn-confirm">🧾 ยืนยันการสั่งซื้อ</button>
        </form>
        <a href="cart.php" class="btn-cancel">🔙 ย้อนกลับ</a>
    </div>
    <?php else: ?>
        <p class="empty-message">❌ ไม่มีสินค้าที่จะทำรายการสั่งซื้อ</p>
        <a href="cart.php" class="btn btn-secondary w-100 mt-3">กลับไปที่ตะกร้า</a>
    <?php endif; ?>
</div>
</body>
</html>
