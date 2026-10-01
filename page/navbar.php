<?php

require_once __DIR__ . '/functions.php';

// จัดการ Session ให้พร้อมใช้ทุกหน้า
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ป้องกันตะกร้าพังถ้ายังไม่มีของ
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>


้<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dist/css/style.css?v=<?= time() ?>">

    <link rel="icon" href="../Favicon/arrow-with-bright-neon-colors.png">
    <!-- Font Awesome Icon Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Basic Icons -->
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <!-- Filled Icons -->
    <link href="https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css" rel="stylesheet">
    <!-- Brand Icons -->
    <link href="https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css" rel="stylesheet">
    <!-- Boxicons 2.1.4 Fallback/Compat -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <title>TechGear</title>
</head>
<body>


    <!--========================= nav ==========================-->
    <nav class="navbar">
        <div class="container">

            <div class="nav-con">

                <div class="logo">
                    <a href="index.php">
                        TechGear
                    </a>
                </div>

                <div class="nav-bar-head">
                    <ul>
                        <li><a href="products.php">
                                All Products
                            </a></li>
                        <li><a href="categories.php">
                                Categories
                            </a></li>
                        <li><a href="about.php">
                                About
                            </a></li>
                        <li><a href="contact.php">
                                Contact
                            </a></li>
                    </ul>
                </div>


                <div class="search">
                    <form action="products.php" method="GET" class="search-con" style="margin: 0;">
                        <i class="bx bx-search-big search-icon"></i>
                        <input type="text" name="search" class="box-search" placeholder="Search product...">
                    </form>
                </div>


                <div class="menu-login">
                    <div class="menu-login-icon-one" onclick="window.location.href='wishlist.php'" title="Wishlist" style="position:relative;">
                        <i class="bx bx-heart "></i>
                        <?php if (isset($_SESSION['wishlist']) && count($_SESSION['wishlist']) > 0): ?>
                            <div class="wishlist-badge heartbeat"></div>
                        <?php endif; ?>
                    </div>
                    <div class="menu-login-icon-tow" onclick="toggleCart(true)">
                        <i  class="bx bx-cart-alt "></i>

                        <!--========================= count-cart ==========================-->

                        <div class="count-cart">
                            <p id="cart-count">
                                <?php
                                    if (isset($_SESSION['cart'])) {
                                        echo count($_SESSION['cart']);
                                    } else {
                                        echo "0";
                                    }
                                ?>
                            </p>
                        </div>

                    </div>

                    <?php if(isset($_SESSION['user_id'])) { ?>
                        <div class="profile-container" onclick="toggleProfile()">
                            <i class='bx bx-user-circle' style="font-size: 24px;"></i>
                            <span style="font-size:14px;">
                                <?php echo htmlspecialchars(mb_substr($_SESSION['username'] ?? 'User', 0, 5)) ?>
                            </span>

                            <div id="pDropdown" class="profile-dropdown">
                                <div class="box-profile-drop">
                                    <a href="account.php"><i class='bx bx-cog'></i> Account</a>
                                    <a href="orders.php"><i class='bx bx-package'></i> My Orders</a>
                                    <a class="box-profile-line" href="logout.php"><i class="bx bx-arrow-out-right-square-half " ></i> Logout</a>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <a href="login.php" class="btn-sign">Sign In</a>
                    <?php } ?>

                </div>


            </div>

        </div>
    </nav>



    <!--========================= cart-overlay ==========================-->

    <div class="cart-overlay" id="cOverlay" onclick="toggleCart(false)"></div>

    <div class="cart-sidebar" id="cSidebar">
        <div class="cart-header">
            <div class="cart-header-con">
                <i class="bx bx-shopping-bag-alt"></i>
                <h3>Your Cart </h3>
                <span class="cart-count-header">(<span id="cart-qty">0</span> items)</span>
            </div>
            <span class="close-cart" onclick="toggleCart(false)" id="closeCart">&times;</span>
        </div>

        <div id="cartItems" class="cart-items">

            <!--=========== cartItems code from navbar.js============= -->

        </div>

        <div class="cart-footer" >
            <div class="total-row">
                <span class="total-con">Subtotal</span>
                <span id="cart-total" >$0.00</span>
            </div>
            <button onclick="window.location.href='checkout.php'" class="btn-checkout">
                Checkout
            </button>
        </div>
    </div>


    <script>
        // ดึงข้อมูลจาก PHP Session มาใส่ตัวแปร JavaScript (ทำในไฟล์ .php ทำได้สบายมาก)
        let cartData = <?php echo isset($_SESSION['cart']) ? json_encode(array_values($_SESSION['cart'])) : '[]'; ?>;
    </script>
    <script src="src/navbar.js"></script>
</body>
</html>