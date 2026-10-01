<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';
include 'page/navbar.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$wishlist = isset($_SESSION['wishlist']) ? $_SESSION['wishlist'] : [];

$sql = "SELECT p.*, c.cat_name 
        FROM products p 
        LEFT JOIN categories c ON p.cat_id = c.cat_id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div style='text-align:center; padding:150px; color:#fff;'><h2>Product not found.</h2><a href='products.php' class='btn'>Back to Shop</a></div>";
    include 'page/footer.php';
    exit();
}

$product = $result->fetch_assoc();
$is_wished = in_array($id, $wishlist);

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_review') {
    if (isset($_SESSION['user_id'])) {
        $u_id = (int)$_SESSION['user_id'];
        $rating = (int)$_POST['rating'];
        $comment = mysqli_real_escape_string($conn, $_POST['comment']);
        mysqli_query($conn, "INSERT INTO product_reviews (p_id, user_id, rating, comment) VALUES ($id, $u_id, $rating, '$comment')");
        echo "<script>window.location.href='product-detail.php?id=$id';</script>";
        exit();
    }
}

// Fetch Reviews
$reviews = [];
$avg_rating = 5.0;
$total_reviews = 0;
$rev_q = mysqli_query($conn, "SELECT pr.*, u.username, u.profile_img FROM product_reviews pr JOIN users u ON pr.user_id = u.user_id WHERE p_id = $id ORDER BY created_at DESC");
if ($rev_q) {
    $sum = 0;
    while ($r = mysqli_fetch_assoc($rev_q)) {
        $reviews[] = $r;
        $sum += $r['rating'];
    }
    $total_reviews = count($reviews);
    if ($total_reviews > 0) {
        $avg_rating = round($sum / $total_reviews, 1);
    }
}
?>

<style>
.pd-wrapper {
    background: var(--bg-container-black);
    color: #fff;
    padding: 120px 0 80px 0;
    min-height: 80vh;
}
.pd-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    padding: 0 20px;
}
.pd-image-col {
    position: relative;
    border-radius: 20px;
    background: #11162c;
    border: 1px solid #1e2536;
    padding: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 20px 50px rgba(0,0,0,0.5), inset 0 0 60px rgba(124, 58, 237, 0.1);
}
.pd-image-col img {
    width: 100%;
    max-height: 500px;
    object-fit: contain;
    filter: drop-shadow(0 20px 30px rgba(0,0,0,0.5));
    transition: transform 0.5s ease;
}
.pd-image-col:hover img {
    transform: scale(1.05);
}
.pd-tag {
    position: absolute;
    top: 30px;
    left: 30px;
    padding: 8px 20px;
    background: #00d2ff;
    color: #000;
    font-weight: 800;
    border-radius: 8px;
    text-transform: uppercase;
    font-size: 14px;
}
.pd-discount {
    background: #f43f5e;
    color: #fff;
}
.pd-info-col {
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.pd-brand {
    color: #8b5cf6;
    font-weight: 700;
    font-size: 16px;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 10px;
}
.pd-title {
    font-size: 42px;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 20px;
    color: #f8fafc;
}
.pd-rating {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 30px;
}
.pd-stars {
    color: #fbbf24;
    font-size: 18px;
}
.pd-reviews {
    color: #94a3b8;
    font-size: 15px;
}
.pd-price-row {
    display: flex;
    align-items: baseline;
    gap: 15px;
    margin-bottom: 30px;
}
.pd-price {
    font-size: 36px;
    font-weight: 800;
    color: #fff;
}
.pd-old-price {
    font-size: 20px;
    text-decoration: line-through;
    color: #64748b;
}
.pd-desc {
    color: #94a3b8;
    line-height: 1.8;
    font-size: 16px;
    margin-bottom: 40px;
}
.pd-actions {
    display: flex;
    gap: 20px;
    align-items: center;
}
.pd-qty {
    display: flex;
    align-items: center;
    background: #1e293b;
    border-radius: 12px;
    padding: 5px;
    border: 1px solid #334155;
}
.pd-qty-btn {
    background: transparent;
    border: none;
    color: #fff;
    width: 40px;
    height: 40px;
    font-size: 20px;
    cursor: pointer;
    transition: 0.2s;
}
.pd-qty-btn:hover {
    color: #8b5cf6;
}
.pd-qty-input {
    width: 50px;
    text-align: center;
    background: transparent;
    border: none;
    color: #fff;
    font-size: 18px;
    font-weight: bold;
    outline: none;
}
.pd-add-cart {
    flex: 1;
    background: linear-gradient(90deg, #7c3aed, #4facfe);
    color: #fff;
    border: none;
    padding: 16px 30px;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
    box-shadow: 0 10px 20px rgba(124, 58, 237, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}
.pd-add-cart:hover {
    filter: brightness(1.1);
    transform: translateY(-2px);
    box-shadow: 0 15px 30px rgba(124, 58, 237, 0.5);
}
.pd-wishlist {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    background: #1e293b;
    border: 1px solid #334155;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    cursor: pointer;
    transition: 0.3s;
    color: #94a3b8;
}
.pd-wishlist:hover {
    border-color: #f43f5e;
}
.pd-wished {
    color: #f43f5e;
    border-color: #f43f5e;
}
.pd-meta {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #232b3e;
    color: #64748b;
    font-size: 14px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.pd-meta span strong {
    color: #cbd5e1;
    margin-right: 10px;
}
/* Reviews Section */
.reviews-section {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}
.reviews-card {
    background: rgba(17, 24, 39, 0.6);
    border-radius: 20px;
    padding: 40px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(20px);
}
.review-item {
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    padding: 20px 0;
}
.review-item:last-child {
    border-bottom: none;
}
.review-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 10px;
}
.review-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #7c3aed;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
    color: #fff;
}
.review-meta h4 {
    margin: 0;
    font-size: 15px;
    color: #fff;
}
.review-meta span {
    font-size: 12px;
    color: #64748b;
}
.review-stars {
    color: #fbbf24;
    font-size: 14px;
    margin-bottom: 10px;
}
.review-text {
    color: #94a3b8;
    font-size: 14px;
    line-height: 1.6;
}
.review-form {
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}
.review-form h3 {
    font-size: 18px;
    margin-bottom: 15px;
    color: #fff;
}
.review-form .form-control {
    width: 100%;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #fff;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
}
.review-form select {
    width: auto;
    padding-right: 30px;
}
.btn-submit-review {
    background: linear-gradient(90deg, #7c3aed, #4facfe);
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
}
</style>

<div class="pd-wrapper">
    <div class="pd-container">
        <!-- Left Image -->
        <div class="pd-image-col">
            <?php if (!empty($product['p_tag'])) { ?>
                <div class="pd-tag <?= ($product['p_tag'] != 'NEW') ? 'pd-discount' : '' ?>">
                    <?= htmlspecialchars($product['p_tag']) ?>
                </div>
            <?php } ?>
            <img src="<?= htmlspecialchars($product['p_image_url']) ?>" alt="<?= htmlspecialchars($product['p_name']) ?>">
        </div>

        <!-- Right Info -->
        <div class="pd-info-col">
            <div class="pd-brand"><?= htmlspecialchars($product['p_brand']) ?></div>
            <h1 class="pd-title"><?= htmlspecialchars($product['p_name']) ?></h1>
            
            <div class="pd-rating">
                <div class="pd-stars">
                    <?php
                    $full = floor($avg_rating);
                    $half = ($avg_rating - $full >= 0.5) ? 1 : 0;
                    $empty = 5 - $full - $half;
                    for($i=0; $i<$full; $i++) echo '<i class="bx bxs-star"></i>';
                    if($half) echo '<i class="bx bxs-star-half"></i>';
                    for($i=0; $i<$empty; $i++) echo '<i class="bx bx-star"></i>';
                    ?>
                </div>
                <div class="pd-reviews"><?= number_format($avg_rating, 1) ?> (<?= $total_reviews ?> Reviews)</div>
            </div>

            <div class="pd-price-row">
                <div class="pd-price">$<?= number_format($product['p_price'], 2) ?></div>
                <!-- If you have price_was in your DB you could show it here, using a dummy old price for styling demo if discounted -->
                <?php if ($product['p_tag'] != 'NEW' && !empty($product['p_tag'])) { ?>
                    <div class="pd-old-price">$<?= number_format($product['p_price'] * 1.2, 2) ?></div>
                <?php } ?>
            </div>

            <p class="pd-desc">
                <?= htmlspecialchars($product['p_desc'] ?? 'Experience premium performance and next-level immersion with the ' . $product['p_brand'] . ' ' . $product['p_name'] . '. Designed for ultimate precision and comfort, this gear is built to elevate your setup to professional standards.') ?>
            </p>

            <div class="pd-actions">
                <div class="pd-qty">
                    <button type="button" class="pd-qty-btn" onclick="updateDetailQty(-1)">-</button>
                    <input type="text" id="pd-qty-input" class="pd-qty-input" value="1" readonly>
                    <button type="button" class="pd-qty-btn" onclick="updateDetailQty(1)">+</button>
                </div>

                <button type="button" class="pd-add-cart" onclick="addDetailToCart(<?= $product['id'] ?>, '<?= addslashes($product['p_name']) ?>', <?= $product['p_price'] ?>, '<?= addslashes($product['p_image_url']) ?>')">
                    <i class="bx bx-cart-alt"></i> Add to Cart
                </button>

                <div class="pd-wishlist <?= $is_wished ? 'pd-wished' : '' ?>" id="pdWishlistBtn" onclick="toggleDetailWishlist(<?= $product['id'] ?>)">
                    <i class="<?= $is_wished ? 'bxs' : 'bx' ?> bx-heart"></i>
                </div>
            </div>

            <div class="pd-meta">
                <span><strong>Category:</strong> <?= htmlspecialchars($product['cat_name']) ?></span>
                <span><strong>Availability:</strong> <?= $product['p_stock'] > 0 ? $product['p_stock'] . ' In Stock (Ready to ship)' : 'Out of Stock' ?></span>
                <span><strong>SKU:</strong> TG-<?= strtoupper(substr($product['p_brand'], 0, 3)) ?>-<?= str_pad($product['id'], 4, '0', STR_PAD_LEFT) ?></span>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="reviews-section">
        <div class="reviews-card">
            <h2 style="font-size:24px; font-weight:800; margin-bottom:20px; color:#fff;">Customer Reviews</h2>
            
            <?php if (empty($reviews)): ?>
                <p style="color:#64748b; font-style:italic;">No reviews yet. Be the first to review this product!</p>
            <?php else: ?>
                <?php foreach ($reviews as $r): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="review-avatar"><?= strtoupper(substr($r['username'], 0, 1)) ?></div>
                            <div class="review-meta">
                                <h4><?= htmlspecialchars($r['username']) ?></h4>
                                <span><?= date('M d, Y', strtotime($r['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="review-stars">
                            <?php for($i=1; $i<=5; $i++) echo $i <= $r['rating'] ? '<i class="bx bxs-star"></i>' : '<i class="bx bx-star"></i>'; ?>
                        </div>
                        <div class="review-text">
                            <?= nl2br(htmlspecialchars($r['comment'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="review-form">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <h3>Write a Review</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_review">
                        <select name="rating" class="form-control" required>
                            <option value="5">⭐⭐⭐⭐⭐ - Excellent</option>
                            <option value="4">⭐⭐⭐⭐ - Good</option>
                            <option value="3">⭐⭐⭐ - Average</option>
                            <option value="2">⭐⭐ - Poor</option>
                            <option value="1">⭐ - Terrible</option>
                        </select>
                        <textarea name="comment" class="form-control" rows="4" placeholder="What do you think about this product?" required></textarea>
                        <button type="submit" class="btn-submit-review">Submit Review</button>
                    </form>
                <?php else: ?>
                    <p style="color:#94a3b8;">Please <a href="login.php" style="color:#7c3aed;">log in</a> to write a review.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function updateDetailQty(change) {
    const input = document.getElementById('pd-qty-input');
    let val = parseInt(input.value) + change;
    if (val < 1) val = 1;
    if (val > 10) val = 10;
    input.value = val;
}

function addDetailToCart(id, name, price, image) {
    const qty = parseInt(document.getElementById('pd-qty-input').value);
    
    const formData = new FormData();
    formData.append('product_id', id);
    formData.append('product_name', name);
    formData.append('product_price', price);
    formData.append('product_image', image);
    formData.append('product_qty', qty);
    formData.append('ajax', 1);

    fetch('add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('cart-count').innerText = data.cart_count;
            const newItem = data.item;
            let found = false;
            cartData.forEach(item => {
                if (item.id == newItem.id) {
                    item.qty = parseInt(item.qty) + parseInt(newItem.qty);
                    found = true;
                }
            });
            if (!found) { cartData.push(newItem); }
            
            if (typeof renderCart === "function") { 
                renderCart(); 
            }
            toggleCart(true); // Open sidebar
        } else {
            alert('Failed to add to cart: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function toggleDetailWishlist(productId) {
    const btn = document.getElementById('pdWishlistBtn');
    if (!btn) return;
    const icon = btn.querySelector('i');
    if (!icon) return;
    
    // Check current state before modifying
    const isCurrentlyAdded = icon.classList.contains('bxs');
    
    // Optimistic UI Update
    if (!isCurrentlyAdded) {
        btn.classList.add('pd-wished');
        icon.classList.remove('bx');
        icon.classList.add('bxs');
        icon.style.setProperty('color', '#f43f5e', 'important');
    } else {
        btn.classList.remove('pd-wished');
        icon.classList.remove('bxs');
        icon.classList.add('bx');
        icon.style.setProperty('color', '', '');
        if(icon.getAttribute('style') === '') icon.removeAttribute('style');
    }
    
    fetch('toggle_wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'added') {
            updateWishlistBadge(true);
        } else if (data.status === 'removed') {
            // Nothing extra needed
        }
    })
    .catch(error => {
        console.error('Error toggling wishlist:', error);
        // Revert UI on error
        if (!isCurrentlyAdded) {
            btn.classList.remove('pd-wished');
            icon.classList.remove('bxs');
            icon.classList.add('bx');
            icon.style.setProperty('color', '', '');
        } else {
            btn.classList.add('pd-wished');
            icon.classList.remove('bx');
            icon.classList.add('bxs');
            icon.style.setProperty('color', '#f43f5e', 'important');
        }
    });
}

// Function to update the red dot badge on navbar heart icon
function updateWishlistBadge(hasItems) {
    let badge = document.querySelector('.wishlist-badge');
    if (hasItems) {
        if (!badge) {
            badge = document.createElement('div');
            badge.className = 'wishlist-badge heartbeat';
            document.querySelector('.menu-login-icon-one').appendChild(badge);
        }
    }
}
</script>

<?php include 'page/footer.php'; ?>
