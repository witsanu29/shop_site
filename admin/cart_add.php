<?php
session_start(); // เรียกได้เลยถ้ายังไม่ใช้ check_login.php

include '../auth/check_login.php'; // check_login จะไม่ซ้ำซ้อน session อีก
include '../config/db.php';
include '../log_helper.php';

$cart = $_SESSION['cart'] ?? [];
$total = 0;

if (!isset($_POST['product_id'], $_POST['quantity'])) {
    die("❌ คำขอไม่สมบูรณ์");
}

$product_id = intval($_POST['product_id']);
$quantity = max(1, intval($_POST['quantity']));

// ตรวจสอบว่าสินค้ามีอยู่จริงหรือไม่
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($product && $product['stock'] >= $quantity) {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'] ?? '',
            'quantity' => $quantity
        ];
    }

    log_action("เพิ่มสินค้าลงตะกร้า: " . $product['name'], $_SESSION['user'] ?? 'guest');
    header('Location: ../admin/cart.php?msg=added');
    exit();
} else {
    echo "<div class='alert alert-danger p-3'>❌ สินค้าไม่มีในระบบหรือจำนวนไม่เพียงพอ</div>";
}
