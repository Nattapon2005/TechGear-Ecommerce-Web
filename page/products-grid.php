<?php
if ($product_count == 0) {
    echo "<div class='no-products-found' style='width: 100%; grid-column: 1 / -1; text-align: center; padding: 50px 0; color: #8d9098;'><h3>No products found</h3><p>Try adjusting your filters</p></div>";
}
while($row = mysqli_fetch_assoc($result)) {
    $p_tag = $row['p_tag'] ?? null;
    $tag_class = ($p_tag === 'NEW') ? 'show-new-product-box' : 'discount-product-box';
    $prod_id = $row['id'] ?? 0;
    $is_wished = in_array($prod_id, $wishlist);
?>
    <div class="product-item-grid">
        <div class="product-item-img " id="">
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

            <form onsubmit="addToCartAsync(event, this)" style="margin: 0;">
                <div class="btn-product-item">
                    <input type="hidden" name="product_id" value="<?php echo $prod_id; ?>">
                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['p_name'] ?? ''); ?>">
                    <input type="hidden" name="product_price" value="<?php echo $row['p_price'] ?? 0; ?>">
                    <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($row['p_image_url'] ?? ''); ?>">
                    <button type="submit"><i class='bx bx-cart-add'></i> Add to cart</button>
                </div>
            </form>
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

        <div class="show-icon-love" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist(<?= $prod_id ?>, this)" style="cursor:pointer; z-index: 10;">
            <div class="icon-love">
                <i class="<?= $is_wished ? 'bxf' : 'bx' ?> bx-heart" style="<?= $is_wished ? 'color: #d98a91;' : '' ?>"></i>
            </div>
        </div>
    </div>
<?php } ?>
