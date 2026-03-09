<?php
session_start();
include '../auth/check_login.php';
include '../config/db.php';
include '../log_helper.php';

$id = intval($_GET['id']);
$error = '';

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die('ไม่พบสินค้านี้');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $image = $product['image'];

    // อัปโหลดรูปภาพใหม่ (ถ้ามี)
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_ext)) {
            $error = "ชนิดไฟล์ภาพไม่ถูกต้อง";
        } else {
            $new_filename = uniqid('img_') . '.' . $file_ext;
            $upload_path = '../uploads/' . $new_filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // ลบไฟล์เก่าถ้ามี
                if ($image && file_exists('../uploads/' . $image)) {
                    unlink('../uploads/' . $image);
                }
                $image = $new_filename;
            } else {
                $error = "อัปโหลดไฟล์ภาพล้มเหลว";
            }
        }
    }

    if (!$error && $name && $price > 0) {
		$sort_order = intval($_POST['sort_order']); // เพิ่ม
        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, image = ?, sort_order = ? WHERE id = ?");
		$stmt->bind_param("sdsii", $name, $price, $image, $sort_order, $id);
		$sort_order = intval($_POST['sort_order']); //...
		
$stmt = $conn->prepare("INSERT INTO products (name, price, image, sort_order) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sdsi", $name, $price, $image_string, $sort_order);

        if ($stmt->execute()) {
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "เกิดข้อผิดพลาดในการแก้ไขข้อมูล";
        }
    } elseif (!$error) {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>แก้ไขสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	
	<style>
  body {
    background: #f0f4f8;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2c3e50;
  }
  .container {
    background: #fff;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    margin-top: 2rem;
  }
  h3 {
    font-weight: 700;
    color: #34495e;
    margin-bottom: 1.5rem;
    text-shadow: 1px 1px 1px rgba(0,0,0,0.05);
  }
  label {
    font-weight: 600;
    color: #34495e;
  }
  input.form-control, input[type="file"] {
    border-radius: 8px;
    border: 1.5px solid #d1d9e6;
    padding: 0.5rem 0.75rem;
    box-shadow: inset 0 2px 6px rgb(0 0 0 / 0.05);
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
  }
  input.form-control:focus, input[type="file"]:focus {
    border-color: #4a90e2;
    box-shadow: 0 0 8px rgba(74,144,226,0.5);
    outline: none;
  }
  img {
    border-radius: 12px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.12);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  img:hover {
    transform: scale(1.05);
    box-shadow: 0 14px 28px rgba(0,0,0,0.18);
  }
  .btn-primary {
    border-radius: 8px;
    padding: 0.6rem 1.2rem;
    font-weight: 600;
    background: linear-gradient(135deg, #4a90e2, #357abd);
    border: none;
    box-shadow: 0 6px 15px rgba(74, 144, 226, 0.5);
    transition: background 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
  }
  .btn-primary:hover {
    background: linear-gradient(135deg, #357abd, #2c5ca7);
    box-shadow: 0 10px 20px rgba(53, 122, 189, 0.7);
    transform: translateY(-2px);
  }
  .btn-secondary {
    border-radius: 8px;
    padding: 0.6rem 1.2rem;
    font-weight: 600;
    background-color: #95a5a6;
    border: none;
    color: #fff;
    box-shadow: 0 4px 10px rgba(149, 165, 166, 0.4);
    transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
  }
  .btn-secondary:hover {
    background-color: #7f8c8d;
    box-shadow: 0 8px 18px rgba(127, 140, 141, 0.6);
    transform: translateY(-2px);
  }
  .alert-danger {
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(220,53,69,0.3);
  }
</style>


</head>
<body>
<div class="container mt-4" style="max-width: 600px;">
    <h3>แก้ไขสินค้า</h3>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" action="">
		<div class="mb-3">
			<label>ลำดับการแสดงผล</label>
			<input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($product['sort_order'] ?? 0) ?>" required min="0">
		</div>
        <div class="mb-3">
            <label>ชื่อสินค้า</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required autofocus>
        </div>
        <div class="mb-3">
            <label>ราคา (บาท)</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= number_format($product['price'], 2) ?>" required min="0">
        </div>
        <div class="mb-3">
            <label>รูปภาพสินค้า (ถ้ามี)</label><br>
            <?php if ($product['image'] && file_exists('../uploads/' . $product['image'])): ?>
                <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="รูปสินค้า" style="max-width: 150px; display:block; margin-bottom:10px;">
            <?php endif; ?>
            <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.gif">
        </div>
        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
        <a href="dashboard.php" class="btn btn-secondary">ยกเลิก</a>
    </form>
</div>
</body>
</html>
