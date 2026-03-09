<?php
require_once __DIR__ . '/vendor/autoload.php';
session_start();
include '../config/db.php';

$order_id = $_SESSION['order_id'];
$order = $conn->query("SELECT * FROM orders WHERE id=$order_id")->fetch_assoc();
$items = $conn->query("SELECT * FROM order_items WHERE order_id=$order_id");

$html = "<h2>🧾 ใบเสร็จรับเงิน</h2>
<p>ชื่อ: {$order['customer_name']}<br>
อีเมล: {$order['customer_email']}<br>
เบอร์โทร: {$order['customer_phone']}<br>
วันที่: " . date("Y-m-d H:i") . "</p>
<table border='1' width='100%' style='border-collapse: collapse;'>
<tr><th>สินค้า</th><th>จำนวน</th><th>ราคา/ชิ้น</th><th>รวม</th></tr>";

while ($item = $items->fetch_assoc()) {
    $sum = $item['price'] * $item['quantity'];
    $html .= "<tr>
                <td>{$item['product_name']}</td>
                <td align='center'>{$item['quantity']}</td>
                <td align='right'>" . number_format($item['price'], 2) . "</td>
                <td align='right'>" . number_format($sum, 2) . "</td>
              </tr>";
}
$html .= "<tr><td colspan='3' align='right'><strong>รวม</strong></td><td align='right'><strong>" . number_format($order['total'], 2) . "</strong></td></tr>";
$html .= "</table>";

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output("receipt_{$order_id}.pdf", 'I'); // I = แสดงใน browser
