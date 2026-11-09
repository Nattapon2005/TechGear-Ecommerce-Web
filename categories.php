<?php
include 'config/db.php';
include 'page/navbar.php';

// ดึง Categories ทั้งหมดและนับจำนวนสินค้าจริงจากฐานข้อมูล
$sql = "SELECT c.*, COUNT(p.id) as real_product_count 
        FROM categories c 
        LEFT JOIN products p ON c.cat_id = p.cat_id 
        GROUP BY c.cat_id 
        ORDER BY c.cat_id";
$cat_query = mysqli_query($conn, $sql);
$categories = [];
if ($cat_query) {
    while($row = mysqli_fetch_assoc($cat_query)) {
        $categories[] = $row;
    }
}
?>

    <!--========================= Categories Hero ==========================-->
    <section class="cat-hero">
        <div class="container">
            <div class="cat-hero-content">
                <div class="cat-hero-badge">
                    <span class="badge-dot"></span>
                    EXPLORE
                </div>
                <h1 class="cat-hero-title">
                    Browse <span class="gradient-text">Categories</span>
                </h1>
                <p class="cat-hero-desc">
                    Discover premium gaming gear organized by category. 
                    Find the perfect equipment for your ultimate setup.
                </p>

                <!-- Floating Category Icons from Database -->
                <div class="hero-floating-icons">
                    <?php 
                    $positions = [
                        ['top' => '8%',  'left' => '8%',   'delay' => '0s',    'dur' => '7s'],
                        ['top' => '15%', 'right' => '12%', 'delay' => '-1.5s', 'dur' => '9s'],
                        ['top' => '55%', 'left' => '5%',   'delay' => '-3s',   'dur' => '8s'],
                        ['top' => '65%', 'right' => '8%',  'delay' => '-2s',   'dur' => '10s'],
                        ['top' => '35%', 'left' => '15%',  'delay' => '-4s',   'dur' => '7.5s'],
                        ['top' => '25%', 'right' => '25%', 'delay' => '-1s',  'dur' => '8.5s'],
                        ['top' => '75%', 'left' => '20%',  'delay' => '-5s',   'dur' => '9s'],
                        ['top' => '10%', 'left' => '30%',  'delay' => '-2.5s', 'dur' => '7s'],
                        ['top' => '50%', 'right' => '18%', 'delay' => '-3.5s', 'dur' => '8s'],
                        ['top' => '80%', 'right' => '30%', 'delay' => '-4.5s', 'dur' => '9.5s'],
                    ];
                    foreach($categories as $i => $cat): 
                        $p = $positions[$i % count($positions)];
                        $icon_class = !empty($cat['cat_icon']) ? $cat['cat_icon'] : 'bx-category';
                        $inline_style = "animation-delay: {$p['delay']}; animation-duration: {$p['dur']};";
                        if (isset($p['top'])) $inline_style .= " top: {$p['top']};";
                        if (isset($p['left'])) $inline_style .= " left: {$p['left']};";
                        if (isset($p['right'])) $inline_style .= " right: {$p['right']};";
                    ?>
                        <div class="floating-icon" style="<?= $inline_style ?>" title="<?= htmlspecialchars($cat['cat_name']) ?>">
                            <i class='bx <?= htmlspecialchars($icon_class) ?>'></i>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Animated Background -->
        <div class="cat-hero-bg">
            <div class="grid-overlay"></div>
            <div class="floating-orb orb-1"></div>
            <div class="floating-orb orb-2"></div>
            <div class="floating-orb orb-3"></div>
            <div class="scan-line"></div>
        </div>

        <style>
        /* ===== Floating Category Icons ===== */
        .hero-floating-icons {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            overflow: hidden;
        }
        .floating-icon {
            position: absolute;
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: rgba(201, 172, 130, 0.08);
            border: 1px solid rgba(201, 172, 130, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(167, 139, 250, 0.5);
            font-size: 24px;
            backdrop-filter: blur(6px);
            animation: floatIcon 8s ease-in-out infinite;
            transition: all .3s;
        }
        .floating-icon:hover {
            background: rgba(201, 172, 130, 0.2);
            color: rgba(167, 139, 250, 0.9);
            border-color: rgba(201, 172, 130, 0.4);
            transform: scale(1.2);
            pointer-events: auto;
        }

        @keyframes floatIcon {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25%  { transform: translateY(-15px) rotate(5deg); }
            50%  { transform: translateY(5px) rotate(-3deg); }
            75%  { transform: translateY(-8px) rotate(2deg); }
        }

        /* Larger icons on wider screens */
        @media(min-width: 1200px) {
            .floating-icon { width: 64px; height: 64px; font-size: 28px; border-radius: 16px; }
        }
        @media(max-width: 768px) {
            .floating-icon { width: 40px; height: 40px; font-size: 18px; border-radius: 10px; }
        }
        </style>
    </section>


    <!--========================= Categories Grid ==========================-->
    <section class="cat-grid-section">
        <div class="container">
            <div class="cat-grid">

                <?php 
                foreach($categories as $index => $record) { 
                    $cat_icon = !empty($record['cat_icon']) ? $record['cat_icon'] : 'bx-category';
                    $count = isset($record['real_product_count']) ? $record['real_product_count'] : $record['product_count'];
                ?>
                <a href="products.php?cat_id=<?php echo $record['cat_id']; ?>" 
                   class="cat-card" 
                   style="--delay: <?= $index * 0.1 ?>s">
                    <div class="cat-card-img">
                        <img src="<?php echo htmlspecialchars($record['cat_image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($record['cat_name']); ?>">
                        <div class="cat-card-overlay"></div>
                    </div>
                    <div class="cat-card-content">
                        <div class="cat-card-icon">
                            <i class="bx <?= htmlspecialchars($cat_icon) ?>"></i>
                        </div>
                        <h3 class="cat-card-title"><?php echo htmlspecialchars($record['cat_name']); ?></h3>
                        <p class="cat-card-count"><?php echo $count; ?> Products</p>
                        <div class="cat-card-action">
                            <span>Explore</span>
                            <i class="bx bx-right-arrow-alt"></i>
                        </div>
                    </div>
                    <div class="cat-card-glow"></div>
                    <div class="cat-card-border"></div>
                </a>
                <?php } ?>

            </div>
        </div>
    </section>


    <!--========================= Why Choose Section ==========================-->
    <section class="why-choose-section">
        <div class="container">
            <div class="why-header">
                <h2>Why Choose <span class="gradient-text">TechGear</span></h2>
                <p>We deliver the best gaming equipment experience</p>
            </div>
            <div class="why-grid">
                <div class="why-card">
                    <div class="why-icon">
                        <i class="bx bx-shield-check"></i>
                    </div>
                    <h4>Authentic Products</h4>
                    <p>100% genuine products from authorized distributors with full warranty coverage.</p>
                    <div class="why-card-line"></div>
                </div>
                <div class="why-card">
                    <div class="why-icon">
                        <i class="bx bx-rocket"></i>
                    </div>
                    <h4>Fast Delivery</h4>
                    <p>Express shipping available. Get your gear delivered within 24-48 hours.</p>
                    <div class="why-card-line"></div>
                </div>
                <div class="why-card">
                    <div class="why-icon">
                        <i class="bx bx-support"></i>
                    </div>
                    <h4>Expert Support</h4>
                    <p>Our gaming specialists are available 24/7 to help you choose the right gear.</p>
                    <div class="why-card-line"></div>
                </div>
                <div class="why-card">
                    <div class="why-icon">
                        <i class="bx bx-refresh"></i>
                    </div>
                    <h4>Easy Returns</h4>
                    <p>30-day hassle-free return policy. No questions asked, full refund guaranteed.</p>
                    <div class="why-card-line"></div>
                </div>
            </div>
        </div>
    </section>


<?php include 'page/footer.php'; ?>
