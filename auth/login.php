<?php
session_start();
include '../config/db.php';
include '../log_helper.php'; // ✅ แก้ path ให้ถูก

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['username'];

            log_user_action("เข้าสู่ระบบ: $username");

            header("Location: ../admin/dashboard.php");
            exit();
        } else {
            $error = "⚠️ รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $error = "⚠️ ไม่พบผู้ใช้นี้ในระบบ";
    }
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>🔐 เข้าสู่ระบบ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            max-width: 420px;
            width: 100%;
        }
    </style>
</head>
<body>
<div class="login-card">
    <h3 class="text-center mb-4">🔐 เข้าสู่ระบบ</h3>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
	
	<?php if (isset($_GET['msg']) && $_GET['msg'] === 'timeout'): ?>
		<div class="alert alert-warning">⏳ หมดเวลาใช้งาน กรุณาเข้าสู่ระบบอีกครั้ง</div>
	<?php endif; ?>

	<?php if (isset($_GET['timeout'])): ?>
	<div class="alert alert-warning">
			⏰ ระบบได้ออกจากระบบอัตโนมัติ เนื่องจากไม่มีการใช้งานนานเกินไป
	</div>
	<?php endif; ?>


    <form method="post" action="">
        <div class="mb-3">
            <label for="username" class="form-label">👤 ชื่อผู้ใช้</label>
            <input type="text" name="username" id="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">🔑 รหัสผ่าน</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">✅ เข้าสู่ระบบ</button>
    </form>
</div>
</body>
</html>
