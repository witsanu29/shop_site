<?php
session_start();

$timeout = 1800; // 30 นาที

// ตรวจสอบเวลาที่ผ่านมา
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset(); 
    session_destroy(); 
    header("Location: ../auth/login.php?timeout=1");
    exit;
}

// อัปเดตเวลาใช้งานล่าสุด
$_SESSION['last_activity'] = time();

include '../auth/check_login.php';
include '../config/db.php';
include '../log_helper.php';

$username = $_SESSION['user'] ?? 'guest';
log_page_view(basename(__FILE__), $username);

// ดึงข้อมูลสินค้า
$q = $conn->real_escape_string($_GET['q'] ?? '');
$sql = "SELECT * FROM products";
if ($q) {
    $sql .= " WHERE name LIKE '%$q%'";
}
$sql .= " ORDER BY sort_order ASC, id DESC";
$result = $conn->query($sql);

if (!$result) {
    die("❌ เกิดข้อผิดพลาดในการดึงข้อมูลสินค้า: " . $conn->error);
}


// นับจำนวนสินค้า
$product_count = $conn->query("SELECT COUNT(*) as cnt FROM products")->fetch_assoc()['cnt'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard - ร้านค้าออนไลน์</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        /* โหมดสว่าง */
        body {
            background-color: #f9fafb;
            color: #2e3a59;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        h3 {
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #1e293b;
        }
        .btn-custom {
            border-radius: 0.6rem;
            padding: 0.4rem 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            filter: brightness(0.9);
        }
        .table thead {
            background-color: #3b82f6;
            color: #fff;
            font-weight: 600;
            letter-spacing: 0.03em;
        }
        .table tbody tr:hover {
            background-color: #dbeafe;
        }
        .img-thumbnail {
            border-radius: 0.4rem;
            box-shadow: 0 2px 6px rgb(59 130 246 / 0.25);
            transition: transform 0.25s ease;
        }
        .img-thumbnail:hover {
            transform: scale(1.1);
        }
        .alert-info {
            background-color: #bfdbfe;
            color: #1e40af;
            border: none;
            font-weight: 600;
            border-radius: 0.5rem;
        }

        /* Dark mode */
        body.dark-mode {
            background-color: #121212;
            color: #ddd;
        }
        body.dark-mode h3 {
            color: #bbdefb;
        }
        body.dark-mode .card,
        body.dark-mode .table,
        body.dark-mode .btn {
            background-color: #1e1e1e;
            color: #ddd;
        }
        body.dark-mode .table thead {
            background-color: #375a7f;
            color: #e3f2fd;
        }
        body.dark-mode .table tbody tr:hover {
            background-color: #284a6d;
        }
        body.dark-mode .btn-primary {
            background-color: #375a7f;
            border-color: #375a7f;
        }
        body.dark-mode .btn-secondary {
            background-color: #2c3e50;
            border-color: #2c3e50;
            color: #ccc;
        }
        body.dark-mode .btn:hover {
            filter: brightness(1.15);
        }
        body.dark-mode .img-thumbnail {
            box-shadow: 0 2px 6px rgba(55, 90, 127, 0.6);
        }

        /* Container and spacing */
        .container {
            max-width: 960px;
        }
        .header-actions {
            gap: 12px;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3 header-actions flex-wrap">
        <h3>แดชบอร์ด</h3>
        <div>
             <a href="add_product.php" class="btn btn-success btn-sm btn-custom">➕ เพิ่มสินค้าใหม่</a>
            <a href="../auth/logout.php" class="btn btn-secondary btn-sm btn-custom ms-2">🚪 ออกจากระบบ</a>
        </div>
    </div>

    <?php
    $msg = $_GET['msg'] ?? '';
    if ($msg === 'added') {
        echo '<div class="alert alert-success">เพิ่มสินค้าสำเร็จ</div>';
    } elseif ($msg === 'edited') {
        echo '<div class="alert alert-success">แก้ไขสินค้าสำเร็จ</div>';
    } elseif ($msg === 'deleted') {
        echo '<div class="alert alert-success">ลบสินค้าสำเร็จ</div>';
    }
    ?>

    <div class="alert alert-info">
        สินค้าทั้งหมด: <strong><?= $product_count ?></strong> รายการ
    </div>

    <form method="get" class="mb-3 d-flex" role="search" autocomplete="off">
        <input
            type="text"
            name="q"
            class="form-control me-2"
            placeholder="ค้นหาสินค้า..."
            value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
        />
        <button type="submit" class="btn btn-primary btn-custom">🔍 ค้นหา</button>
        <a href="dashboard.php" class="btn btn-secondary btn-custom ms-2">❌ ล้าง</a>
    </form>

    <table class="table table-striped table-bordered align-middle table-hover">
        <thead>
        <tr>
            <th style="width:5%">ลำดับ</th>
            <th style="width:35%">ชื่อสินค้า</th>
            <th style="width:15%">ราคา (บาท)</th>
            <th style="width:25%">รูปภาพ</th>
            <th style="width:20%">จัดการ</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['sort_order'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= number_format($row['price'], 2) ?></td>
                <td>
                    <?php
                    if (!empty($row['image'])) {
                        $imgs = explode(',', $row['image']);
                        foreach ($imgs as $img) {
                            if (file_exists("../uploads/" . $img)) {
                                echo "<img src='../uploads/" . htmlspecialchars($img) . "' class='img-thumbnail me-1 mb-1' style='max-width: 60px;' loading='lazy'>";
                            }
                        }
                    } else {
                        echo "<span class='text-muted'>ไม่มีรูปภาพ</span>";
                    }
                    ?>
               <td class="d-flex gap-2 flex-wrap">
					<a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">✏️ แก้ไข</a>
					<a href="delete_product.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('คุณแน่ใจว่าจะลบสินค้านี้หรือไม่?')">🗑️ ลบ</a>
    
				<form method="POST" action="../admin/cart_add.php"> <!-- ถ้าเรียกจากหน้าหลัก -->
					<input type="hidden" name="product_id" value="<?= $row['id'] ?>">
					<input type="hidden" name="quantity" value="1">
				<button type="submit" class="btn btn-success btn-sm">🛒 เพิ่มลงตะกร้า</button>
				</form>
				</td>

            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
	function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
}
</script>
</body>
</html>
