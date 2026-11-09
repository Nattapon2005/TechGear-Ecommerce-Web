<?php
include 'config/db.php';
include 'page/navbar.php';

$sql = "SELECT * FROM categories";
$cat_query = mysqli_query($conn, $sql);

?>


    <!--========================= Hero-Section (Scroll Sequence) ==========================-->
    <style>
        .scroll-sequence-section {
            position: relative;
            height: 350vh; /* Scroll length */
            background: #0b0c0e;
            margin-top: -80px; /* Offset for navbar */
        }

        .sticky-container {
            position: sticky;
            top: 0;
            height: 100vh;
            width: 100%;
            overflow: hidden;
            background: #0b0c0e;
        }

        canvas#hero-lightpass {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            max-width: 100vw;
            max-height: 100vh;
            object-fit: cover;
        }

        .scroll-text-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10;
            color: #f3f1ec;
            text-align: center;
            pointer-events: none;
        }

        .scroll-text-overlay h1 {
            font-size: 5rem;
            font-weight: 900;
            margin: 0;
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.4s ease, transform 0.4s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
            background: linear-gradient(to right, #c9ac82, #8a6f4d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .scroll-text-overlay p {
            font-size: 1.5rem;
            color: #ccc;
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.4s ease 0.1s, transform 0.4s ease 0.1s;
            margin-top: 10px;
        }

        .scroll-text-overlay.active h1,
        .scroll-text-overlay.active p {
            opacity: 1;
            transform: translateY(0);
        }

        .scroll-indicator-down {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            color: #f3f1ec;
            font-size: 0.9rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            opacity: 0.8;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            animation: bounce-scroll 2s infinite;
            z-index: 20;
            transition: opacity 0.3s ease;
        }

        @keyframes bounce-scroll {
            0%, 100% { transform: translate(-50%, 0); }
            50% { transform: translate(-50%, 10px); }
        }
        
        @media (max-width: 768px) {
            .scroll-text-overlay h1 {
                font-size: 3rem;
            }
            .scroll-text-overlay p {
                font-size: 1.2rem;
            }
        }
    </style>

    <div class="scroll-sequence-section" id="scrollSequence">
        <div class="sticky-container">
            <canvas id="hero-lightpass"></canvas>
            <div class="scroll-text-overlay" id="textOverlay">
                <h1 id="mainTitle">TechGear</h1>
                <p id="subTitle">Precision sound. Every frame.</p>
            </div>
            <div class="scroll-indicator-down" id="scrollIndicator">
                Scroll Down
                <i class='bx bx-chevron-down' style="font-size: 2rem;"></i>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById("hero-lightpass");
            if (!canvas) return;
            const context = canvas.getContext("2d");

            // Apple AirPods sequence used as a stunning demonstration for scroll-frames
            const frameCount = 147;
            const currentFrame = index => (
                `https://www.apple.com/105/media/us/airpods-pro/2019/1299e2f5_9206_4470_b28e_08307a42f19b/anim/sequence/large/01-hero-lightpass/${(index + 1).toString().padStart(4, '0')}.jpg`
            );

            const images = [];
            const seq = { frame: 0 };

            // Preload images
            for (let i = 0; i < frameCount; i++) {
                const img = new Image();
                img.src = currentFrame(i);
                images.push(img);
            }

            images[0].onload = render;

            function render() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
                
                const img = images[seq.frame];
                if (!img || !img.complete) return;

                const imgW = img.width || 1158;
                const imgH = img.height || 770;
                
                const ratio = Math.max(canvas.width / imgW, canvas.height / imgH);
                const newWidth = imgW * ratio;
                const newHeight = imgH * ratio;
                
                const offsetX = (canvas.width - newWidth) / 2;
                const offsetY = (canvas.height - newHeight) / 2;

                context.clearRect(0, 0, canvas.width, canvas.height);
                context.drawImage(img, offsetX, offsetY, newWidth, newHeight);
            }

            window.addEventListener("resize", render);

            const section = document.getElementById('scrollSequence');
            const textOverlay = document.getElementById('textOverlay');
            const mainTitle = document.getElementById('mainTitle');
            const subTitle = document.getElementById('subTitle');
            const scrollIndicator = document.getElementById('scrollIndicator');

            const texts = [
                { start: 0.05, end: 0.25, title: "Immersive Audio", sub: "Experience every detail." },
                { start: 0.35, end: 0.60, title: "Precision Engineering", sub: "Designed for gamers." },
                { start: 0.70, end: 0.90, title: "Next-Gen Setup", sub: "Upgrade your gear." }
            ];

            window.addEventListener('scroll', () => {
                const rect = section.getBoundingClientRect();
                const sectionTop = rect.top;
                
                // Calculate how much we've scrolled inside the section
                let scrollFraction = 0;
                if (sectionTop < 0) {
                    const maxScroll = section.scrollHeight - window.innerHeight;
                    scrollFraction = Math.max(0, Math.min(1, Math.abs(sectionTop) / maxScroll));
                }

                // Hide indicator once started scrolling
                if (scrollFraction > 0.02) {
                    scrollIndicator.style.opacity = '0';
                } else {
                    scrollIndicator.style.opacity = '0.8';
                }

                const frameIndex = Math.min(
                    frameCount - 1,
                    Math.floor(scrollFraction * frameCount)
                );
                
                if (seq.frame !== frameIndex) {
                    seq.frame = frameIndex;
                    requestAnimationFrame(render);
                }

                // Update text
                let activeText = null;
                for (let t of texts) {
                    if (scrollFraction >= t.start && scrollFraction <= t.end) {
                        activeText = t;
                        break;
                    }
                }

                if (activeText) {
                    if (!textOverlay.classList.contains('active') || mainTitle.innerText !== activeText.title) {
                        mainTitle.innerText = activeText.title;
                        subTitle.innerText = activeText.sub;
                        textOverlay.classList.add('active');
                    }
                } else {
                    textOverlay.classList.remove('active');
                }
            });
        });
    </script>
    <!--========================= End Hero-Section ==========================-->

    <!--========================= Hero-Section 2 (Scroll Sequence 2) ==========================-->
    <div class="scroll-sequence-section" id="scrollSequence2" style="margin-top: 0; background: #0b0c0e;">
        <div class="sticky-container" style="background: #0b0c0e;">
            <canvas id="hero-head-bob"></canvas>
            <div class="scroll-text-overlay" id="textOverlay2">
                <h1 id="mainTitle2">Pro Series</h1>
                <p id="subTitle2">Designed for the elite.</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas2 = document.getElementById("hero-head-bob");
            if (!canvas2) return;
            const context2 = canvas2.getContext("2d");

            // Second sequence (AirPods Pro head bob) to act as a placeholder for gaming hardware
            const frameCount2 = 131;
            const currentFrame2 = index => (
                `https://www.apple.com/105/media/us/airpods-pro/2019/1299e2f5_9206_4470_b28e_08307a42f19b/anim/sequence/large/02-head-bob-turn/${(index + 1).toString().padStart(4, '0')}.jpg`
            );

            const images2 = [];
            const seq2 = { frame: 0 };

            for (let i = 0; i < frameCount2; i++) {
                const img = new Image();
                img.src = currentFrame2(i);
                images2.push(img);
            }

            images2[0].onload = render2;

            function render2() {
                canvas2.width = window.innerWidth;
                canvas2.height = window.innerHeight;
                
                const img = images2[seq2.frame];
                if (!img || !img.complete) return;

                const imgW = img.width || 1158;
                const imgH = img.height || 770;
                
                const ratio = Math.max(canvas2.width / imgW, canvas2.height / imgH);
                const newWidth = imgW * ratio;
                const newHeight = imgH * ratio;
                
                const offsetX = (canvas2.width - newWidth) / 2;
                const offsetY = (canvas2.height - newHeight) / 2;

                context2.clearRect(0, 0, canvas2.width, canvas2.height);
                context2.drawImage(img, offsetX, offsetY, newWidth, newHeight);
            }

            window.addEventListener("resize", render2);

            const section2 = document.getElementById('scrollSequence2');
            const textOverlay2 = document.getElementById('textOverlay2');
            const mainTitle2 = document.getElementById('mainTitle2');
            const subTitle2 = document.getElementById('subTitle2');

            const texts2 = [
                { start: 0.15, end: 0.40, title: "Uncompromised Quality", sub: "Built with premium materials." },
                { start: 0.50, end: 0.85, title: "Total Control", sub: "Engineered to win." }
            ];

            window.addEventListener('scroll', () => {
                const rect = section2.getBoundingClientRect();
                const sectionTop = rect.top;
                
                let scrollFraction = 0;
                if (sectionTop < 0) {
                    const maxScroll = section2.scrollHeight - window.innerHeight;
                    scrollFraction = Math.max(0, Math.min(1, Math.abs(sectionTop) / maxScroll));
                }

                const frameIndex = Math.min(
                    frameCount2 - 1,
                    Math.floor(scrollFraction * frameCount2)
                );
                
                if (seq2.frame !== frameIndex) {
                    seq2.frame = frameIndex;
                    requestAnimationFrame(render2);
                }

                // Update text
                let activeText = null;
                for (let t of texts2) {
                    if (scrollFraction >= t.start && scrollFraction <= t.end) {
                        activeText = t;
                        break;
                    }
                }

                if (activeText) {
                    if (!textOverlay2.classList.contains('active') || mainTitle2.innerText !== activeText.title) {
                        mainTitle2.innerText = activeText.title;
                        subTitle2.innerText = activeText.sub;
                        textOverlay2.classList.add('active');
                    }
                } else {
                    textOverlay2.classList.remove('active');
                }
            });
        });
    </script>
    <!--========================= End Hero-Section 2 ==========================-->


    <!--========================= menu-icon-title ==========================-->

    <div class="menu-icon-title">
        <div class="container">

            <div class="menu-icon-title-con">

                <div class="menu-icon-show">
                    <div class="show-icon-menu">
                        <i class="bx bx-truck"></i>
                    </div>
                    <h4>
                        Free Shipping
                    </h4>
                    <p>
                        On orders over $99
                    </p>
                </div>

                <div class="menu-icon-show">
                    <div class="show-icon-menu">
                        <i class="bx bx-check-shield"></i>
                    </div>
                    <h4>
                        2 Year Warranty
                    </h4>
                    <p>
                        Full coverage included
                    </p>
                </div>

                <div class="menu-icon-show">
                    <div class="show-icon-menu">
                        <i class="bx bx-headphone-alt"></i>
                    </div>
                    <h4>
                        24/7 Support
                    </h4>
                    <p>
                        Expert help anytime
                    </p>
                </div>

                <div class="menu-icon-show">
                    <div class="show-icon-menu">
                        <i class="bx bx-refresh-cw"></i>
                    </div>
                    <h4>
                        Easy Returns
                    </h4>
                    <p>
                        30-day return policy
                    </p>
                </div>

            </div>

        </div>
    </div>



    <!--========================= Shop-Category ==========================-->


    <div class="Shop-Category">
        <div class="container">
            <div class="show-category-con">

                <div class="show-category-con-header">

                    <div class="show-category-right">
                        <h5>
                            Shop by Category
                        </h5>
                        <p>
                            Find the perfect gear for your setup
                        </p>
                    </div>
                    <a href="products.php">
                        <div class="show-category-left">
                            <p>
                                View All
                            </p>
                            <i class="bx bx-arrow-right-stroke"></i>
                        </div>
                    </a>

                </div>

                <div class="show-category-imgs">

                <?php while($record = mysqli_fetch_assoc($cat_query)) { ?>
                    <div class="show-category-menu-con img1">
                        <a href="products.php?cat_id=<?php echo $record['cat_id'] ?>">
                            <img src="<?php echo $record['cat_image_url']; ?>" alt="">
                            <div class="cat-overlay"></div>
                            <div class="show-cat-content">
                                <div class="show-category-title">
                                    <h4>
                                        <?php echo $record['cat_name']; ?>
                                    </h4>
                                </div>
                                <div class="show-menu-product">
                                    <p><?php echo $record['product_count']; ?> Products</p>
                                    <span>
                                        Browse
                                        <i class="bx bx-chevron-right"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php } ?>

                    <!-- product grid items -->

                </div>

            </div>
        </div>
    </div>



    <!--========================= Featured-Products ==========================-->

    <div class="Featured-Products">
        <div class="container">
            <div class="products-con">

                <div class="show-category-con-header">

                    <div class="show-category-right">
                        <h5>
                            Featured Products
                        </h5>
                        <p>
                            Hand-picked by our experts
                        </p>
                    </div>
                    <a href="products.php">
                        <div class="show-category-left">
                            <p>
                                View All
                            </p>
                        <i class="bx bx-arrow-right-stroke"></i>
                    </div>
                    </a>

                </div>

            </div>
        </div>
    </div>


    <!--========================= product-item ==========================-->

    <div class="product-item">
        <div class="container">
            <div class="product-item-con">
            <?php
            // ดึงข้อมูลจากตาราง products ตามภาพที่ 5
            $prod_query = mysqli_query($conn, "SELECT * FROM products LIMIT 8");
            while($row = mysqli_fetch_assoc($prod_query)) {

                // ✅ แก้จุดที่ 1: เช็คค่า p_tag ก่อนเพื่อป้องกัน Error "Undefined array key"
                $p_tag = $row['p_tag'] ?? null;
                $tag_class = ($p_tag === 'NEW') ? 'show-new-product-box' : 'discount-product-box';

                // ✅ แก้จุดที่ 2: เปลี่ยนจาก p_id เป็น id ให้ตรงกับฐานข้อมูล
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
                        <p>
                            <?php echo htmlspecialchars($row['p_brand'] ?? ''); ?>
                        </p>
                        <h4>
                            <?php echo htmlspecialchars($row['p_name'] ?? ''); ?>
                        </h4>
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
                                <?php echo number_format($row['p_price'] ?? 0, 2); ?>
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

                    <?php
                        $is_wished = false;
                        if(isset($_SESSION['wishlist']) && in_array($prod_id, $_SESSION['wishlist'])) {
                            $is_wished = true;
                        }
                    ?>
                    <div class="show-icon-love" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist(<?= $prod_id ?>, this)">
                        <div class="icon-love">
                            <i class="<?= $is_wished ? 'bxf' : 'bx' ?> bx-heart" style="<?= $is_wished ? 'color: #d98a91;' : '' ?>"></i>
                        </div>
                    </div>

                </div>

                <?php } ?>

            </div>
        </div>
    </div>



    <!--========================= footer-banner-wrapper ==========================-->

    <section class="footer-banner-wrapper">
        <div class="container">
            <div class="footer-wrapper-con">
                <div class="footer-banner">
                    <h2>Ready to Upgrade Your Setup?</h2>
                    <p>Join thousands of gamers and developers who trust TechGear for premium equipment.</p>
                    <button class="btn-white">Shop Now ➔</button>
                </div>
            </div>
        </div>
    </section>





<script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>
<?php
include 'page/footer.php';
?>
