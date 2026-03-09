<?php
include '../config/db.php';
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($password !== $password_confirm) {
        $error = 'รหัสผ่านไม่ตรงกัน';
    } elseif (strlen($password) < 6) {
        $error = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    } else {
        // ตรวจสอบ username ซ้ำ
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'มีชื่อผู้ใช้นี้ในระบบแล้ว';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hash);
            if ($stmt->execute()) {
                header('Location: login.php');
                exit();
            } else {
                $error = 'เกิดข้อผิดพลาดในการลงทะเบียน';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>สมัครสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 400px;">
    <h3 class="mb-4">สมัครสมาชิก (Admin)</h3>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="mb-3">
            <label>ชื่อผู้ใช้</label>
            <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label>รหัสผ่าน</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>ยืนยันรหัสผ่าน</label>
            <input type="password" name="password_confirm" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">สมัครสมาชิก</button>
        <a href="login.php" class="btn btn-link mt-3">เข้าสู่ระบบ</a>
    </form>
</div>
</body>
</html>
