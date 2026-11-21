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

function fixImageUrl($url) {
    if (empty($url)) {
        return 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=800&q=80';
    }
    // แก้ไข URL รูปที่ตาย/หมดอายุจาก Unsplash
    $broken_maps = [
        'photo-1527814050087-179f0011abf4' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=800&q=80',
        'photo-1600080972464-8e5f2840d437' => 'https://images.unsplash.com/photo-1612287233002-91d0bc5367ff?w=800&q=80',
        'photo-1615663245857-ac9310031881' => 'https://images.unsplash.com/photo-1617233083187-be4925d699d6?q=80&w=500',
    ];
    foreach ($broken_maps as $broken => $fixed) {
        if (str_contains($url, $broken)) {
            return $fixed;
        }
    }
    return $url;
}
