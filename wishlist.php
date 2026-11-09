<?php
session_start();
include 'config/db.php';
include 'page/navbar.php';

$wishlist = isset($_SESSION['wishlist']) ? $_SESSION['wishlist'] : [];
?>

<div class="product-body wishlist-body">
    <div class="container mt-100">
        <div class="product-head wishlist-head">
            <div class="wishlist-head-text">
                <h1>My Wishlist <i class="bxf bx-heart" style="color: #d98a91;"></i></h1>
                <p><span id="wishlist-count"><?= count($wishlist) ?></span> items saved</p>
            </div>
            <a href="products.php" class="wishlist-continue">
                <i class='bx bx-arrow-back'></i> Continue shopping
            </a>
        </div>

        <?php if (empty($wishlist)) { ?>
            <div class="empty-wishlist" id="wishlistEmpty">
                <div class="empty-wishlist-icon">
                    <i class='bx bx-heart'></i>
                </div>
                <h3>Your wishlist is empty</h3>
                <p>Save items you like and they will appear here.</p>
                <a href="products.php" class="btn-browse">Browse Products</a>
            </div>
        <?php } else { ?>
            <div class="product-item wishlist-grid-container">
                <div class="product-item-con">
                <?php
                // Get all wishlist products
                $ids = implode(',', array_map('intval', $wishlist));
                $wish_query = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($ids)");
                
                while($row = mysqli_fetch_assoc($wish_query)) {
                    $p_tag = $row['p_tag'] ?? null;
                    $tag_class = ($p_tag === 'NEW') ? 'show-new-product-box' : 'discount-product-box';
                    $prod_id = $row['id'] ?? 0;
                ?>
                    <div class="product-item-grid">
                        <div class="product-item-img">
                            <a href="product-detail.php?id=<?php echo $prod_id; ?>" class="product-img-g img-grid">
                                <img src="<?php echo htmlspecialchars($row['p_image_url'] ?? ''); ?>" alt="<?php echo htmlspecialchars($row['p_name'] ?? ''); ?>" loading="lazy">
                            </a>
                            <div class="cat-overlay"></div>
                        </div>

                        <div class="product-item-title-con">
                            <div class="product-item-title">
                                <p><?php echo htmlspecialchars($row['p_brand'] ?? ''); ?></p>
                                <h4><?php echo htmlspecialchars($row['p_name'] ?? ''); ?></h4>
                                <div class="product-show-title">
                                    <span class="star-con">
                                        <span class="fa fa-star checked"></span>
                                        <span class="fa fa-star checked"></span>
                                        <span class="fa fa-star checked"></span>
                                        <span class="fa fa-star checked"></span>
                                        <span class="fa fa-star star-bg"></span>
                                    </span>
                                    <span class="star-cont">(234)</span>
                                </div>

                                <div class="product-price">
                                    <span class="product-price-w">
                                        $<?php echo number_format($row['p_price'] ?? 0, 2); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="wishlist-actions">
                                <form onsubmit="addToCartAsync(event, this)" style="margin: 0; flex: 1;">
                                    <div class="btn-product-item">
                                        <input type="hidden" name="product_id" value="<?php echo $prod_id; ?>">
                                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['p_name'] ?? ''); ?>">
                                        <input type="hidden" name="product_price" value="<?php echo $row['p_price'] ?? 0; ?>">
                                        <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($row['p_image_url'] ?? ''); ?>">
                                        <button type="submit"><i class='bx bx-cart-add'></i> Add to cart</button>
                                    </div>
                                </form>
                                <button type="button" class="btn-wishlist-remove" title="Remove from wishlist"
                                    onclick="removeFromWishlist(<?= $prod_id ?>, this)">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </div>
                        </div>

                        <div class="show-discount-product">
                            <div class="new-product">
                                <?php if(!empty($p_tag) && $p_tag !== 'NULL') { ?>
                                    <span class="show-product-box <?= $tag_class ?>">
                                        <?php echo htmlspecialchars($p_tag); ?>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="show-icon-love" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist(<?= $prod_id ?>, this)">
                            <div class="icon-love">
                                <i class="bxf bx-heart" style="color: #d98a91;"></i>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                </div>
            </div>

            <div class="empty-wishlist" id="wishlistEmpty" style="display: none;">
                <div class="empty-wishlist-icon">
                    <i class='bx bx-heart'></i>
                </div>
                <h3>Your wishlist is empty</h3>
                <p>Save items you like and they will appear here.</p>
                <a href="products.php" class="btn-browse">Browse Products</a>
            </div>
        <?php } ?>
    </div>
</div>

<?php include 'page/footer.php'; ?>
