<?php
session_start();
include 'config/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check cart
if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

// Fetch user data for defaults
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// Fetch payment methods
$payment_methods = [];
$q_pm = $conn->query("SELECT * FROM payment_methods");
if ($q_pm) {
    while($row = $q_pm->fetch_assoc()) {
        $payment_methods[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'PromptPay';
    
    // Calculate total amount
    $total_amount = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_amount += ($item['price'] * $item['qty']);
    }

    $order_status = 'Pending';

    // Insert order
    $sql_order = "INSERT INTO orders (user_id, total_amount, order_status, payment_method, fullname, phone, email, address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("idssssss", $user_id, $total_amount, $order_status, $payment_method, $fullname, $phone, $email, $address);
    
    if ($stmt_order->execute()) {
        $order_id = $stmt_order->insert_id;
        
        // Insert order items
        $sql_item = "INSERT INTO order_items (order_id, p_id, price, quantity) VALUES (?, ?, ?, ?)";
        $stmt_item = $conn->prepare($sql_item);
        
        foreach ($_SESSION['cart'] as $key => $item) {
            $p_id = (int)$item['id'];
            $price = $item['price'];
            $qty = $item['qty'];
            
            $check_product = $conn->query("SELECT id FROM products WHERE id = $p_id");
            if ($check_product && $check_product->num_rows > 0) {
                $stmt_item->bind_param("iidi", $order_id, $p_id, $price, $qty);
                $stmt_item->execute();
            }
        }
        
        // Clear cart
        unset($_SESSION['cart']);
        
        // Redirect to order detail
        header("Location: order-detail.php?id=$order_id");
        exit();
    } else {
        $error_msg = "Error creating order. Please try again.";
    }
}

include 'page/navbar.php';
?>

<style>
/* High-Tech UI Styles */
.body-account {
    background: radial-gradient(circle at top right, #131518 0%, #0b0c0e 100%);
    min-height: 100vh;
    padding-top: 100px;
}
.content-box {
    background: rgba(17, 24, 39, 0.7);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(201, 172, 130, 0.3);
    box-shadow: 0 0 40px rgba(201, 172, 130, 0.1), inset 0 0 20px rgba(201, 172, 130, 0.05);
    border-radius: 20px;
    padding: 40px;
    position: relative;
    overflow: hidden;
}
.content-box::before {
    content: '';
    position: absolute;
    top: -50%; left: -50%; width: 200%; height: 200%;
    background: conic-gradient(from 0deg, transparent 0%, rgba(201, 172, 130,0.1) 25%, transparent 50%);
    animation: rotate 10s linear infinite;
    z-index: 0;
    pointer-events: none;
}
@keyframes rotate { 100% { transform: rotate(360deg); } }

.content-box-con { position: relative; z-index: 1; }

.box-title {
    color: #f3f1ec;
    font-size: 24px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 25px;
    text-shadow: 0 0 10px rgba(201, 172, 130, 0.5);
    display: flex;
    align-items: center;
    gap: 10px;
}
.box-title i { color: #c9ac82; }

.form-group { position: relative; margin-bottom: 25px; }
.form-input {
    width: 100%;
    padding: 16px 20px;
    background: rgba(19, 21, 24, 0.6);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 12px;
    color: #c9ac82;
    font-size: 15px;
    font-family: monospace;
    outline: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.form-input:focus {
    border-color: #c9ac82;
    box-shadow: 0 0 15px rgba(201, 172, 130, 0.3), inset 0 0 10px rgba(201, 172, 130, 0.1);
    background: rgba(19, 21, 24, 0.9);
}
.form-group label {
    display: block;
    margin-bottom: 10px;
    color: #c9ac82;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    transition: 0.3s;
}

/* Payment Method Cards */
.payment-methods-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 15px;
}
.payment-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 15px;
    cursor: pointer;
    background: rgba(19, 21, 24, 0.8);
    padding: 20px 15px;
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, 0.2);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}
.payment-label::after {
    content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(135deg, transparent, rgba(255,255,255,0.05), transparent);
    transform: translateX(-100%); transition: 0.5s;
}
.payment-label:hover::after { transform: translateX(100%); }
.payment-label:hover {
    transform: translateY(-5px);
    border-color: #c9ac82;
    box-shadow: 0 10px 20px rgba(201, 172, 130, 0.2);
}
.payment-label:has(input:checked) {
    border-color: #c9ac82;
    background: rgba(201, 172, 130, 0.05);
    box-shadow: 0 0 20px rgba(201, 172, 130, 0.2), inset 0 0 15px rgba(201, 172, 130, 0.1);
}
.payment-label input[type="radio"] { display: none; }

/* Bank Logos */
.bank-logo {
    width: 60px;
    height: 60px;
    object-fit: contain;
    filter: grayscale(1) brightness(0.8);
    transition: 0.4s;
}
.payment-label:has(input:checked) .bank-logo,
.payment-label:hover .bank-logo {
    filter: grayscale(0) brightness(1);
    transform: scale(1.1);
}
.payment-name {
    font-weight: 700;
    font-size: 14px;
    color: #8d9098;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: 0.3s;
}
.payment-label:has(input:checked) .payment-name { color: #c9ac82; }

/* Button */
.btn-place-order {
    background: linear-gradient(135deg, #b89563 0%, #8a6f4d 100%);
    color: #17130d;
    border: none;
    padding: 18px;
    border-radius: 14px;
    font-size: 18px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 2px;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    margin-top: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    position: relative;
    overflow: hidden;
}
.btn-place-order::before {
    content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: 0.5s;
}
.btn-place-order:hover::before { left: 100%; }
.btn-place-order:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 15px 30px rgba(184, 149, 99, 0.4);
}

/* Sidebar styling tweak */
.profile-sidebar {
    background: rgba(19, 21, 24, 0.8) !important;
    border: 1px solid rgba(201, 172, 130, 0.2) !important;
    backdrop-filter: blur(10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    padding: 30px !important;
    border-radius: 20px !important;
}
.summary-item {
    display: flex;
    gap: 15px;
    align-items: center;
    background: rgba(255, 255, 255, 0.03);
    padding: 10px;
    border-radius: 12px;
    transition: 0.3s;
}
.summary-item:hover {
    background: rgba(201, 172, 130, 0.1);
}
</style>

<div class="body-account">
    <div class="account-wrapper">
        <h1 class="page-title">Checkout</h1>

        <div class="account-grid" style="grid-template-columns: 2fr 1fr;">
            
            <main class="content-box">
                <?php if(isset($error_msg)): ?>
                <div class="alert-error" style="color:red; margin-bottom: 15px;"><i class='bx bxs-error-circle'></i> <?= $error_msg ?></div>
                <?php endif; ?>
                
                <div class="content-box-con">
                    <form method="POST" action="checkout.php">
                        <div class="box-header">
                            <h2 class="box-title"><i class='bx bx-current-location'></i> Shipping Info</h2>
                        </div>

                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="fullname" class="form-input" value="<?= htmlspecialchars($user_data['username'] ?? '') ?>" required>
                        </div>

                        <div class="form-row" style="display:flex; gap:20px;">
                            <div class="form-group" style="flex:1;">
                                <label>Email Address</label>
                                <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" required>
                            </div>
                            <div class="form-group" style="flex:1;">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-input" value="<?= htmlspecialchars($user_data['phone'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Delivery Address</label>
                            <textarea name="address" class="form-input" style="height: 120px; resize: none;" required><?= htmlspecialchars($user_data['address'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="box-header" style="margin-top: 40px;">
                            <h2 class="box-title"><i class='bx bx-wallet-alt'></i> Payment Method</h2>
                        </div>
                        
                        <div class="payment-methods-grid">
                            <?php 
                            $first = true;
                            foreach($payment_methods as $pm): 
                            ?>
                            <label class="payment-label">
                                <input type="radio" name="payment_method" value="<?= htmlspecialchars($pm['name']) ?>" <?= $first ? 'checked' : '' ?>>
                                <?php if(!empty($pm['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($pm['image_url']) ?>" alt="<?= htmlspecialchars($pm['name']) ?>" class="bank-logo" style="background:#f3f1ec; padding:5px; border-radius:8px;">
                                <?php else: ?>
                                    <i class="<?= htmlspecialchars($pm['icon_class'] ?? 'bx bx-credit-card') ?>" style="font-size: 36px; color: #d9c7a8; margin-bottom: 5px;"></i>
                                <?php endif; ?>
                                <span class="payment-name"><?= htmlspecialchars($pm['name']) ?></span>
                            </label>
                            <?php 
                            $first = false;
                            endforeach; 
                            ?>
                        </div>

                        <button type="submit" class="btn-place-order">
                            <i class='bx bx-fingerprint' style="font-size:24px;"></i> Confirm & Place Order
                        </button>
                    </form>
                </div>
            </main>
            
            <aside class="profile-sidebar" style="height: fit-content; margin-top: 0;">
                <h3 class="sidebar-name" style="text-align: left; margin-bottom: 25px; border-bottom: 1px solid rgba(148, 163, 184, 0.2); padding-bottom: 15px; color: #f3f1ec;">Order Summary</h3>
                
                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 30px;">
                    <?php 
                    $total = 0;
                    foreach($_SESSION['cart'] as $item): 
                        $total += $item['price'] * $item['qty'];
                    ?>
                    <div class="summary-item">
                        <img src="<?= htmlspecialchars($item['image']) ?>" style="width:60px; height:60px; border-radius:10px; object-fit:contain; background: #f3f1ec; padding: 2px;">
                        <div style="flex:1;">
                            <div style="font-size:14px; font-weight:bold; color:#f3f1ec; margin-bottom:4px;"><?= htmlspecialchars($item['name']) ?></div>
                            <div style="font-size:13px; color:#8d9098;">$<?= number_format($item['price'], 2) ?> x <?= $item['qty'] ?></div>
                        </div>
                        <div style="font-weight:900; color:#c9ac82; font-size:15px;">$<?= number_format($item['price'] * $item['qty'], 2) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="border-top: 1px dashed rgba(148, 163, 184, 0.3); padding-top: 25px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:15px; color:#8d9098; font-size:15px;">
                        <span>Subtotal</span>
                        <span style="color: #f3f1ec;">$<?= number_format($total, 2) ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:20px; color:#8d9098; font-size:15px;">
                        <span>Shipping Fee</span>
                        <span style="color:#c9ac82; font-weight:bold;">Free</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-top:20px; padding-top: 20px; border-top: 1px solid rgba(148, 163, 184, 0.2); font-size: 20px; font-weight:900; color:#f3f1ec;">
                        <span>Total</span>
                        <span style="color:#c9ac82;">$<?= number_format($total, 2) ?></span>
                    </div>
                </div>
            </aside>
            
        </div>
    </div>
</div>

<?php
include 'page/footer.php';
?>
