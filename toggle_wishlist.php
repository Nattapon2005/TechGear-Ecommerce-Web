<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    
    if ($product_id > 0) {
        if (!isset($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = [];
        }
        
        $index = array_search($product_id, $_SESSION['wishlist']);
        if ($index !== false) {
            // Remove from wishlist
            unset($_SESSION['wishlist'][$index]);
            $_SESSION['wishlist'] = array_values($_SESSION['wishlist']); // re-index
            echo json_encode(["status" => "removed"]);
        } else {
            // Add to wishlist
            $_SESSION['wishlist'][] = $product_id;
            echo json_encode(["status" => "added"]);
        }
        exit();
    }
}
echo json_encode(["status" => "error"]);
?>
