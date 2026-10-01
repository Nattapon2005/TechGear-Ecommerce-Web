<?php
session_start();
include 'config/db.php';

// เช็คการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (!$order_id) {
    header("Location: orders.php");
    exit();
}

// 1. ดึงข้อมูลบิล
$sql_order = "SELECT o.*, u.username, u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.user_id
                WHERE o.order_id = '$order_id' AND o.user_id = '$user_id'";
$res_order = mysqli_query($conn, $sql_order);
$order = mysqli_fetch_assoc($res_order);

if (!$order) {
    die("<div style='text-align:center; padding:100px; color:white; font-family:sans-serif;'><h2><i class='bx bx-error' style='color:#f43f5e;font-size:40px;'></i><br>ไม่พบคำสั่งซื้อนี้ หรือคุณไม่มีสิทธิ์เข้าถึง</h2><a href='orders.php' style='color:#8b5cf6;'>กลับหน้าคำสั่งซื้อของฉัน</a></div>");
}

// 2. ดึง Tracking data
$tracking = [];
$qt = mysqli_query($conn, "SELECT * FROM order_tracking WHERE order_id = '$order_id' ORDER BY created_at ASC");
if ($qt) while($t = mysqli_fetch_assoc($qt)) $tracking[] = $t;

// กำหนด tracking steps ตาม status
$status_steps = [
    'Pending' => 0,
    'Processing' => 1,
    'Shipped' => 2,
    'Completed' => 3,
];
$current_step = $status_steps[$order['order_status']] ?? 0;
$is_cancelled = ($order['order_status'] === 'Cancelled');

include 'page/navbar.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
/* ===== Tracking Specific Styles ===== */
.tracking-section {
    background: linear-gradient(145deg, #0f1117, #191c28);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 24px;
    animation: fadeSlideUp .6s cubic-bezier(.4,0,.2,1) both;
    animation-delay: .3s;
}
@keyframes fadeSlideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Progress Bar (Shopee style) */
.tracking-progress {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    position: relative;
    padding: 0 10px;
    margin: 30px 0 40px;
}
.tracking-progress::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 40px;
    right: 40px;
    height: 4px;
    background: #191c28;
    border-radius: 2px;
    z-index: 0;
}
.tracking-progress::after {
    content: '';
    position: absolute;
    top: 20px;
    left: 40px;
    height: 4px;
    background: linear-gradient(90deg, #7c5cfc, #a78bfa, #14b8a6);
    border-radius: 2px;
    z-index: 1;
    transition: width 1s cubic-bezier(.4,0,.2,1);
    width: <?= $is_cancelled ? '0' : (($current_step / 3) * (100 - 10)) ?>%;
    animation: progressGrow 1.5s cubic-bezier(.4,0,.2,1) both;
    animation-delay: .5s;
}
@keyframes progressGrow {
    from { width: 0; }
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 2;
    flex: 1;
    max-width: 120px;
}
.step-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-bottom: 10px;
    transition: all .5s cubic-bezier(.4,0,.2,1);
    border: 3px solid #191c28;
}
.step-icon.active {
    background: linear-gradient(135deg, #7c5cfc, #a78bfa);
    color: #fff;
    border-color: #7c5cfc;
    box-shadow: 0 0 20px rgba(124,92,252,.4);
    animation: pulseStep 2s infinite;
}
.step-icon.done {
    background: #10b981;
    color: #fff;
    border-color: #10b981;
    box-shadow: 0 0 12px rgba(16,185,129,.3);
}
.step-icon.waiting {
    background: #191c28;
    color: #64748b;
    border-color: #21253a;
}
.step-icon.cancelled {
    background: #f43f5e;
    color: #fff;
    border-color: #f43f5e;
    box-shadow: 0 0 12px rgba(244,63,94,.3);
}
@keyframes pulseStep {
    0%, 100% { box-shadow: 0 0 15px rgba(124,92,252,.3); }
    50% { box-shadow: 0 0 30px rgba(124,92,252,.5), 0 0 50px rgba(124,92,252,.2); }
}
.step-label {
    font-size: 12px;
    font-weight: 600;
    color: #94a3b8;
    text-align: center;
}
.step-label.active { color: #a78bfa; }
.step-label.done { color: #34d399; }
.step-time {
    font-size: 10px;
    color: #94a3b8;
    margin-top: 4px;
    text-align: center;
}

/* Timeline */
.tracking-timeline {
    position: relative;
    padding-left: 32px;
    margin-top: 24px;
}
.tracking-timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(180deg, #7c5cfc, #191c28);
}
.tl-item {
    position: relative;
    margin-bottom: 20px;
    padding: 14px 18px;
    background: rgba(15,17,23,.8);
    border: 1px solid rgba(255,255,255,.04);
    border-radius: 12px;
    transition: all .3s;
    animation: fadeSlideUp .5s both;
}
.tl-item:hover {
    border-color: rgba(124,92,252,.2);
    background: rgba(25,28,40,.9);
    transform: translateX(4px);
}
.tl-dot {
    position: absolute;
    left: -28px;
    top: 18px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #7c5cfc;
    border: 3px solid #0f1117;
    z-index: 1;
}
.tl-dot.green { background: #10b981; }
.tl-dot.amber { background: #f59e0b; }
.tl-dot.red { background: #f43f5e; }
.tl-dot.first { box-shadow: 0 0 12px rgba(124,92,252,.5); animation: pulseStep 2s infinite; }
.tl-status { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
.tl-loc { font-size: 12px; color: #94a3b8; }
.tl-loc i { font-size: 12px; vertical-align: -1px; }
.tl-desc { font-size: 12px; color: #cbd5e1; margin-top: 6px; }
.tl-time { font-size: 11px; color: #94a3b8; margin-top: 6px; }

/* Map */
#trackingMap {
    width: 100%;
    height: 300px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.06);
    margin-top: 16px;
    z-index: 1;
}
.map-placeholder {
    width: 100%;
    height: 300px;
    border-radius: 14px;
    background: linear-gradient(145deg, #0f1117, #191c28);
    border: 1px solid rgba(255,255,255,.06);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    margin-top: 16px;
}
.map-placeholder i { font-size: 40px; margin-bottom: 8px; color: #21253a; }

/* ===== Redesigned UI Elements ===== */
.body-order-detail-wrapper {
    background: radial-gradient(circle at top right, #111827 0%, #030712 100%);
    min-height: 100vh;
    padding: 100px 20px 40px;
}
.order-detail-wrapper {
    max-width: 1100px;
    margin: 0 auto;
}
.breadcrumb {
    color: #94a3b8;
    font-size: 14px;
    margin-bottom: 20px;
}
.breadcrumb a {
    color: #8b5cf6;
    text-decoration: none;
    transition: 0.3s;
}
.breadcrumb a:hover { color: #00ffcc; text-shadow: 0 0 10px rgba(0,255,204,0.5); }

.header-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(17, 24, 39, 0.7);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(139, 92, 246, 0.3);
    border-radius: 16px;
    padding: 20px 30px;
    margin-bottom: 24px;
    box-shadow: 0 0 30px rgba(139,92,246,0.1);
}
.page-title {
    color: #fff;
    font-size: 24px;
    font-weight: 800;
    margin: 0 0 5px 0;
    text-shadow: 0 0 10px rgba(139,92,246,0.5);
}
.order-date { color: #cbd5e1; font-size: 14px; }
.btn-print {
    background: linear-gradient(135deg, #7c3aed, #3b82f6);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}
.btn-print:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(124,58,237,0.3); }

/* Info Grid (Cards) */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}
.info-card {
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 16px;
    padding: 24px;
    transition: 0.3s;
}
.info-card:hover { border-color: rgba(139,92,246,0.5); box-shadow: 0 10px 30px rgba(139,92,246,0.1); }
.info-card h3 {
    margin: 0 0 15px 0;
    color: #fff;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px dashed rgba(148,163,184,0.3);
    padding-bottom: 15px;
}
.info-card h3 i { color: #a78bfa; font-size: 20px; }
.info-data { color: #cbd5e1; font-size: 14px; line-height: 1.6; }

/* Items Box */
.items-card {
    background: rgba(17, 24, 39, 0.7);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(139, 92, 246, 0.3);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 0 30px rgba(139,92,246,0.1);
}
.items-table {
    width: 100%;
    border-collapse: collapse;
}
.items-table th {
    text-align: left;
    color: #94a3b8;
    font-size: 13px;
    text-transform: uppercase;
    padding: 10px 15px;
    border-bottom: 1px solid rgba(148,163,184,0.2);
}
.items-table td {
    padding: 15px;
    border-bottom: 1px solid rgba(148,163,184,0.1);
}
.prod-cell { display: flex; align-items: center; gap: 15px; }
.prod-img {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    object-fit: contain;
    background: #fff;
    padding: 2px;
}
.prod-name { color: #f8fafc; font-weight: 600; font-size: 14px; }

.summary-box {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px dashed rgba(148,163,184,0.3);
    width: 100%;
    max-width: 400px;
    margin-left: auto;
}
.sum-row {
    display: flex;
    justify-content: space-between;
    color: #94a3b8;
    margin-bottom: 15px;
    font-size: 14px;
}
.sum-total {
    display: flex;
    justify-content: space-between;
    color: #fff;
    font-weight: 800;
    font-size: 20px;
    padding-top: 15px;
    border-top: 1px solid rgba(148,163,184,0.2);
}

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}
.status-pending { background: rgba(245,158,11,0.2); color: #f59e0b; border: 1px solid rgba(245,158,11,0.5); }
.status-processing { background: rgba(56,189,248,0.2); color: #38bdf8; border: 1px solid rgba(56,189,248,0.5); }
.status-shipped { background: rgba(139,92,246,0.2); color: #a78bfa; border: 1px solid rgba(139,92,246,0.5); }
.status-completed { background: rgba(16,185,129,0.2); color: #34d399; border: 1px solid rgba(16,185,129,0.5); }
.status-cancelled { background: rgba(244,63,94,0.2); color: #fb7185; border: 1px solid rgba(244,63,94,0.5); }

/* Print Styles */
@media print {
    body { background: #fff !important; color: #000 !important; }
    header, footer, .breadcrumb, .btn-print, .cancel-form, .tracking-section, .slip-upload-section, .leaflet-control-container, #trackingMap { display: none !important; }
    .body-order-detail-wrapper { padding: 0 !important; background: transparent !important; }
    .order-detail-wrapper { width: 100% !important; max-width: 100% !important; box-shadow: none !important; border: none !important; background: transparent !important; margin: 0 !important; padding: 0 !important; }
    .header-box { display: flex !important; justify-content: space-between !important; border-bottom: 2px solid #000 !important; padding-bottom: 20px !important; margin-bottom: 20px !important; }
    .header-box-title h1 { color: #000 !important; font-size: 24px !important; }
    .order-date { color: #333 !important; }
    .detail-grid { grid-template-columns: 1fr 1fr !important; gap: 20px !important; }
    .info-card { background: transparent !important; border: 1px solid #ccc !important; box-shadow: none !important; break-inside: avoid; }
    .info-card h3 { color: #000 !important; border-bottom: 1px solid #ccc !important; padding-bottom: 5px !important; }
    .info-card p, .info-card span { color: #000 !important; }
    .order-items { margin-top: 20px !important; }
    .item-card { background: transparent !important; border: 1px solid #ccc !important; display: flex !important; }
    .item-info h4, .item-price, .item-qty { color: #000 !important; }
    .item-img { filter: none !important; }
    .sum-row, .sum-total { color: #000 !important; }
    .sum-total { border-top: 2px solid #000 !important; margin-top: 10px !important; padding-top: 10px !important; }
}
</style>

<div class="body-order-detail-wrapper">
    <div class="order-detail-wrapper">
        <div class="breadcrumb">
            <a href="orders.php">My Orders</a> >
            <span>Order Details</span>
        </div>

        <div class="header-box">
            <div class="header-box-title">
                <h1 class="page-title">Order #<?= htmlspecialchars($order['order_id']) ?></h1>
                <div class="order-date"><i class='bx bx-calendar'></i> Placed on <?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
            </div>
            <button class="btn-print" onclick="window.print()"><i class='bx bx-printer'></i> Print Receipt</button>
        </div>

        <!-- ===== TRACKING SECTION (Shopee-style) ===== -->
        <div class="tracking-section">
            <h3 style="font-size:16px; font-weight:700; margin-bottom:6px;">
                <i class='bx bxs-truck' style="color:#a78bfa;"></i> การติดตามพัสดุ
            </h3>
            <p style="font-size:12px; color:#64748b; margin-bottom:20px;">ติดตามสถานะการจัดส่งสินค้าแบบ Real-time</p>

            <?php if($is_cancelled): ?>
                <div style="text-align:center; padding:20px; background:rgba(244,63,94,.08); border-radius:12px; border:1px solid rgba(244,63,94,.2);">
                    <i class='bx bx-x-circle' style="font-size:36px; color:#f43f5e;"></i>
                    <div style="font-size:14px; font-weight:700; color:#fb7185; margin-top:8px;">คำสั่งซื้อถูกยกเลิก</div>
                </div>
            <?php else: ?>
                <!-- Progress Steps -->
                <div class="tracking-progress">
                    <?php
                    $steps_info = [
                        ['icon' => 'bx-receipt', 'label' => 'สั่งซื้อแล้ว'],
                        ['icon' => 'bx-cog', 'label' => 'กำลังจัดเตรียม'],
                        ['icon' => 'bx-truck', 'label' => 'กำลังจัดส่ง'],
                        ['icon' => 'bx-check-circle', 'label' => 'จัดส่งสำเร็จ'],
                    ];
                    foreach($steps_info as $si => $s):
                        $state = '';
                        if($si < $current_step) $state = 'done';
                        elseif($si === $current_step) $state = 'active';
                        else $state = 'waiting';
                    ?>
                    <div class="step">
                        <div class="step-icon <?= $state ?>"><i class='bx <?= $s['icon'] ?>'></i></div>
                        <div class="step-label <?= $state ?>"><?= $s['label'] ?></div>
                        <?php if($si <= $current_step): ?>
                            <div class="step-time">
                                <?php
                                // Find matching tracking entry time
                                foreach($tracking as $tk) {
                                    $map = ['Pending'=>0,'Processing'=>1,'Shipped'=>2,'Completed'=>3];
                                    if(isset($map[$tk['status']]) && $map[$tk['status']] === $si) {
                                        echo date('d/m H:i', strtotime($tk['created_at']));
                                        break;
                                    }
                                }
                                if($si === 0) echo date('d/m H:i', strtotime($order['created_at']));
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Map -->
            <?php if(!$is_cancelled && $current_step >= 2): ?>
                <h4 style="font-size:13px; font-weight:600; color:#a78bfa; margin-bottom:8px;">
                    <i class='bx bx-map'></i> ตำแหน่งพัสดุ Real-time
                </h4>
                <div id="trackingMap"></div>
            <?php elseif(!$is_cancelled): ?>
                <div class="map-placeholder">
                    <i class='bx bx-map'></i>
                    <div style="font-size:13px;">แผนที่จะแสดงเมื่อสินค้าถูกจัดส่ง</div>
                </div>
            <?php endif; ?>

            <!-- Tracking Timeline -->
            <?php if(!empty($tracking)): ?>
                <h4 style="font-size:13px; font-weight:600; color:#14b8a6; margin:24px 0 12px;">
                    <i class='bx bx-time-five bx-clock'></i> ประวัติการอัปเดต
                </h4>
                <div class="tracking-timeline">
                    <?php foreach(array_reverse($tracking) as $ti => $t): 
                        $dot_class = '';
                        if(stripos($t['status'],'completed')!==false || stripos($t['status'],'delivered')!==false || stripos($t['status'],'confirmed')!==false) $dot_class='green';
                        elseif(stripos($t['status'],'cancelled')!==false || stripos($t['status'],'rejected')!==false) $dot_class='red';
                        elseif(stripos($t['status'],'pending')!==false) $dot_class='amber';
                    ?>
                    <div class="tl-item" style="animation-delay:<?= $ti * 0.1 ?>s;">
                        <div class="tl-dot <?= $dot_class ?> <?= $ti===0?'first':'' ?>"></div>
                        <div class="tl-status"><?= htmlspecialchars($t['status']) ?></div>
                        <div class="tl-loc"><i class='bx bx-map-pin'></i> <?= htmlspecialchars($t['location']) ?></div>
                        <?php if($t['description']): ?>
                            <div class="tl-desc"><?= htmlspecialchars($t['description']) ?></div>
                        <?php endif; ?>
                        <div class="tl-time"><i class='bx bx-time bx-clock'></i> <?= date('d M Y, H:i:s', strtotime($t['created_at'])) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h3>
                    <i class='bx bxs-truck'></i>
                    Shipping Address
                </h3>
                <div class="info-data">
                    <strong style="color: #00ffcc; font-size: 16px; display: block; margin-bottom: 8px;">
                        <?= htmlspecialchars($order['fullname'] ?? 'N/A')  ?>
                    </strong>
                    <div style="margin-bottom: 15px; color: #cbd5e1;">
                        <i class='bx bx-map' style="color:#a78bfa;"></i> <?= nl2br(htmlspecialchars($order['address'] ?? 'N/A' )) ?>
                    </div>
                    <div style="display: flex; gap: 20px; color: #94a3b8;">
                        <span><i class='bx bx-phone' style="color:#a78bfa;"></i> <?= htmlspecialchars($order['phone'] ?? 'N/A') ?></span>
                        <span><i class='bx bx-envelope' style="color:#a78bfa;"></i> <?= htmlspecialchars($order['email'] ?? 'N/A') ?></span>
                    </div>
                </div>
            </div>

            <div class="info-card">
                <h3><i class='bx bx-credit-card-front'></i> Payment Summary</h3>
                <div class="info-data">
                    <div style="margin-bottom: 10px;">
                        <span style="color:#64748b;">Method:</span> <strong style="color:white;"><?= htmlspecialchars($order['payment_method']) ?></strong>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <span style="color:#64748b;">Status:</span>
                        <?php $status_class = strtolower($order['order_status']); ?>
                        <span class="status-badge status-<?= $status_class ?>"><?= htmlspecialchars($order['order_status']) ?></span>
                    </div>

                    <?php
                    $method = strtolower($order['payment_method']);
                    // โชว์ฟอร์มแนบสลิปเฉพาะคนที่เลือกโอนเงิน และยังไม่อัปโหลดสลิป
                    if (str_contains($method, 'bank') || str_contains($method, 'promptpay') || $method == '') {
                    ?>
                        <?php if(empty($order['slip_image'])) { ?>
                            <div style="margin-top: 15px; border-top: 1px dashed #232b3e; padding-top: 15px;">
                                <h4 style="color: #a78bfa; margin-top: 0; margin-bottom: 15px; text-align: center; font-size: 14px;">สแกน QR Code ชำระเงิน</h4>
                                <div style="text-align: center; margin-bottom: 15px;">
                                    <img src="https://promptpay.io/0948124542/<?= $order['total_amount'] ?>.png" style="width: 130px; border-radius: 10px; background: white; padding: 10px;">
                                </div>

                                <form action="upload_slip.php" method="POST" enctype="multipart/form-data" style="text-align: center;">
                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['order_id']) ?>">
                                    <label style="display: block; background: rgba(139, 92, 246, 0.1); border: 1px dashed #8b5cf6; padding: 15px; border-radius: 12px; cursor: pointer; margin-bottom: 10px; transition: 0.3s;">
                                        <i class='bx bx-cloud-upload' style="font-size: 28px; color: #a78bfa;"></i>
                                        <div style="font-size: 12px; color: #e2e8f0; margin-top: 5px;">คลิกเพื่อเลือกรูปสลิป (JPG, PNG)</div>
                                        <input type="file" name="slip_image" accept="image/*" required style="display: none;" onchange="document.getElementById('filename-<?= $order['order_id'] ?>').innerText = this.files[0].name">
                                        <div id="filename-<?= $order['order_id'] ?>" style="font-size: 11px; color: #22c55e; margin-top: 5px; font-weight: bold;"></div>
                                    </label>
                                    <button type="submit" style="width: 100%; background: #8b5cf6; color: white; border: none; padding: 10px; border-radius: 8px; font-weight: bold; cursor: pointer;">ยืนยันการโอนเงิน</button>
                                </form>
                            </div>
                        <?php } else { ?>
                            <div style="margin-top:15px; padding-top:15px; border-top:1px dashed #232b3e; text-align: center;">
                                <h4 style="color: #22c55e; margin-top: 0; margin-bottom: 10px; font-size: 13px;"><i class='bx bx-check-circle'></i> อัปโหลดหลักฐานแล้ว</h4>
                                <a href="<?= htmlspecialchars($order['slip_image']) ?>" target="_blank">
                                    <img src="<?= htmlspecialchars($order['slip_image']) ?>" style="max-width: 100px; border-radius: 8px; border: 1px solid #4c4a8a; opacity: 0.8;">
                                </a>
                                <div style="font-size:12px; margin-top: 10px;">
                                    <?php 
                                    $s_status = $order['slip_status'] ?? 'pending';
                                    if($s_status == 'pending') echo "<span style='color:#fbbf24;'><i class='bx bx-time-five bx-clock'></i> กำลังรอแอดมินตรวจสอบ</span>";
                                    elseif($s_status == 'approved') echo "<span style='color:#22c55e;'><i class='bx bx-check-shield'></i> ตรวจสอบสลิปถูกต้อง</span>";
                                    elseif($s_status == 'rejected') echo "<span style='color:#f43f5e;'><i class='bx bx-x-circle'></i> สลิปถูกปฏิเสธ กรุณาติดต่อแอดมิน</span>";
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="items-card">
            <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; color: white;"><i class='bx bx-package'></i> Items Ordered</h3>
            <div style="overflow-x: auto;">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th style="text-align: center;">Price</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql_items = "SELECT oi.*, p.p_name, p.p_image_url
                                    FROM order_items oi
                                    LEFT JOIN products p ON oi.p_id = p.id
                                    WHERE oi.order_id = '$order_id'";
                        $res_items = mysqli_query($conn, $sql_items);

                        if($res_items) {
                            while($item = mysqli_fetch_assoc($res_items)):
                                $img_src = (!empty($item['p_image_url']) && str_starts_with($item['p_image_url'], 'http')) ? $item['p_image_url'] : $item['p_image_url'] ?? '';
                        ?>
                        <tr>
                            <td>
                                <div class="prod-cell">
                                    <?php if($img_src): ?>
                                        <img src="<?= htmlspecialchars($img_src) ?>" class="prod-img">
                                    <?php else: ?>
                                        <div class="prod-img" style="display:flex;align-items:center;justify-content:center;color:#64748b;"><i class='bx bx-image'></i></div>
                                    <?php endif; ?>
                                    <span class="prod-name"><?= htmlspecialchars($item['p_name'] ?? 'Unknown Product') ?></span>
                                </div>
                            </td>
                            <td style="text-align: center; color: #94a3b8;">$<?= number_format($item['price'], 2) ?></td>
                            <td style="text-align: center; font-weight: bold; color: #94a3b8;">x<?= $item['quantity'] ?></td>
                            <td style="text-align: right; font-weight: bold; color: white;">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        </tr>
                        <?php 
                            endwhile; 
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="summary-box">
                <div class="sum-row">
                    <span>Subtotal</span>
                    <span style="color:white;">$<?= number_format($order['total_amount'], 2) ?></span>
                </div>
                <div class="sum-row">
                    <span>Shipping Fee</span>
                    <span style="color:#22c55e;">Free</span>
                </div>
                <div class="sum-total">
                    <span>Total</span>
                    <span style="color:#8b5cf6;">$<?= number_format($order['total_amount'], 2) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(!$is_cancelled && $current_step >= 2): ?>
<script>
// ===== Leaflet Map for Tracking =====
const map = L.map('trackingMap', {
    zoomControl: true,
    scrollWheelZoom: false,
});

// Dark tile layer
L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '©OpenStreetMap ©CARTO',
    maxZoom: 19,
}).addTo(map);

// Dynamic route points from database
<?php
$map_points = [];
foreach ($tracking as $t) {
    if (!empty($t['lat']) && !empty($t['lng'])) {
        $map_points[] = [
            'lat' => (float)$t['lat'],
            'lng' => (float)$t['lng'],
            'label' => $t['location'],
            'status' => $t['status']
        ];
    }
}
if (empty($map_points)) {
    // Fallback if no real coordinates yet
    $map_points = [
        ['lat' => 13.7563, 'lng' => 100.5018, 'label' => 'TechGear Warehouse (กรุงเทพฯ)', 'status' => 'รอดำเนินการ (ไม่มีข้อมูลพิกัด)']
    ];
}
?>
const routePoints = <?= json_encode($map_points) ?>;

if (routePoints.length > 0) {
    map.setView([routePoints[routePoints.length - 1].lat, routePoints[routePoints.length - 1].lng], 6);
} else {
    map.setView([13.7563, 100.5018], 6);
}

// Custom icon
const activeIcon = L.divIcon({
    className: '',
    html: '<div style="width:24px;height:24px;background:linear-gradient(135deg,#7c5cfc,#a78bfa);border-radius:50%;border:3px solid #fff;box-shadow:0 0 20px rgba(124,92,252,.6);animation:pulse 2s infinite;"></div>',
    iconSize: [24, 24],
    iconAnchor: [12, 12],
});

const warehouseIcon = L.divIcon({
    className: '',
    html: '<div style="width:18px;height:18px;background:#10b981;border-radius:50%;border:2px solid #fff;box-shadow:0 0 10px rgba(16,185,129,.5);"></div>',
    iconSize: [18, 18],
    iconAnchor: [9, 9],
});

const passedIcon = L.divIcon({
    className: '',
    html: '<div style="width:12px;height:12px;background:#64748b;border-radius:50%;border:2px solid #94a3b8;"></div>',
    iconSize: [12, 12],
    iconAnchor: [6, 6],
});

// Determine how many points to show based on status
const pointsToShow = <?= $current_step >= 3 ? count(json_encode($routePoints)) : 'Math.min(' . ($current_step + 1) . ', routePoints.length)' ?>;

// Add markers
const latLngs = [];
routePoints.forEach((point, i) => {
    if (i >= pointsToShow) return;
    
    let icon = i === pointsToShow - 1 ? activeIcon : (i === 0 ? warehouseIcon : passedIcon);
    const marker = L.marker([point.lat, point.lng], { icon }).addTo(map);
    marker.bindPopup(`<div style="font-size:12px;"><strong>${point.label}</strong><br><span style="color:#888;">${point.status}</span></div>`);
    latLngs.push([point.lat, point.lng]);
});

// Draw route line
if (latLngs.length > 1) {
    L.polyline(latLngs, {
        color: '#7c5cfc',
        weight: 3,
        opacity: 0.7,
        dashArray: '8, 8',
    }).addTo(map);
    
    map.fitBounds(latLngs, { padding: [40, 40] });
}

// Animate pulse CSS
const style = document.createElement('style');
style.textContent = `@keyframes pulse{0%,100%{box-shadow:0 0 15px rgba(124,92,252,.4);}50%{box-shadow:0 0 30px rgba(124,92,252,.8),0 0 50px rgba(124,92,252,.3);}}`;
document.head.appendChild(style);
</script>
<?php endif; ?>

<?php
include 'page/footer.php';
?>