<?php
$host = 'localhost';
$user = 'sa';        // เปลี่ยนเป็น user ของโฮสต์จริง
$pass = 'sa';            // เปลี่ยนเป็น password ของโฮสต์จริง
$db   = 'shop_db';     // ชื่อฐานข้อมูล

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset('utf8mb4');

if (!$conn || $conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}
?>
