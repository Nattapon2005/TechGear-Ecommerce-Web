<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php"); exit();
}
require_once '../config/db.php';

// ===== Handle Actions =====
$msg = ''; $msg_type = '';

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'update_status') {
        $oid = (int)$_POST['order_id'];
        $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
        mysqli_query($conn, "UPDATE orders SET order_status = '$new_status' WHERE order_id = $oid");
        
        // Auto-add tracking entry
        $desc = '';
        $loc = '';
        switch($new_status) {
            case 'Processing': $desc = 'คำสั่งซื้อได้รับการยืนยันและกำลังจัดเตรียมสินค้า'; $loc = 'TechGear Warehouse, Bangkok'; break;
            case 'Shipped': $desc = 'สินค้าออกจากคลังสินค้าแล้ว กำลังจัดส่ง'; $loc = 'Sorting Center, Bangkok'; break;
            case 'Completed': $desc = 'สินค้าจัดส่งถึงผู้รับเรียบร้อยแล้ว'; $loc = 'Delivered to customer'; break;
            case 'Cancelled': $desc = 'คำสั่งซื้อถูกยกเลิก'; $loc = '-'; break;
            default: $desc = "สถานะเปลี่ยนเป็น: $new_status"; $loc = '-';
        }
        $desc_esc = mysqli_real_escape_string($conn, $desc);
        $loc_esc = mysqli_real_escape_string($conn, $loc);
        mysqli_query($conn, "INSERT INTO order_tracking (order_id, status, location, description) VALUES ($oid, '$new_status', '$loc_esc', '$desc_esc')");
        
        $msg = "อัปเดตสถานะ Order #$oid เป็น $new_status สำเร็จ!";
        $msg_type = 'success';
    }
    
    if ($action === 'update_tracking') {
        $oid = (int)$_POST['order_id'];
        $loc = mysqli_real_escape_string($conn, $_POST['location']);
        $desc = mysqli_real_escape_string($conn, $_POST['description']);
        $status = mysqli_real_escape_string($conn, $_POST['tracking_status']);
        
        $lat_val = !empty($_POST['lat']) ? (float)$_POST['lat'] : 'NULL';
        $lng_val = !empty($_POST['lng']) ? (float)$_POST['lng'] : 'NULL';

        mysqli_query($conn, "INSERT INTO order_tracking (order_id, status, location, description, lat, lng) VALUES ($oid, '$status', '$loc', '$desc', $lat_val, $lng_val)");
        $msg = "เพิ่มข้อมูลติดตามพัสดุสำเร็จ!";
        $msg_type = 'success';
    }

    if ($action === 'approve_slip') {
        $oid = (int)$_POST['order_id'];
        mysqli_query($conn, "UPDATE orders SET slip_status = 'approved', order_status = 'Processing' WHERE order_id = $oid");
        mysqli_query($conn, "INSERT INTO order_tracking (order_id, status, location, description) VALUES ($oid, 'Payment Confirmed', 'TechGear HQ', 'ชำระเงินเรียบร้อย – แอดมินตรวจสอบสลิปแล้ว')");
        $msg = "อนุมัติสลิปสำเร็จ! สถานะเปลี่ยนเป็น Processing";
        $msg_type = 'success';
    }

    if ($action === 'reject_slip') {
        $oid = (int)$_POST['order_id'];
        mysqli_query($conn, "UPDATE orders SET slip_status = 'rejected' WHERE order_id = $oid");
        $msg = "ปฏิเสธสลิปสำเร็จ!";
        $msg_type = 'error';
    }
}

// Filter
$filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$where = "1=1";
if ($filter) $where .= " AND o.order_status = '$filter'";
if ($search) $where .= " AND (o.order_id LIKE '%$search%' OR u.username LIKE '%$search%' OR o.fullname LIKE '%$search%')";

$orders = [];
$q = mysqli_query($conn, "SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.user_id WHERE $where ORDER BY o.created_at DESC");
if ($q) while($r = mysqli_fetch_assoc($q)) $orders[] = $r;

// Count by status
$cnt_all = (int)mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM orders"))['0'];
$cnt_pending = (int)mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM orders WHERE order_status='Pending'"))['0'];
$cnt_processing = (int)mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM orders WHERE order_status='Processing'"))['0'];
$cnt_shipped = (int)mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM orders WHERE order_status='Shipped'"))['0'];
$cnt_completed = (int)mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM orders WHERE order_status='Completed'"))['0'];

$pending_orders = $cnt_pending;
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TechGear Admin — Orders</title>
<?php include 'admin-header.php'; ?>
<style>
.status-tabs{display:flex;gap:8px;flex-wrap:wrap;}
.status-tab{padding:7px 16px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;color:var(--muted);border:1px solid var(--border);transition:all .25s;}
.status-tab:hover{border-color:var(--purple);color:var(--purple-lt);}
.status-tab.active{background:rgba(124,92,252,.15);color:var(--purple-lt);border-color:rgba(124,92,252,.3);}
.status-tab .count{margin-left:4px;font-size:10px;opacity:.7;}

/* Tracking Timeline */
.timeline{position:relative;padding-left:28px;margin-top:16px;}
.timeline::before{content:'';position:absolute;left:8px;top:0;bottom:0;width:2px;background:var(--bg4);}
.timeline-item{position:relative;margin-bottom:16px;animation:fadeSlideUp .4s both;}
.timeline-item:nth-child(1){animation-delay:.1s;}
.timeline-item:nth-child(2){animation-delay:.2s;}
.timeline-item:nth-child(3){animation-delay:.3s;}
.timeline-dot{position:absolute;left:-24px;top:4px;width:12px;height:12px;border-radius:50%;background:var(--purple);border:2px solid var(--bg2);z-index:1;}
.timeline-dot.green{background:var(--green);}
.timeline-dot.amber{background:var(--amber);}
.timeline-dot.red{background:var(--red);}
.timeline-status{font-size:13px;font-weight:600;margin-bottom:2px;}
.timeline-loc{font-size:11px;color:var(--muted);}
.timeline-desc{font-size:12px;color:var(--text);opacity:.8;margin-top:2px;}
.timeline-time{font-size:10px;color:var(--muted);margin-top:4px;}

/* Order detail panel */
.detail-panel{display:none;background:var(--bg);border-radius:12px;padding:20px;margin-top:8px;border:1px solid var(--border);animation:fadeScaleIn .3s both;}
.detail-panel.active{display:block;}

.slip-img{max-width:150px;border-radius:10px;border:1px solid var(--border);cursor:pointer;transition:transform .3s;}
.slip-img:hover{transform:scale(1.1);}
</style>
</head>
<body>

<?php if($msg): ?>
<div class="toast toast-<?= $msg_type ?>"><?= $msg ?></div>
<?php endif; ?>

<?php include 'sidebar.php'; ?>

<main class="main">
    <div class="topbar">
        <div>
            <h1>🛒 Orders Management</h1>
            <p>จัดการคำสั่งซื้อ อัปเดตสถานะ และติดตามพัสดุ</p>
        </div>
    </div>

    <div class="toolbar">
        <div class="search-box">
            <i class='bx bx-search'></i>
            <input type="text" id="searchInput" placeholder="ค้นหา Order ID, ชื่อลูกค้า..." value="<?= htmlspecialchars($search) ?>" onkeyup="if(event.key==='Enter') location.href='orders.php?q='+this.value+'&status=<?= $filter ?>'">
        </div>
        <div class="status-tabs">
            <a href="orders.php" class="status-tab <?= !$filter?'active':'' ?>">ทั้งหมด <span class="count"><?= $cnt_all ?></span></a>
            <a href="orders.php?status=Pending" class="status-tab <?= $filter==='Pending'?'active':'' ?>">Pending <span class="count"><?= $cnt_pending ?></span></a>
            <a href="orders.php?status=Processing" class="status-tab <?= $filter==='Processing'?'active':'' ?>">Processing <span class="count"><?= $cnt_processing ?></span></a>
            <a href="orders.php?status=Shipped" class="status-tab <?= $filter==='Shipped'?'active':'' ?>">Shipped <span class="count"><?= $cnt_shipped ?></span></a>
            <a href="orders.php?status=Completed" class="status-tab <?= $filter==='Completed'?'active':'' ?>">Completed <span class="count"><?= $cnt_completed ?></span></a>
        </div>
    </div>

    <div class="admin-content-card">
        <?php if(empty($orders)): ?>
            <div class="empty-state">
                <i class='bx bx-receipt'></i>
                <h3>ไม่พบคำสั่งซื้อ</h3>
                <p>ยังไม่มีคำสั่งซื้อที่ตรงกับเงื่อนไข</p>
            </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>ลูกค้า</th>
                    <th>ยอดรวม</th>
                    <th>ชำระเงิน</th>
                    <th>สลิป</th>
                    <th>สถานะ</th>
                    <th>วันที่</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($orders as $o): 
                $s_class = strtolower($o['order_status']);
                // Get tracking for this order
                $tracking = [];
                $qt = mysqli_query($conn, "SELECT * FROM order_tracking WHERE order_id = {$o['order_id']} ORDER BY created_at ASC");
                if($qt) while($t = mysqli_fetch_assoc($qt)) $tracking[] = $t;
            ?>
                <tr>
                    <td style="font-weight:700;color:var(--purple-lt);font-size:12px;">#<?= str_pad($o['order_id'],5,'0',STR_PAD_LEFT) ?></td>
                    <td>
                        <div style="font-weight:600;"><?= htmlspecialchars($o['username'] ?? $o['fullname'] ?? 'Guest') ?></div>
                        <div style="font-size:11px;color:var(--muted);"><?= htmlspecialchars($o['email'] ?? '') ?></div>
                    </td>
                    <td style="font-weight:700;color:var(--green);">$<?= number_format($o['total_amount'],2) ?></td>
                    <td style="font-size:12px;"><?= htmlspecialchars($o['payment_method'] ?? 'N/A') ?></td>
                    <td>
                        <?php if(!empty($o['slip_image'])): ?>
                            <img src="../<?= htmlspecialchars($o['slip_image']) ?>" class="img-thumb slip-img" onclick="window.open('../<?= htmlspecialchars($o['slip_image']) ?>')">
                            <?php
                            $ss = $o['slip_status'] ?? 'pending';
                            if($ss === 'pending') echo '<div style="font-size:10px;color:#fbbf24;margin-top:4px;">⏳ รอตรวจ</div>';
                            elseif($ss === 'approved') echo '<div style="font-size:10px;color:#34d399;margin-top:4px;">✅ อนุมัติ</div>';
                            elseif($ss === 'rejected') echo '<div style="font-size:10px;color:#fb7185;margin-top:4px;">❌ ปฏิเสธ</div>';
                            ?>
                        <?php else: ?>
                            <span style="font-size:11px;color:var(--muted);">ยังไม่แนบ</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="pill pill-<?= $s_class ?>"><?= $o['order_status'] ?></span></td>
                    <td style="font-size:12px;color:var(--muted);"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            <button class="btn btn-outline btn-xs" onclick="toggleDetail(<?= $o['order_id'] ?>)"><i class='bx bx-show'></i></button>
                            <?php if(!empty($o['slip_image']) && ($o['slip_status'] ?? 'pending') === 'pending'): ?>
                                <form method="POST" style="display:inline;"><input type="hidden" name="action" value="approve_slip"><input type="hidden" name="order_id" value="<?= $o['order_id'] ?>"><button class="btn btn-success btn-xs" title="อนุมัติสลิป"><i class='bx bx-check'></i></button></form>
                                <form method="POST" style="display:inline;"><input type="hidden" name="action" value="reject_slip"><input type="hidden" name="order_id" value="<?= $o['order_id'] ?>"><button class="btn btn-danger btn-xs" title="ปฏิเสธสลิป"><i class='bx bx-x'></i></button></form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="8" style="padding:0;border:none;">
                        <div class="detail-panel" id="detail-<?= $o['order_id'] ?>">
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">
                                <!-- Order Info -->
                                <div>
                                    <h4 style="font-size:13px;color:var(--purple-lt);margin-bottom:10px;"><i class='bx bx-info-circle'></i> ข้อมูลคำสั่งซื้อ</h4>
                                    <div style="font-size:12px;color:var(--muted);line-height:2;">
                                        <strong>ชื่อผู้รับ:</strong> <?= htmlspecialchars($o['fullname'] ?? 'N/A') ?><br>
                                        <strong>โทร:</strong> <?= htmlspecialchars($o['phone'] ?? 'N/A') ?><br>
                                        <strong>ที่อยู่:</strong> <?= htmlspecialchars($o['address'] ?? 'N/A') ?>
                                    </div>
                                </div>

                                <!-- Status Update -->
                                <div>
                                    <h4 style="font-size:13px;color:var(--purple-lt);margin-bottom:10px;"><i class='bx bx-edit'></i> อัปเดตสถานะ</h4>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                                        <select name="new_status" class="form-control" style="margin-bottom:8px;">
                                            <option value="Pending" <?= $o['order_status']==='Pending'?'selected':'' ?>>⏳ Pending</option>
                                            <option value="Processing" <?= $o['order_status']==='Processing'?'selected':'' ?>>⚙️ Processing</option>
                                            <option value="Shipped" <?= $o['order_status']==='Shipped'?'selected':'' ?>>🚚 Shipped</option>
                                            <option value="Completed" <?= $o['order_status']==='Completed'?'selected':'' ?>>✅ Completed</option>
                                            <option value="Cancelled" <?= $o['order_status']==='Cancelled'?'selected':'' ?>>❌ Cancelled</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm" style="width:100%;">อัปเดตสถานะ</button>
                                    </form>

                                    <h4 style="font-size:13px;color:var(--teal);margin:16px 0 10px;"><i class='bx bx-map-pin'></i> เพิ่มตำแหน่งพัสดุ</h4>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_tracking">
                                        <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                                        <input type="text" name="tracking_status" class="form-control" placeholder="สถานะ เช่น In Transit" style="margin-bottom:6px;" required>
                                        <input type="text" name="location" class="form-control" placeholder="ตำแหน่ง เช่น ศูนย์คัดแยก กรุงเทพ" style="margin-bottom:6px;" required>
                                        <input type="text" name="description" class="form-control" placeholder="รายละเอียด (ไม่บังคับ)" style="margin-bottom:6px;">
                                        <div style="display:flex;gap:6px;margin-bottom:8px;">
                                            <input type="number" step="0.0000001" name="lat" class="form-control" placeholder="Lat (13.xx)">
                                            <input type="number" step="0.0000001" name="lng" class="form-control" placeholder="Lng (100.xx)">
                                        </div>
                                        <button type="submit" class="btn btn-success btn-sm" style="width:100%;"><i class='bx bx-plus'></i> เพิ่มจุดติดตาม</button>
                                    </form>
                                </div>

                                <!-- Tracking Timeline -->
                                <div>
                                    <h4 style="font-size:13px;color:var(--green);margin-bottom:10px;"><i class='bx bx-map'></i> ไทม์ไลน์การจัดส่ง</h4>
                                    <?php if(empty($tracking)): ?>
                                        <div style="font-size:12px;color:var(--muted);text-align:center;padding:20px;">ยังไม่มีข้อมูลติดตาม</div>
                                    <?php else: ?>
                                        <div class="timeline">
                                        <?php foreach(array_reverse($tracking) as $ti => $t): 
                                            $dot_class = '';
                                            if(stripos($t['status'],'completed')!==false || stripos($t['status'],'delivered')!==false) $dot_class='green';
                                            elseif(stripos($t['status'],'cancelled')!==false || stripos($t['status'],'rejected')!==false) $dot_class='red';
                                            elseif(stripos($t['status'],'pending')!==false) $dot_class='amber';
                                        ?>
                                            <div class="timeline-item">
                                                <div class="timeline-dot <?= $dot_class ?>"></div>
                                                <div class="timeline-status"><?= htmlspecialchars($t['status']) ?></div>
                                                <div class="timeline-loc"><i class='bx bx-map-pin' style="font-size:11px;"></i> <?= htmlspecialchars($t['location']) ?></div>
                                                <?php if($t['description']): ?>
                                                    <div class="timeline-desc"><?= htmlspecialchars($t['description']) ?></div>
                                                <?php endif; ?>
                                                <div class="timeline-time"><?= date('d/m/Y H:i:s', strtotime($t['created_at'])) ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
function toggleDetail(id) {
    const el = document.getElementById('detail-' + id);
    el.classList.toggle('active');
}
</script>
</body>
</html>
