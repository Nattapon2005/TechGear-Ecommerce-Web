<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id']) && isset($_FILES['slip_image'])) {
    $order_id = $_POST['order_id'];
    
    // Verify order belongs to user
    $check_stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $order_id, $user_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows == 0) {
        header("Location: orders.php");
        exit();
    }
    
    $target_dir = "imgs/slips/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = $_FILES["slip_image"]["name"];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $new_filename = "slip_" . $order_id . "_" . time() . "." . $file_ext;
    $target_file = $target_dir . $new_filename;
    
    $allowed = array("jpg", "jpeg", "png", "webp");
    if (in_array($file_ext, $allowed)) {
        if (move_uploaded_file($_FILES["slip_image"]["tmp_name"], $target_file)) {
            $slip_path = $target_file;
            $update_stmt = $conn->prepare("UPDATE orders SET slip_image = ?, slip_status = 'pending' WHERE order_id = ?");
            $update_stmt->bind_param("si", $slip_path, $order_id);
            $update_stmt->execute();
        }
    }
    
    header("Location: order-detail.php?id=" . $order_id);
    exit();
} else {
    header("Location: orders.php");
    exit();
}
?>
