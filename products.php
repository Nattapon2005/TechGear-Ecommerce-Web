<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

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

// AJAX filtering: return only the product grid so the page never reloads.
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    ob_start();
    include 'page/products-grid.php';
    $grid_html = ob_get_clean();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'html' => $grid_html,
        'count' => (int)$product_count
    ]);
    exit;
}

include 'page/navbar.php';
?>

<div class="product-body">
    <div class="container mt-100">
        <div class="product-head">
            <h1>All Products</h1>
            <p><span id="product-count"><?= $product_count ?></span> items available</p>
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
                            <h3 id="priceTitle">Max Price ($<?= htmlspecialchars($max_price) ?>)</h3>
                            <input type="range" name="max_price" id="priceRange" min="0" max="1000" value="<?= htmlspecialchars($max_price) ?>" style="width: 100%; margin: .9375rem 0;">
                            <div class="filter-product-price">
                                <span>$0</span>
                                <span>$1000</span>
                            </div>
                        </div>
                    </form>

                    <button class="filter-button" id="clearFilters" type="button">
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
                        <?php include 'page/products-grid.php'; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>

<?php include 'page/footer.php'; ?>
