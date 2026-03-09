<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

// กำหนดเวลา timeout (หน่วยวินาที)
$timeout = 1800; // 30 นาที

// ตรวจสอบเวลาที่ผ่านมา
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {

    session_unset();     // เคลียร์ตัวแปร session
    session_destroy();   // ทำลาย session

    header("Location: auth/login.php?timeout=1"); // กลับหน้า login
    exit;
}

// อัปเดตเวลาใช้งานล่าสุด
$_SESSION['last_activity'] = time();
?>

