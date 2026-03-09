<?php
include '../auth/check_login.php';
include '../config/db.php';

$id = intval($_GET['id']);

// ดึงข้อมูลรูปภาพก่อนลบ
$stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$product = $res->fetch_assoc();

if ($product) {
    // ลบรูปภาพเก่า
    if ($product['image'] && file_exists('../uploads/' . $product['image'])) {
        unlink('../uploads/' . $product['image']);
    }

    // ลบข้อมูลสินค้า
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // บันทึก log
    file_put_contents("../logs/user_logs.txt", "ลบสินค้า ID: $id เวลา: ".date("Y-m-d H:i:s")."\n", FILE_APPEND);
}

header("Location: dashboard.php");
exit();
header("Location: dashboard.php?msg=added");
exit();

?>
