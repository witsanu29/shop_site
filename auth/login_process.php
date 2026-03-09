<?php
session_start();
include '../config/db.php';

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// ตรวจสอบค่าว่าง
if (!$username || !$password) {
    echo "<div class='alert alert-danger p-3'>⚠️ กรุณากรอกชื่อผู้ใช้และรหัสผ่าน</div>";
    exit;
}

// เข้ารหัสรหัสผ่าน (ใช้ SHA1 หรืออื่น ๆ ตามระบบเดิม)
$hashed = sha1($password);

// ตรวจสอบในฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$stmt->bind_param("ss", $username, $hashed);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    // ✅ เข้าสู่ระบบสำเร็จ
    $_SESSION['user'] = $user['username'];
    $_SESSION['role'] = $user['role'] ?? 'user'; // กรณีมี role
    $_SESSION['last_activity'] = time(); // ⏰ เพิ่มบรรทัดนี้เพื่อรองรับ timeout

    header("Location: ../admin/dashboard.php");
    exit;
} else {
    echo "<div class='alert alert-danger p-3'>❌ ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง</div>";
}
?>
