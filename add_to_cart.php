<?php
session_start();
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $p_id    = $_POST['product_id'];
    $p_name  = $_POST['product_name'];
    $p_price = $_POST['product_price'];
    $p_image = $_POST['product_image'];
    $p_qty   = isset($_POST['product_qty']) ? intval($_POST['product_qty']) : 1;

    if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $p_id) {
            $item['qty'] += $p_qty;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = ['id' => $p_id, 'name' => $p_name, 'price' => $p_price, 'image' => $p_image, 'qty' => $p_qty];
    }

    //  จุดสำคัญ: ต้องส่ง "item" กลับไปให้หน้า Index ด้วย
    if (isset($_POST['ajax'])) {
        echo json_encode([
            "status" => "success",
            "cart_count" => count($_SESSION['cart']),
            "item" => [ // ส่งก้อนนี้กลับไปให้ JS วาดรูป
                "id" => $p_id,
                "name" => $p_name,
                "price" => $p_price,
                "image" => $p_image,
                "qty" => $p_qty
            ]
        ]);
        exit();
    }
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}