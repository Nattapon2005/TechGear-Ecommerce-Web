<?php
session_start();

// ===== Session Check =====
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php'; // ใช้การเชื่อมต่อ mysqli

// ฟังก์ชันช่วยดึงค่าแถวเดียว
function get_single_value($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        return $row[0];
    }
    return 0;
}

// เช็คว่ามีตาราง orders ไหม (เผื่อระบบยังไม่สมบูรณ์)
$has_orders = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'orders'")) > 0;

// ==========================================
// 📊 ดึงข้อมูลสถิติต่างๆ (ของจริงจาก DB)
// ==========================================

$revenue = 0; 
$pending_orders = 0;
$total_orders = 0;

if ($has_orders) {
    // รวมยอดขายเฉพาะบิลที่ไม่ถูกยกเลิก (ดึงยอดจริงมาโชว์เลย!)
    $revenue = get_single_value($conn, "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status != 'Cancelled'");
    $pending_orders = get_single_value($conn, "SELECT COUNT(*) FROM orders WHERE order_status = 'Pending'");
    $total_orders = get_single_value($conn, "SELECT COUNT(*) FROM orders");
}

// สต็อกรวม (อิงจากตาราง products)
$stock_total = get_single_value($conn, "SELECT COUNT(*) FROM products");

// เช็คว่ามีคอลัมน์ p_stock หรือไม่
$has_stock_col = false;
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'p_stock'");
if ($check_col && mysqli_num_rows($check_col) > 0) {
    $has_stock_col = true;
}

if ($has_stock_col) {
    $stock_in  = get_single_value($conn, "SELECT COUNT(*) FROM products WHERE p_stock > 0");
    $low_stock = get_single_value($conn, "SELECT COUNT(*) FROM products WHERE p_stock > 0 AND p_stock <= 5");
} else {
    $stock_in  = $stock_total;
    $low_stock = 0;
}
$stock_pct = $stock_total > 0 ? round(($stock_in / $stock_total) * 100) : 0;

// จำนวน Users ทั้งหมด (ที่ไม่ใช่ admin)
$total_users = get_single_value($conn, "SELECT COUNT(*) FROM users WHERE role != 'admin'");

// ==========================================
// 📈 ดึงข้อมูลทำกราฟ
// ==========================================

// 1. หมวดหมู่สินค้า (Donut)
$cat_labels = []; $cat_data = [];
$q_cat = mysqli_query($conn, "SELECT c.cat_name, COUNT(*) AS cnt FROM products p LEFT JOIN categories c ON p.cat_id = c.cat_id GROUP BY c.cat_id ORDER BY cnt DESC LIMIT 5");
if ($q_cat && mysqli_num_rows($q_cat) > 0) {
    while($row = mysqli_fetch_assoc($q_cat)) {
        $cat_labels[] = $row['cat_name'] ?? 'Uncategorized';
        $cat_data[]   = (int)$row['cnt'];
    }
} else {
    $cat_labels = ['ยังไม่มีข้อมูล']; $cat_data = [1];
}
$cat_labels_json = json_encode($cat_labels);
$cat_data_json   = json_encode($cat_data);

// 2. กราฟยอดขาย 6 เดือนย้อนหลัง (คำนวณเดือนให้อัตโนมัติ)
$months_labels = [];
$sales_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month_name = date('M', strtotime("-$i months"));
    $m = date('m', strtotime("-$i months"));
    $y = date('Y', strtotime("-$i months"));
    
    $monthly_sale = 0;
    if ($has_orders) {
        $monthly_sale = get_single_value($conn, "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE MONTH(created_at)='$m' AND YEAR(created_at)='$y' AND order_status != 'Cancelled'");
    }
    
    $months_labels[] = $month_name;
    $sales_data[] = (float)$monthly_sale;
}
$chart_labels = json_encode($months_labels);
$chart_data   = json_encode($sales_data);

// 3. กราฟสถานะออเดอร์
$os_labels_array = ['Completed', 'Pending', 'Processing', 'Shipped', 'Cancelled'];
$os_data_array   = [0, 0, 0, 0, 0];
if ($has_orders) {
    $q_os = mysqli_query($conn, "SELECT order_status, COUNT(*) as cnt FROM orders GROUP BY order_status");
    if($q_os) {
        while($r = mysqli_fetch_assoc($q_os)) {
            $idx = array_search($r['order_status'], $os_labels_array);
            if($idx !== false) $os_data_array[$idx] = (int)$r['cnt'];
        }
    }
}
$os_labels = json_encode($os_labels_array);
$os_data   = json_encode($os_data_array);

// ==========================================
// 📋 ตารางข้อมูลด้านล่าง
// ==========================================

// Top 5 สินค้าแพงสุด
$top_products = [];
$q_top = mysqli_query($conn, "SELECT p.*, c.cat_name FROM products p LEFT JOIN categories c ON p.cat_id = c.cat_id ORDER BY p_price DESC LIMIT 5");
if ($q_top && mysqli_num_rows($q_top) > 0) {
    while($row = mysqli_fetch_assoc($q_top)) { $top_products[] = $row; }
}

// ออเดอร์ล่าสุด 5 รายการ
$recent_orders = [];
if ($has_orders) {
    $q_recent = mysqli_query($conn, "SELECT o.order_id, o.total_amount, o.order_status, o.created_at, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.user_id ORDER BY o.created_at DESC LIMIT 5");
    if ($q_recent && mysqli_num_rows($q_recent) > 0) {
        while($row = mysqli_fetch_assoc($q_recent)) { $recent_orders[] = $row; }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TechGear Admin — Dashboard</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<style>
  /* ===== CSS Variables ===== */
  :root {
    --bg:        #08090e;
    --bg2:       #0f1117;
    --bg3:       #191c28;
    --bg4:       #21253a;
    --border:    rgba(255,255,255,0.06);
    --purple:    #7c5cfc;
    --purple-lt: #a78bfa;
    --purple-glow: rgba(124,92,252,.35);
    --teal:      #14b8a6;
    --amber:     #f59e0b;
    --red:       #f43f5e;
    --green:     #10b981;
    --blue:      #3b82f6;
    --cyan:      #06b6d4;
    --text:      #f1f5f9;
    --muted:     #64748b;
    --sidebar-w: 250px;
    --radius:    16px;
  }

  * { margin:0; padding:0; box-sizing:border-box; }
  body { 
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif; 
    background: var(--bg); 
    color: var(--text); 
    display:flex; 
    min-height:100vh;
    overflow-x: hidden;
  }

  /* ===== Scrollbar ===== */
  ::-webkit-scrollbar { width:5px; }
  ::-webkit-scrollbar-thumb { background: var(--bg3); border-radius:4px; }
  ::-webkit-scrollbar-track { background: transparent; }

  /* ===== Sidebar ===== */
  .sidebar { 
    width: var(--sidebar-w); 
    background: var(--bg2); 
    height: 100vh; 
    position: fixed; 
    top:0; left:0; 
    display:flex; 
    flex-direction:column; 
    border-right: 1px solid var(--border); 
    z-index: 100;
    backdrop-filter: blur(20px);
  }
  .sidebar-logo { 
    padding: 24px 22px 22px; 
    font-size: 19px; 
    font-weight: 800; 
    letter-spacing: -0.5px; 
    display:flex; 
    align-items:center; 
    gap:10px; 
    border-bottom: 1px solid var(--border); 
  }
  .sidebar-logo span { color: var(--purple-lt); font-size:24px; }
  .sidebar-nav { flex:1; padding: 18px 14px; overflow-y:auto; }
  .nav-label { font-size:10px; font-weight:700; letter-spacing:.1em; color: var(--muted); text-transform:uppercase; padding: 12px 10px 6px; }
  .nav-item { 
    display:flex; align-items:center; gap:12px; 
    padding: 11px 14px; border-radius:12px; 
    color: var(--muted); text-decoration:none; 
    font-size:13.5px; font-weight: 500;
    transition: all .25s cubic-bezier(.4,0,.2,1); 
    margin-bottom:2px; 
    position: relative;
    overflow: hidden;
  }
  .nav-item::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(124,92,252,.15), rgba(124,92,252,.05));
    opacity: 0;
    transition: opacity .25s;
    border-radius: 12px;
  }
  .nav-item:hover { color: var(--text); transform: translateX(4px); }
  .nav-item:hover::before { opacity: 1; }
  .nav-item.active { 
    background: linear-gradient(135deg, rgba(124,92,252,.2), rgba(124,92,252,.08)); 
    color: var(--purple-lt); 
    box-shadow: 0 0 20px rgba(124,92,252,.1);
  }
  .nav-item.active i { color: var(--purple); }
  .nav-item i { font-size:18px; flex-shrink:0; position: relative; z-index: 1; }
  .nav-item span, .nav-item:not(i) { position: relative; z-index: 1; }
  .nav-item.danger { color: #f43f5e88; }
  .nav-item.danger:hover { color: var(--red); transform: translateX(4px); }
  .nav-item.danger:hover::before { background: linear-gradient(135deg, rgba(244,63,94,.15), rgba(244,63,94,.05)); opacity: 1; }
  .badge-nav { margin-left:auto; font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; position: relative; z-index: 1; }
  .sidebar-footer { padding:16px; border-top:1px solid var(--border); }
  .admin-card { 
    display:flex; align-items:center; gap:10px; 
    padding:12px; background:var(--bg3); border-radius:12px;
    transition: all .3s;
    cursor: default;
  }
  .admin-card:hover { background: var(--bg4); transform: scale(1.02); }
  .admin-card i { font-size: 36px; color: var(--purple-lt); }
  .admin-card .info { flex:1; min-width:0; }
  .admin-card .info strong { display:block; font-size:13px; font-weight:600; }
  .admin-card .info small { font-size:11px; color: var(--muted); }
  .admin-card .dot { width:8px; height:8px; border-radius:50%; background: var(--green); flex-shrink:0; box-shadow: 0 0 8px var(--green); animation: pulse-dot 2s infinite; }

  @keyframes pulse-dot {
    0%, 100% { box-shadow: 0 0 6px var(--green); }
    50% { box-shadow: 0 0 14px var(--green), 0 0 24px rgba(16,185,129,.3); }
  }

  /* ===== Main ===== */
  .main { margin-left: var(--sidebar-w); flex:1; padding:28px 30px 40px; min-width:0; position: relative; }

  /* ===== Animated Background Mesh ===== */
  .bg-mesh {
    position: fixed;
    top: 0; left: var(--sidebar-w); right: 0; bottom: 0;
    pointer-events: none;
    z-index: 0;
    overflow: hidden;
  }
  .bg-mesh .orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    opacity: 0.07;
    animation: float-orb 20s ease-in-out infinite;
  }
  .bg-mesh .orb:nth-child(1) { width:600px; height:600px; background: var(--purple); top:-200px; right:-100px; animation-delay: 0s; }
  .bg-mesh .orb:nth-child(2) { width:500px; height:500px; background: var(--teal); bottom:-150px; left:100px; animation-delay: -7s; }
  .bg-mesh .orb:nth-child(3) { width:400px; height:400px; background: var(--amber); top:50%; left:50%; animation-delay: -14s; }

  @keyframes float-orb {
    0%, 100% { transform: translate(0,0) scale(1); }
    33% { transform: translate(40px,-30px) scale(1.1); }
    66% { transform: translate(-20px,40px) scale(0.9); }
  }

  /* ===== Content (above bg) ===== */
  .main > *:not(.bg-mesh) { position: relative; z-index: 1; }

  /* ===== Topbar ===== */
  .topbar { 
    display:flex; justify-content:space-between; align-items:center; margin-bottom:26px;
    animation: fadeSlideDown .6s cubic-bezier(.4,0,.2,1) both;
  }
  .topbar h1 { 
    font-size:24px; font-weight:800; letter-spacing:-0.5px; 
    background: linear-gradient(135deg, var(--text) 0%, var(--purple-lt) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  .topbar p { font-size:13px; color: var(--muted); margin-top:3px; }
  .topbar-right { display:flex; align-items:center; gap:12px; }
  .btn-icon { 
    width:40px; height:40px; border-radius:12px; 
    background: var(--bg2); border:1px solid var(--border); 
    display:flex; align-items:center; justify-content:center; 
    cursor:pointer; color: var(--muted); 
    transition: all .25s cubic-bezier(.4,0,.2,1); 
    position:relative;
    backdrop-filter: blur(10px);
  }
  .btn-icon:hover { 
    color: var(--text); 
    border-color: var(--purple); 
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(124,92,252,.2);
  }
  .notif-badge { position:absolute; top:7px; right:7px; width:8px; height:8px; border-radius:50%; background: var(--red); border:2px solid var(--bg); animation: pulse-dot 2s infinite; }
  .date-chip { 
    font-size:12px; color: var(--muted); 
    background: var(--bg2); border:1px solid var(--border); 
    padding:8px 16px; border-radius:10px;
    backdrop-filter: blur(10px);
    transition: all .25s;
  }
  .date-chip:hover { border-color: rgba(124,92,252,.3); }

  /* ===== Entrance Animations ===== */
  @keyframes fadeSlideDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeSlideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeScaleIn {
    from { opacity: 0; transform: scale(0.92); }
    to { opacity: 1; transform: scale(1); }
  }
  @keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
  }
  @keyframes countUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* ===== Stat Cards ===== */
  .stats-grid { display:grid; grid-template-columns: repeat(4,1fr); gap:18px; margin-bottom:24px; }
  .stat-card { 
    background: linear-gradient(145deg, var(--bg2), var(--bg3)); 
    border-radius: var(--radius); 
    border:1px solid var(--border); 
    padding:22px; 
    position:relative; 
    overflow:hidden; 
    transition: all .35s cubic-bezier(.4,0,.2,1); 
    cursor: default;
    animation: fadeSlideUp .6s cubic-bezier(.4,0,.2,1) both;
    /* 3D tilt perspective */
    transform-style: preserve-3d;
    perspective: 800px;
  }
  .stat-card:nth-child(1) { animation-delay: .1s; }
  .stat-card:nth-child(2) { animation-delay: .2s; }
  .stat-card:nth-child(3) { animation-delay: .3s; }
  .stat-card:nth-child(4) { animation-delay: .4s; }

  .stat-card:hover { 
    border-color: var(--card-accent, rgba(124,92,252,.4));
    box-shadow: 0 8px 32px var(--card-shadow, rgba(124,92,252,.15)),
                0 0 60px var(--card-shadow, rgba(124,92,252,.05));
  }

  /* Animated gradient border on hover */
  .stat-card::before { 
    content:''; position:absolute; top:0; right:0; 
    width:90px; height:90px; border-radius:50%; 
    background: var(--accent, rgba(124,92,252,.1)); 
    transform: translate(20px,-20px); 
    transition: all .5s cubic-bezier(.4,0,.2,1);
  }
  .stat-card:hover::before {
    width: 140px; height: 140px;
    opacity: .8;
  }

  /* Shimmer line */
  .stat-card::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--card-accent, var(--purple)), transparent);
    opacity: 0;
    transition: opacity .4s;
  }
  .stat-card:hover::after { opacity: 1; }

  .stat-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; }
  .stat-icon { 
    width:44px; height:44px; border-radius:12px; 
    display:flex; align-items:center; justify-content:center; font-size:20px;
    transition: all .35s cubic-bezier(.4,0,.2,1);
  }
  .stat-card:hover .stat-icon { transform: scale(1.15) rotate(-5deg); }
  .stat-label { font-size:12px; color: var(--muted); font-weight:600; letter-spacing: .02em; }
  .stat-value { 
    font-size:28px; font-weight:800; letter-spacing:-0.5px; 
    line-height:1; margin-bottom:10px;
    animation: countUp .8s cubic-bezier(.4,0,.2,1) both;
  }
  .stat-badge { 
    display:inline-flex; align-items:center; gap:4px; 
    font-size:11px; padding:4px 10px; border-radius:8px; font-weight:600;
    transition: all .25s;
  }
  .stat-card:hover .stat-badge { transform: translateX(3px); }
  .badge-up   { background:rgba(16,185,129,.12); color:#34d399; }
  .badge-down { background:rgba(244,63,94,.12); color:#fb7185; }
  .badge-warn { background:rgba(245,158,11,.12); color:#fbbf24; }
  .badge-info { background:rgba(124,92,252,.12); color: var(--purple-lt); }

  /* Progress bar */
  .prog-wrap { margin-top:14px; }
  .prog-label { display:flex; justify-content:space-between; font-size:11px; color:var(--muted); margin-bottom:6px; font-weight: 500; }
  .prog-bg { height:6px; background:var(--bg); border-radius:4px; overflow:hidden; }
  .prog-fill { 
    height:100%; border-radius:4px; 
    background: linear-gradient(90deg, var(--purple), var(--purple-lt), var(--cyan)); 
    background-size: 200% 100%;
    animation: shimmer 3s linear infinite;
    transition: width 1.2s cubic-bezier(.4,0,.2,1);
    width: 0;
  }

  /* ===== Charts Row ===== */
  .charts-row { display:grid; grid-template-columns:2fr 1fr 1fr; gap:18px; margin-bottom:24px; }
  .chart-card { 
    background: linear-gradient(145deg, var(--bg2), rgba(25,28,40,.8)); 
    border-radius: var(--radius); 
    border:1px solid var(--border); 
    padding:22px;
    transition: all .35s cubic-bezier(.4,0,.2,1);
    animation: fadeSlideUp .6s cubic-bezier(.4,0,.2,1) both;
    position: relative;
    overflow: hidden;
  }
  .chart-card:nth-child(1) { animation-delay: .3s; }
  .chart-card:nth-child(2) { animation-delay: .4s; }
  .chart-card:nth-child(3) { animation-delay: .5s; }

  .chart-card:hover { 
    border-color: rgba(124,92,252,.25); 
    transform: translateY(-3px);
    box-shadow: 0 12px 40px rgba(0,0,0,.3);
  }
  .card-title { font-size:15px; font-weight:700; margin-bottom:4px; letter-spacing: -.02em; }
  .card-sub { font-size:12px; color:var(--muted); margin-bottom:18px; font-weight: 500; }

  /* ===== Bottom Row ===== */
  .bottom-row { display:grid; grid-template-columns:1fr 1fr; gap:18px; }

  /* ===== Table ===== */
  .data-table { width:100%; border-collapse:collapse; }
  .data-table th { 
    font-size:10px; font-weight:700; letter-spacing:.08em; 
    text-transform:uppercase; color:var(--muted); 
    padding:0 0 12px; text-align:left; 
    border-bottom:1px solid var(--border); 
  }
  .data-table td { 
    padding:12px 0; font-size:13px; 
    border-bottom:1px solid var(--border); 
    vertical-align:middle; 
    transition: all .2s;
  }
  .data-table tr:last-child td { border-bottom:none; }
  .data-table tr { transition: all .25s; }
  .data-table tr:hover td { background:rgba(124,92,252,.04); }
  .data-table tr:hover { transform: scale(1.01); }

  /* Status pill */
  .status-pill { 
    font-size:11px; font-weight:600; padding:4px 10px; 
    border-radius:20px; text-transform:capitalize; display:inline-block;
    transition: all .25s;
  }
  .data-table tr:hover .status-pill { transform: scale(1.05); }
  .status-pending    { background:rgba(245,158,11,.15); color:#fbbf24; }
  .status-processing { background:rgba(124,92,252,.15); color:var(--purple-lt); }
  .status-shipped    { background:rgba(20,184,166,.15);  color:#2dd4bf; }
  .status-completed  { background:rgba(16,185,129,.15);  color:#34d399; }
  .status-cancelled  { background:rgba(244,63,94,.15);   color:#fb7185; }

  /* Product mini row */
  .prod-row { display:flex; align-items:center; gap:10px; }
  .prod-img-placeholder { 
    width:38px; height:38px; border-radius:10px; 
    background:var(--bg); display:flex; align-items:center; 
    justify-content:center; color:var(--muted); font-size:14px; flex-shrink:0;
    transition: all .3s;
    overflow: hidden;
  }
  .data-table tr:hover .prod-img-placeholder { transform: scale(1.1) rotate(-3deg); box-shadow: 0 4px 12px rgba(0,0,0,.3); }
  .prod-img-placeholder img { width:100%; height:100%; border-radius:10px; object-fit:cover; }
  .prod-name { font-size:13px; font-weight:600; }
  .prod-sold { font-size:11px; color:var(--muted); font-weight: 500; }

  /* rank badge */
  .rank { 
    width:24px; height:24px; border-radius:8px; 
    background:var(--bg); font-size:11px; font-weight:700; 
    color:var(--muted); display:flex; align-items:center; 
    justify-content:center; flex-shrink:0;
    transition: all .3s;
  }
  .rank-1 { background: linear-gradient(135deg, rgba(245,158,11,.3), rgba(245,158,11,.1)); color:#fbbf24; }
  .data-table tr:hover .rank-1 { box-shadow: 0 0 12px rgba(245,158,11,.3); }

  /* ===== Floating Particles (mouse follow) ===== */
  .particle-canvas {
    position: fixed;
    top: 0; left: var(--sidebar-w); right: 0; bottom: 0;
    pointer-events: none;
    z-index: 0;
  }

  /* ===== Mouse Glow ===== */
  .mouse-glow {
    position: fixed;
    width: 350px; height: 350px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(124,92,252,.08) 0%, transparent 70%);
    pointer-events: none;
    z-index: 0;
    transform: translate(-50%, -50%);
    transition: opacity .3s;
    opacity: 0;
  }
  .mouse-glow.visible { opacity: 1; }

  /* ===== Responsive ===== */
  @media(max-width:1200px) {
    .stats-grid { grid-template-columns:repeat(2,1fr); }
    .charts-row { grid-template-columns:1fr 1fr; }
    .bottom-row { grid-template-columns: 1fr; }
  }
  @media(max-width:768px) {
    .stats-grid { grid-template-columns:1fr; }
    .charts-row { grid-template-columns:1fr; }
    .sidebar { display: none; }
    .main { margin-left: 0; padding: 20px; }
  }

  /* ===== Magnetic Button Effect ===== */
  .magnetic { transition: transform .2s cubic-bezier(.4,0,.2,1); }
</style>
</head>
<body>

<!-- Mouse glow effect -->
<div class="mouse-glow" id="mouseGlow"></div>

<!-- Background mesh -->
<div class="bg-mesh">
    <div class="orb"></div>
    <div class="orb"></div>
    <div class="orb"></div>
</div>

<!-- Particle canvas -->
<canvas class="particle-canvas" id="particleCanvas"></canvas>

<?php include 'sidebar.php'; ?>

<main class="main">

  <div class="topbar">
    <div>
      <h1>Dashboard</h1>
      <p><?= date('l, d F Y') ?></p>
    </div>
    <div class="topbar-right">
      <div class="date-chip"><i class='bx bx-time-five' style="vertical-align:-2px;margin-right:4px;"></i><?= date('H:i') ?> ICT</div>
      <div class="btn-icon magnetic"><i class='bx bx-bell'></i><?php if($pending_orders>0): ?><span class="notif-badge"></span><?php endif; ?></div>
    </div>
  </div>

  <div class="stats-grid">
    <div class="stat-card tilt-card" style="--accent:rgba(124,92,252,.12); --card-accent:rgba(124,92,252,.4); --card-shadow:rgba(124,92,252,.15);">
      <div class="stat-header">
        <div class="stat-icon" style="background:rgba(124,92,252,.15);color:var(--purple-lt)"><i class='bx bx-dollar-circle'></i></div>
        <span class="stat-label">ยอดขายสุทธิ</span>
      </div>
      <div class="stat-value" data-count="<?= $revenue ?>">$<?= number_format($revenue, 2) ?></div>
      <span class="stat-badge badge-up"><i class='bx bx-trending-up'></i> อัปเดตล่าสุด</span>
    </div>

    <div class="stat-card tilt-card" style="--accent:rgba(244,63,94,.1); --card-accent:rgba(244,63,94,.4); --card-shadow:rgba(244,63,94,.15);">
      <div class="stat-header">
        <div class="stat-icon" style="background:rgba(244,63,94,.15);color:#fb7185"><i class='bx bx-time'></i></div>
        <span class="stat-label">Pending Orders</span>
      </div>
      <div class="stat-value"><?= number_format($pending_orders) ?></div>
      <a href="orders.php" class="stat-badge badge-warn" style="text-decoration:none;"><i class='bx bx-right-arrow-alt'></i> ตรวจสอบ</a>
    </div>

    <div class="stat-card tilt-card" style="--accent:rgba(20,184,166,.1); --card-accent:rgba(20,184,166,.4); --card-shadow:rgba(20,184,166,.15);">
      <div class="stat-header">
        <div class="stat-icon" style="background:rgba(20,184,166,.15);color:#2dd4bf"><i class='bx bx-package'></i></div>
        <span class="stat-label">สต็อกสินค้า</span>
      </div>
      <div class="stat-value"><?= $stock_pct ?>%</div>
      <div class="prog-wrap">
        <div class="prog-label"><span>พร้อมส่ง <?= $stock_in ?> / <?= $stock_total ?> รายการ</span><?php if($low_stock>0): ?><span style="color:var(--amber)">⚠ <?= $low_stock ?> ใกล้หมด</span><?php endif; ?></div>
        <div class="prog-bg"><div class="prog-fill" data-width="<?= $stock_pct ?>"></div></div>
      </div>
    </div>

    <div class="stat-card tilt-card" style="--accent:rgba(245,158,11,.1); --card-accent:rgba(245,158,11,.4); --card-shadow:rgba(245,158,11,.15);">
      <div class="stat-header">
        <div class="stat-icon" style="background:rgba(245,158,11,.15);color:#fbbf24"><i class='bx bx-group'></i></div>
        <span class="stat-label">ลูกค้าทั้งหมด</span>
      </div>
      <div class="stat-value"><?= number_format($total_users) ?></div>
      <span class="stat-badge badge-info"><i class='bx bx-user-check'></i> ลงทะเบียนแล้ว</span>
    </div>
  </div>

  <div class="charts-row">
    <div class="chart-card tilt-card">
      <div class="card-title">📈 ยอดขาย 6 เดือนย้อนหลัง</div>
      <div class="card-sub">คำนวณจากออเดอร์ทั้งหมดในระบบ</div>
      <div id="lineChart"></div>
    </div>

    <div class="chart-card tilt-card">
      <div class="card-title">🧩 สัดส่วนหมวดหมู่</div>
      <div class="card-sub">จากสินค้าทั้งหมดในคลัง</div>
      <div id="catDonut"></div>
    </div>

    <div class="chart-card tilt-card">
      <div class="card-title">📊 สถานะคำสั่งซื้อ</div>
      <div class="card-sub">ภาพรวม Order ทั้งหมด</div>
      <div id="orderDonut"></div>
    </div>
  </div>

  <div class="bottom-row">

    <div class="chart-card tilt-card" style="animation-delay: .6s;">
      <div class="card-title">🏆 สินค้าราคาสูงสุด Top 5</div>
      <div class="card-sub">จัดอันดับจากฐานข้อมูล Products</div>
      <?php if(empty($top_products)): ?>
        <p style="color:var(--muted);font-size:13px;padding:20px 0;text-align:center;">ยังไม่มีข้อมูลสินค้า</p>
      <?php else: ?>
      <table class="data-table">
        <thead><tr><th>#</th><th>สินค้า</th><th style="text-align:right">หมวดหมู่</th><th style="text-align:right">ราคา</th></tr></thead>
        <tbody>
        <?php foreach($top_products as $i => $p): ?>
        <tr>
          <td><div class="rank <?= $i===0?'rank-1':'' ?>"><?= $i+1 ?></div></td>
          <td>
            <div class="prod-row">
              <div class="prod-img-placeholder">
                  <?php if(!empty($p['p_image_url'])): ?>
                      <img src="<?= str_starts_with($p['p_image_url'],'http') ? $p['p_image_url'] : '../'.$p['p_image_url'] ?>">
                  <?php else: ?>
                      <i class='bx bx-package'></i>
                  <?php endif; ?>
              </div>
              <div>
                <div class="prod-name"><?= htmlspecialchars($p['p_name']) ?></div>
                <div class="prod-sold">Stock: <?= number_format($p['p_stock'] ?? 0) ?> ชิ้น</div>
              </div>
            </div>
          </td>
          <td style="text-align:right;font-weight:600; font-size:11px; color:var(--muted);"><?= htmlspecialchars($p['cat_name'] ?? 'ไม่มีหมวดหมู่') ?></td>
          <td style="text-align:right;color:var(--green);font-weight:700">$<?= number_format($p['p_price'],2) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

    <div class="chart-card tilt-card" style="animation-delay: .7s;">
      <div class="card-title">🛒 คำสั่งซื้อล่าสุด</div>
      <div class="card-sub">5 รายการล่าสุดจากลูกค้า</div>
      <?php if(empty($recent_orders)): ?>
        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:40px 0; opacity: 0.5;">
            <i class='bx bx-receipt' style="font-size: 44px; margin-bottom: 12px; color: var(--purple-lt);"></i>
            <p style="color:var(--muted);font-size:13px;text-align:center;">ยังไม่มีคำสั่งซื้อ</p>
        </div>
      <?php else: ?>
        <table class="data-table">
          <thead><tr><th>Order ID</th><th>ลูกค้า</th><th style="text-align:right">ยอดรวม</th><th style="text-align:right">สถานะ</th></tr></thead>
          <tbody>
          <?php foreach($recent_orders as $ro): 
              $s = $ro['order_status'];
              $s_class = strtolower($s);
          ?>
          <tr>
            <td style="font-family:'JetBrains Mono',monospace; color:var(--purple-lt); font-weight:600; font-size:12px;">#<?= str_pad($ro['order_id'],5,'0',STR_PAD_LEFT) ?></td>
            <td>
                <div style="font-weight:600;"><?= htmlspecialchars($ro['username'] ?? 'Guest') ?></div>
                <div style="font-size:11px;color:var(--muted);"><?= date('d/m/Y H:i', strtotime($ro['created_at'])) ?></div>
            </td>
            <td style="text-align:right; font-weight:700; color:var(--green);">$<?= number_format($ro['total_amount'],2) ?></td>
            <td style="text-align:right;"><span class="status-pill status-<?= $s_class ?>"><?= $s ?></span></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

  </div>
</main>

<script>
// ==========================================
// 🌟 INTERACTIVE ANIMATIONS
// ==========================================

// ===== Mouse Glow Follow =====
const glow = document.getElementById('mouseGlow');
let mouseX = 0, mouseY = 0, glowX = 0, glowY = 0;

document.addEventListener('mousemove', (e) => {
  mouseX = e.clientX;
  mouseY = e.clientY;
  glow.classList.add('visible');
});
document.addEventListener('mouseleave', () => glow.classList.remove('visible'));

function animateGlow() {
  glowX += (mouseX - glowX) * 0.08;
  glowY += (mouseY - glowY) * 0.08;
  glow.style.left = glowX + 'px';
  glow.style.top = glowY + 'px';
  requestAnimationFrame(animateGlow);
}
animateGlow();

// ===== 3D Tilt Cards on Mouse Move =====
document.querySelectorAll('.tilt-card').forEach(card => {
  card.addEventListener('mousemove', (e) => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;
    const rotateX = ((y - centerY) / centerY) * -6;
    const rotateY = ((x - centerX) / centerX) * 6;
    
    card.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-4px)`;
  });

  card.addEventListener('mouseleave', () => {
    card.style.transform = 'perspective(800px) rotateX(0) rotateY(0) translateY(0)';
    card.style.transition = 'transform .5s cubic-bezier(.4,0,.2,1)';
  });

  card.addEventListener('mouseenter', () => {
    card.style.transition = 'transform .15s cubic-bezier(.4,0,.2,1)';
  });
});

// ===== Magnetic Buttons =====
document.querySelectorAll('.magnetic').forEach(btn => {
  btn.addEventListener('mousemove', (e) => {
    const rect = btn.getBoundingClientRect();
    const x = e.clientX - rect.left - rect.width / 2;
    const y = e.clientY - rect.top - rect.height / 2;
    btn.style.transform = `translate(${x * 0.3}px, ${y * 0.3}px)`;
  });
  btn.addEventListener('mouseleave', () => {
    btn.style.transform = 'translate(0,0)';
  });
});

// ===== Particle System (Canvas) =====
const canvas = document.getElementById('particleCanvas');
const ctx = canvas.getContext('2d');
let particles = [];
const sidebarW = 250;

function resizeCanvas() {
  canvas.width = window.innerWidth - sidebarW;
  canvas.height = window.innerHeight;
  canvas.style.left = sidebarW + 'px';
}
resizeCanvas();
window.addEventListener('resize', resizeCanvas);

class Particle {
  constructor(x, y) {
    this.x = x || Math.random() * canvas.width;
    this.y = y || Math.random() * canvas.height;
    this.size = Math.random() * 2 + 0.5;
    this.speedX = (Math.random() - 0.5) * 0.5;
    this.speedY = (Math.random() - 0.5) * 0.5;
    this.life = 1;
    this.decay = Math.random() * 0.005 + 0.002;
    this.hue = Math.random() > 0.5 ? 258 : 175; // purple or teal
  }
  update() {
    this.x += this.speedX;
    this.y += this.speedY;
    this.life -= this.decay;
    // Mouse attraction
    const dx = (mouseX - sidebarW) - this.x;
    const dy = mouseY - this.y;
    const dist = Math.sqrt(dx * dx + dy * dy);
    if (dist < 200) {
      this.speedX += dx * 0.00005;
      this.speedY += dy * 0.00005;
    }
  }
  draw() {
    ctx.globalAlpha = this.life * 0.5;
    ctx.fillStyle = `hsla(${this.hue}, 80%, 70%, ${this.life})`;
    ctx.beginPath();
    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
    ctx.fill();
  }
}

// Init particles
for (let i = 0; i < 50; i++) {
  particles.push(new Particle());
}

// Spawn particles on mouse move
let particleThrottle = 0;
document.addEventListener('mousemove', () => {
  particleThrottle++;
  if (particleThrottle % 4 === 0 && particles.length < 120) {
    particles.push(new Particle(mouseX - sidebarW, mouseY));
  }
});

function animateParticles() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  
  particles = particles.filter(p => p.life > 0);
  
  // Maintain minimum particles
  while (particles.length < 30) {
    particles.push(new Particle());
  }

  particles.forEach(p => {
    p.update();
    p.draw();
  });

  // Draw connections between nearby particles
  ctx.globalAlpha = 0.08;
  ctx.strokeStyle = '#7c5cfc';
  ctx.lineWidth = 0.5;
  for (let i = 0; i < particles.length; i++) {
    for (let j = i + 1; j < particles.length; j++) {
      const dx = particles[i].x - particles[j].x;
      const dy = particles[i].y - particles[j].y;
      const dist = Math.sqrt(dx * dx + dy * dy);
      if (dist < 100) {
        ctx.globalAlpha = 0.06 * (1 - dist / 100) * particles[i].life * particles[j].life;
        ctx.beginPath();
        ctx.moveTo(particles[i].x, particles[i].y);
        ctx.lineTo(particles[j].x, particles[j].y);
        ctx.stroke();
      }
    }
  }
  
  requestAnimationFrame(animateParticles);
}
animateParticles();

// ===== Animate Progress Bar on Scroll =====
const progFills = document.querySelectorAll('.prog-fill');
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const bar = entry.target;
      setTimeout(() => {
        bar.style.width = bar.dataset.width + '%';
      }, 400);
    }
  });
}, { threshold: 0.5 });
progFills.forEach(bar => observer.observe(bar));

// ===== Charts =====
const dark = { foreColor:'#94a3b8', grid:'rgba(255,255,255,.04)', tooltip:{theme:'dark'} };

// Line Chart
new ApexCharts(document.querySelector('#lineChart'), {
  series: [{ name:'ยอดขาย ($)', data: <?= $chart_data ?> }],
  labels: <?= $chart_labels ?>,
  chart:  { type:'area', height:200, toolbar:{show:false}, foreColor:dark.foreColor, background:'transparent', animations: { enabled:true, speed:1200, animateGradually: { enabled:true, delay:200 }, dynamicAnimation: { enabled:true, speed:500 } } },
  theme:  { mode:'dark' },
  colors: ['#7c5cfc'],
  fill:   { type:'gradient', gradient:{ shadeIntensity:.5, opacityFrom:.45, opacityTo:0, stops:[0,90,100] } },
  stroke: { curve:'smooth', width:2.5 },
  grid:   { borderColor: dark.grid, padding:{left:0,right:0} },
  xaxis:  { axisBorder:{show:false}, axisTicks:{show:false} },
  yaxis:  { labels:{ formatter: v => '$'+Number(v).toLocaleString() } },
  tooltip:{ y:{ formatter: v => '$'+Number(v).toLocaleString() } },
  dataLabels: { enabled:false },
}).render();

// Category Donut
new ApexCharts(document.querySelector('#catDonut'), {
  series: <?= $cat_data_json ?>,
  labels: <?= $cat_labels_json ?>,
  chart:  { type:'donut', height:200, foreColor:dark.foreColor, background:'transparent', animations: { enabled:true, speed:1500, animateGradually: { enabled:true, delay:250 } } },
  theme:  { mode:'dark' },
  colors: ['#7c5cfc','#14b8a6','#f59e0b','#f43f5e','#3b82f6'],
  stroke: { show:false },
  plotOptions: { pie:{ donut:{ size:'72%', labels: { show:true, name:{fontSize:'13px',color:'#94a3b8'}, value:{fontSize:'20px',fontWeight:700,color:'#f1f5f9'}, total: { show:true, label:'ทั้งหมด', color:'#94a3b8', fontSize:'12px' } } } } },
  legend: { position:'bottom', fontSize:'12px' },
  dataLabels: { enabled:false },
  tooltip: { theme:'dark' },
}).render();

// Order Status Donut
new ApexCharts(document.querySelector('#orderDonut'), {
  series: <?= $os_data ?>,
  labels: <?= $os_labels ?>,
  chart:  { type:'donut', height:200, foreColor:dark.foreColor, background:'transparent', animations: { enabled:true, speed:1500, animateGradually: { enabled:true, delay:250 } } },
  theme:  { mode:'dark' },
  colors: ['#10b981','#f59e0b','#7c5cfc','#14b8a6','#f43f5e'],
  stroke: { show:false },
  plotOptions: { pie:{ donut:{ size:'72%', labels: { show:true, name:{fontSize:'13px',color:'#94a3b8'}, value:{fontSize:'20px',fontWeight:700,color:'#f1f5f9'}, total: { show:true, label:'Orders', color:'#94a3b8', fontSize:'12px' } } } } },
  legend: { position:'bottom', fontSize:'12px' },
  dataLabels: { enabled:false },
  tooltip: { theme:'dark' },
}).render();
</script>
</body>
</html>