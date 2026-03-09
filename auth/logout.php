<?php
session_start();

// 🔐 log การออกจากระบบ
if (isset($_SESSION['user'])) {
    $log = "ออกจากระบบ: " . $_SESSION['user'] . " เวลา: " . date("Y-m-d H:i:s") . "\n";
    file_put_contents("../logs/user_logs.txt", $log, FILE_APPEND);
}

// 🔄 ล้าง session
$_SESSION = []; // ล้างตัวแปร session ทั้งหมด
session_destroy(); // ทำลาย session ปัจจุบัน
setcookie(session_name(), '', time() - 3600); // เคลียร์ cookie ที่เก็บ session id ด้วย

// 🔁 redirect ไปหน้าแรก
header("Location: ../index.php");

exit();
?>
