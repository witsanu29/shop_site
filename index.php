<?php
session_start();

// ⏰ เช็ค Timeout 30 นาที
if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > 1800) {
    session_unset();
    session_destroy();
    header("Location: auth/login.php?msg=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

include 'config/db.php';
include 'log_helper.php';
$username = $_SESSION['user'] ?? 'guest';
log_page_view(basename(__FILE__), $username);

// ...

$result = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>🛍️ ร้านค้าออนไลน์</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        <style>
body {
    background: linear-gradient(135deg, #f0f4f8, #dbeafe);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.product-card {
    border: none;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    transition: transform 0.25s ease, box-shadow 0.3s ease;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
}

.card-img-top {
    height: 200px;
    object-fit: cover;
}

.card-body {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.btn-success {
    background-color: #10b981;
    border-color: #10b981;
}

.btn-success:hover {
    background-color: #059669;
    border-color: #059669;
}

.btn-outline-primary {
    border-radius: 0.6rem;
}

.dark-mode {
    background-color: #121212 !important;
    color: #eee !important;
}

.dark-mode .card {
    background-color: #1f2937;
    color: #eee;
    border: none;
}

.dark-mode .btn-success {
    background-color: #22c55e;
}

.dark-mode .btn-outline-primary {
    border-color: #60a5fa;
    color: #93c5fd;
}

.dark-mode .btn-outline-primary:hover {
    background-color: #60a5fa;
    color: white;
}

@media (max-width: 576px) {
    .card-body {
        padding: 1rem;
    }
}
</style>

    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🛒 รายการสินค้า</h2>
        <div>
            <button class="btn btn-outline-secondary me-2" onclick="toggleDarkMode()">🌓 สลับธีม</button>
            <a href="auth/login.php" class="btn btn-primary">🔐 เข้าสู่ระบบ Admin</a>
        </div>
    </div>

    <div class="row">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
    <div class="card product-card h-100">
        <?php if (!empty($row['image']) && file_exists('uploads/' . $row['image'])): ?>
            <img src="uploads/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>">
        <?php else: ?>
            <img src="https://via.placeholder.com/300x200?text=No+Image" class="card-img-top" alt="No Image">
        <?php endif; ?>
        <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-1"><?= htmlspecialchars($row['name']) ?></h5>
            <p class="card-text text-success fw-bold mb-1">฿ <?= number_format($row['price'], 2) ?></p>
            <p class="card-text text-muted mb-3">เหลือ: <?= (int)$row['stock'] ?> ชิ้น</p>

            <?php if ((int)$row['stock'] > 0): ?>
                <form method="POST" action="../admin/cart_add.php" class="mb-2">
                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                    <div class="input-group mb-2" style="max-width: 140px;">
                        <input type="number" name="quantity" value="1" min="1" max="<?= $row['stock'] ?>" class="form-control text-center">
                        <button type="submit" class="btn btn-success">🛒</button>
                    </div>
                </form>
            <?php else: ?>
                <button class="btn btn-secondary w-100 mb-2" disabled>❌ สินค้าหมด</button>
            <?php endif; ?>

            <a href="product_detail.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary w-100">🔍 ดูรายละเอียด</a>
        </div>
    </div>
</div>

        <?php endwhile; ?>
    </div>
</div>

<script>
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
}
</script>
</body>
</html>
