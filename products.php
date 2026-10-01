<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';
include 'page/navbar.php';

$cat_id = isset($_GET['cat_id']) ? $_GET['cat_id'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : 1000;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'new';
$wishlist = isset($_SESSION['wishlist']) ? $_SESSION['wishlist'] : [];

$sql = "SELECT * FROM products WHERE p_price <= " . (float)$max_price;

if ($cat_id != 'all') {
    $sql .= " AND cat_id = '" . mysqli_real_escape_string($conn, $cat_id) . "'";
}

if ($search != '') {
    $sql .= " AND p_name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
}

switch($sort) {
    case 'low': $sql .= " ORDER BY p_price ASC"; break;
    case 'high': $sql .= " ORDER BY p_price DESC"; break;
    case 'rating': $sql .= " ORDER BY id DESC"; break;
    case 'az': $sql .= " ORDER BY p_name ASC"; break;
    case 'new': 
    default: $sql .= " ORDER BY created_at DESC"; break;
}

$result = mysqli_query($conn, $sql);
$product_count = mysqli_num_rows($result);
?>

<div class="product-body">
    <div class="container mt-100">
        <div class="product-head">
            <h1>All Products</h1>
            <p><?= $product_count ?> items available</p>
        </div>

        <div class="products-layout">
            <aside class="filter-sidebar">
                <div class="filter-card">
                    <div class="filter-group">
                        <h3>Categories</h3>
                        <div class="cat-list">
                            <a href="products.php" class="cat-link <?= ($cat_id == 'all') ? 'active' : '' ?>">All</a>
                            <?php
                            $cat_query = mysqli_query($conn, "SELECT * FROM categories ORDER BY cat_id");
                            while($c = mysqli_fetch_assoc($cat_query)) {
                            ?>
                                <a href="products.php?cat_id=<?= $c['cat_id'] ?>" class="cat-link <?= ($cat_id == $c['cat_id']) ? 'active' : '' ?>">
                                    <?php if(!empty($c['cat_icon'])): ?><i class="bx <?= htmlspecialchars($c['cat_icon']) ?>" style="margin-right:6px;"></i><?php endif; ?>
                                    <?= htmlspecialchars($c['cat_name']) ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>

                    <form action="products.php" method="GET" id="filterForm">
                        <?php if($cat_id != 'all') { ?>
                            <input type="hidden" name="cat_id" value="<?= htmlspecialchars($cat_id) ?>">
                        <?php } ?>
                        <?php if($search != '') { ?>
                            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                        <?php } ?>
                        <?php if($sort != 'new') { ?>
                            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                        <?php } ?>

                        <div class="filter-product-con">
                            <h3>Max Price ($<?= htmlspecialchars($max_price) ?>)</h3>
                            <input type="range" name="max_price" min="0" max="1000" value="<?= htmlspecialchars($max_price) ?>" style="width: 100%; margin: .9375rem 0;" onchange="this.form.submit()">
                            <div class="filter-product-price">
                                <span>$0</span>
                                <span>$1000</span>
                            </div>
                        </div>
                    </form>

                    <button class="filter-button" onclick="window.location.href='products.php'">
                        Clear All Filters
                    </button>
                </div>
            </aside>

            <main class="products-content">
                <div class="toolbar">
                    <form action="products.php" method="GET" class="search-bar-lg">
                        <i class='bx bx-search icon'></i>
                        <input type="text" name="search" placeholder="Search for gear..." value="<?= htmlspecialchars($search) ?>">
                        <?php if($cat_id != 'all') { ?>
                            <input type="hidden" name="cat_id" value="<?= htmlspecialchars($cat_id) ?>">
                        <?php } ?>
                        <input type="hidden" name="max_price" value="<?= htmlspecialchars($max_price) ?>">
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                    </form>

                    <!--------------------- sort-box --------------------->
                    <div class="sort-box">
                        <div class="dropdown" id="sortDropdown">
                            <div class="selected" id="sortTrigger">
                                <?php 
                                $sortLabels = [
                                    'new' => 'Newest',
                                    'low' => 'Price: Low to High',
                                    'high' => 'Price: High to Low',
                                    'rating' => 'Rating',
                                    'az' => 'Name A-Z'
                                ];
                                ?>
                                <h5 id="sortLabel"><?= $sortLabels[$sort] ?></h5>
                                <i class="bx bx-chevron-down"></i>
                            </div>

                            <div class="options">
                                <?php foreach($sortLabels as $key => $label): ?>
                                <div class="options-box <?= $sort == $key ? 'active' : '' ?>" data-value="<?= $key ?>">
                                    <?= $label ?>
                                    <i class="bx bx-check" style="<?= $sort == $key ? 'opacity:1;' : 'opacity:0;' ?>"></i>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="view-toggle">
                            <button class="view-btn active" id="gridBtn" title="Grid view">
                                <i class="bx bx-table-cells"></i>
                            </button>
                            <button class="view-btn" id="listBtn" title="List view">
                                <i class="bx bx-list-ul"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!---------------------------- product-grid-lg ---------------------------->
                <div class="product-grid-lg">
                    <div class="container">
                        <div class="product-item-con-grid grid-view" id="productsGrid">
                        <?php
                        if ($product_count == 0) {
                            echo "<div style='width: 100%; grid-column: 1 / -1; text-align: center; padding: 50px 0; color: #94a3b8;'><h3>No products found</h3><p>Try adjusting your filters</p></div>";
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
                                        <img src="<?php echo htmlspecialchars($row['p_image_url'] ?? ''); ?>" alt="">
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
                                            <button type="submit">Add to cart</button>
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
                                        <i class="<?= $is_wished ? 'bxf' : 'bx' ?> bx-heart" style="<?= $is_wished ? 'color: #f43f5e;' : '' ?>"></i>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>

<script>
// Handle Sort Dropdown Logic
document.querySelectorAll('.options-box').forEach(item => {
    item.addEventListener('click', function(e) {
        let sortVal = this.getAttribute('data-value');
        let currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('sort', sortVal);
        window.location.href = currentUrl.href;
    });
});
</script>

<?php include 'page/footer.php'; ?>
