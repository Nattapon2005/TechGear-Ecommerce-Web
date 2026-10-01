<?php
session_start();

// 1. รับค่า Action และ ID ของสินค้าในตะกร้า
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : null;

// 2. ตรวจสอบความถูกต้องของข้อมูล
if ($id !== null && isset($_SESSION['cart'][$id])) {

    if ($action === 'increase') {
        // เพิ่มจำนวนปกติ
        $_SESSION['cart'][$id]['qty']++;
    }
    elseif ($action === 'decrease') {
        // ✨ จุดที่ปรับปรุงตามที่พี่ขอ:
        if ($_SESSION['cart'][$id]['qty'] > 1) {
            // ถ้ามากกว่า 1 ให้ลดจำนวนลงปกติ
            $_SESSION['cart'][$id]['qty']--;
        } else {
            // 🚨 ถ้าเหลือ 1 แล้วกดลด ให้ลบสินค้านั้นออกไปเลย!
            unset($_SESSION['cart'][$id]);
            // จัดเรียงลำดับอาเรย์ใหม่เพื่อให้ระบบไม่รวน
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
    }
    elseif ($action === 'remove') {
        // ปุ่มถังขยะ: ลบออกทันที
        unset($_SESSION['cart'][$id]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
}

// 3. จัดการเสร็จแล้วเด้งกลับหน้าเดิม
if (isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: index.php");
}
exit();