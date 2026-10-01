<?php
include 'config/db.php';
include 'page/navbar.php';


// 1. ถ้ายังไม่ล็อกอิน ให้เด้งกลับไปหน้า login
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

// 2. ดึงข้อมูลออเดอร์ของผู้ใช้จาก Database (เรียงจากล่าสุดขึ้นก่อน)
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$has_orders = ($result->num_rows > 0);


?>



<style>
.order-body-bg {
    background: radial-gradient(circle at top right, #111827 0%, #030712 100%);
    min-height: 100vh;
    padding: 100px 20px 40px;
}
.orders-wrapper {
    max-width: 1000px;
    margin: 0 auto;
}
.page-title {
    color: #fff;
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 30px;
    text-shadow: 0 0 15px rgba(139,92,246,0.6);
    display: flex;
    align-items: center;
    gap: 10px;
}
.page-title::before {
    content: '\eb71'; /* bx-package */
    font-family: 'boxicons';
    color: #8b5cf6;
    font-size: 32px;
}

/* Glassmorphism Card */
.order-card {
    background: rgba(17, 24, 39, 0.6);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(139, 92, 246, 0.3);
    border-radius: 16px;
    margin-bottom: 20px;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 0 20px rgba(139,92,246,0.05);
}
.order-card:hover {
    transform: translateY(-5px);
    border-color: #a78bfa;
    box-shadow: 0 10px 30px rgba(139,92,246,0.2), inset 0 0 20px rgba(139,92,246,0.1);
}

.order-line-header {
    background: rgba(15, 23, 42, 0.8);
    padding: 15px 25px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}
.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.order-id {
    font-size: 18px;
    font-weight: 800;
    color: #00ffcc;
    letter-spacing: 1px;
}
.order-date {
    color: #cbd5e1;
    font-size: 13px;
    margin-left: 15px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.status-pending { background: rgba(245,158,11,0.2); color: #f59e0b; border: 1px solid rgba(245,158,11,0.5); }
.status-completed { background: rgba(16,185,129,0.2); color: #34d399; border: 1px solid rgba(16,185,129,0.5); }
.status-shipped { background: rgba(139,92,246,0.2); color: #a78bfa; border: 1px solid rgba(139,92,246,0.5); }
.status-cancelled { background: rgba(244,63,94,0.2); color: #fb7185; border: 1px solid rgba(244,63,94,0.5); }

.order-line-body {
    padding: 25px;
}
.order-body {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.order-total-label {
    font-size: 13px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
}
.order-total-value {
    font-size: 24px;
    font-weight: 900;
    color: #fff;
    text-shadow: 0 0 10px rgba(255,255,255,0.3);
}

.order-show-date {
    display: flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #220c49ff, #161e63ff);
    padding: 10px 20px;
    border-radius: 12px;
    cursor: pointer;
    transition: 0.3s;
}
.order-show-date:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 20px rgba(124,58,237,0.4);
}
.order-show-date i { color: #fff; font-size: 18px; }
.btn-view-detail {
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
}

/* Empty State */
.empty-orders {
    background: rgba(17, 24, 39, 0.6);
    backdrop-filter: blur(20px);
    border: 1px dashed rgba(148, 163, 184, 0.3);
    border-radius: 20px;
    padding: 60px 20px;
    text-align: center;
    color: #fff;
}
.empty-icon-circle {
    width: 80px;
    height: 80px;
    background: rgba(139, 92, 246, 0.1);
    border: 2px solid #8b5cf6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 40px;
    color: #a78bfa;
    box-shadow: 0 0 30px rgba(139,92,246,0.3);
}
.empty-orders h2 { font-size: 24px; margin-bottom: 10px; }
.empty-orders p { color: #cbd5e1; margin-bottom: 30px; font-size: 15px; }
.btn-shopping {
    background: #00ffcc;
    color: #030712;
    padding: 12px 30px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 800;
    text-transform: uppercase;
    transition: 0.3s;
    display: inline-block;
}
.btn-shopping:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,255,204,0.4);
}
</style>

<div class="order-body-bg">
    <div class="orders-wrapper">
        <h1 class="page-title">My Orders</h1>

        <?php if ($has_orders) { ?>
            <?php while($order = $result->fetch_assoc()) {
                $status_class = strtolower($order['order_status']);
            ?>
                <div class="order-card">
                    <div class="order-line-header">
                        <div class="order-header">
                            <div class="order-con">
                                <span class="order-id">#ORD-<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?></span>
                                <span class="order-date">
                                    <i class='bx bx-calendar'></i> <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                                </span>
                            </div>
                            <span class="status-badge status-<?= $status_class ?>">
                                <i class="bx bx-loader-circle bx-spin" style="<?= $status_class == 'completed' || $status_class == 'cancelled' ? 'display:none;' : '' ?>"></i>
                                <?= htmlspecialchars($order['order_status']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="order-line-body">
                        <div class="order-body">
                            <div class="order-con">
                                <div class="order-total-label">Total Amount</div>
                                <div class="order-total-value">$<?= number_format($order['total_amount'], 2) ?></div>
                            </div>
                            <a href="order-detail.php?id=<?= $order['order_id'] ?>" style="text-decoration:none;">
                                <div class="order-show-date">
                                    <i class="bx bx-receipt"></i>
                                    <span class="btn-view-detail">View Details</span>
                                    <i class="bx bx-chevron-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>

        <?php  } else { ?>
            <div class="empty-orders">
                <div class="empty-icon-circle">
                    <i class='bx bx-package'></i>
                </div>
                <h2>No orders yet</h2>
                <p>When you make a purchase, your order history will appear here.</p>
                <a href="products.php" class="btn-shopping">Start Shopping</a>
            </div>
        <?php } ?>

    </div>
</div>


<?php
include 'page/footer.php';
?>