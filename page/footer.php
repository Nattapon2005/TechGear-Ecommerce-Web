
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <!--========================= main-footer ==========================-->

    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="f-col brand-col">
                    <div class="logo">
                        <a href="index.php">
                            TechGear
                        </a>
                    </div>
                    <p>Premium gaming and tech equipment for developers, gamers, and enthusiasts.</p>
                </div>

                <div class="f-col">
                    <h4>Shop</h4>
                    <ul>
                        <li><a href="#">Keyboards</a></li>
                        <li><a href="#">Mice</a></li>
                        <li><a href="#">Headsets</a></li>
                        <li><a href="#">Monitors</a></li>
                        <li><a href="#">Controllers</a></li>
                        <li><a href="#">Accessories</a></li>
                    </ul>
                </div>

                <div class="f-col">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Shipping Info</a></li>
                    </ul>
                </div>

                <div class="f-col">
                    <h4>Stay Updated</h4>
                    <p class="sub-text">Subscribe for deals and new arrivals.</p>
                    <form action="#" class="sub-form">
                        <input type="email" placeholder="Your email">
                        <button type="submit">Subscribe</button>
                    </form>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 TechGear. All rights reserved.</p>
                <div class="f-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>


    <script src="src/app.js?v=<?= time() ?>"></script>
    <script src="src/index.js?v=<?= time() ?>"></script>
    <script src="src/navbar.js?v=<?= time() ?>"></script>
    <script src="src/account.js?v=<?= time() ?>"></script>
    <script src="src/products.js?v=<?= time() ?>"></script>
    <script src="src/animations.js?v=<?= time() ?>"></script>
</body>
</html>
