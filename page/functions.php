<?php
function startSession() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['sid'])) $_SESSION['sid'] = bin2hex(random_bytes(16));
    return $_SESSION['sid'];
}

function getCategories($pdo) {
    return $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
}

function getCartCount($pdo, $sid) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE session_id=?");
    $stmt->execute([$sid]);
    return (int)$stmt->fetchColumn();
}

function getWishlistIds($pdo, $sid) {
    $stmt = $pdo->prepare("SELECT product_id FROM wishlist WHERE session_id=?");
    $stmt->execute([$sid]);
    return array_column($stmt->fetchAll(), 'product_id');
}

function slugify($text) {
    return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $text), '-'));
}

function formatPrice($p) { return '$' . number_format($p, 2); }

function stars($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $rating >= $i ? '<span class="star">★</span>' : '<span class="star empty">★</span>';
    }
    return $html;
}

function redirect($url) {
    header("Location: $url"); exit;
}
