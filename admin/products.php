<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php"); exit();
}
require_once '../config/db.php';

$msg = ''; $msg_type = '';
$pending_orders = (int)mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM orders WHERE order_status='Pending'"))['0'];

// ===== Handle Actions =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $cat_id = (int)$_POST['cat_id'];
        $brand = mysqli_real_escape_string($conn, $_POST['p_brand']);
        $name = mysqli_real_escape_string($conn, $_POST['p_name']);
        $price = (float)$_POST['p_price'];
        $stock = (int)$_POST['p_stock'];
        $tag = mysqli_real_escape_string($conn, $_POST['p_tag']);
        $img_url = mysqli_real_escape_string($conn, $_POST['p_image_url']);
        $desc = mysqli_real_escape_string($conn, $_POST['p_desc'] ?? '');

        // Handle file upload
        if (!empty($_FILES['p_image_file']['name'])) {
            $upload_dir = '../imgs/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $ext = pathinfo($_FILES['p_image_file']['name'], PATHINFO_EXTENSION);
            $filename = 'prod_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['p_image_file']['tmp_name'], $upload_dir . $filename);
            $img_url = 'imgs/products/' . $filename;
        }

        $desc_sql = $desc ? "'$desc'" : "NULL";
        mysqli_query($conn, "INSERT INTO products (cat_id, p_brand, p_name, p_price, p_stock, p_desc, p_image_url, p_tag) VALUES ($cat_id, '$brand', '$name', $price, $stock, $desc_sql, '$img_url', " . ($tag ? "'$tag'" : "NULL") . ")");
        $msg = "เพิ่มสินค้า '$name' สำเร็จ!";
        $msg_type = 'success';
    }

    if ($action === 'edit') {
        $id = (int)$_POST['product_id'];
        $cat_id = (int)$_POST['cat_id'];
        $brand = mysqli_real_escape_string($conn, $_POST['p_brand']);
        $name = mysqli_real_escape_string($conn, $_POST['p_name']);
        $price = (float)$_POST['p_price'];
        $stock = (int)$_POST['p_stock'];
        $tag = mysqli_real_escape_string($conn, $_POST['p_tag']);
        $img_url = mysqli_real_escape_string($conn, $_POST['p_image_url']);
        $desc = mysqli_real_escape_string($conn, $_POST['p_desc'] ?? '');

        if (!empty($_FILES['p_image_file']['name'])) {
            $upload_dir = '../imgs/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $ext = pathinfo($_FILES['p_image_file']['name'], PATHINFO_EXTENSION);
            $filename = 'prod_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['p_image_file']['tmp_name'], $upload_dir . $filename);
            $img_url = 'imgs/products/' . $filename;
        }

        $tag_sql = $tag ? "'$tag'" : "NULL";
        $desc_sql = $desc ? "'$desc'" : "NULL";
        mysqli_query($conn, "UPDATE products SET cat_id=$cat_id, p_brand='$brand', p_name='$name', p_price=$price, p_stock=$stock, p_desc=$desc_sql, p_image_url='$img_url', p_tag=$tag_sql WHERE id=$id");
        $msg = "แก้ไขสินค้า '$name' สำเร็จ!";
        $msg_type = 'success';
    }

    if ($action === 'delete') {
        $id = (int)$_POST['product_id'];
        mysqli_query($conn, "DELETE FROM products WHERE id=$id");
        $msg = "ลบสินค้าสำเร็จ!";
        $msg_type = 'success';
    }
}

// Fetch categories for dropdown
$categories = [];
$qc = mysqli_query($conn, "SELECT * FROM categories ORDER BY cat_name");
if ($qc) while($r = mysqli_fetch_assoc($qc)) $categories[] = $r;

// Fetch products
$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$cat_filter = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$where = "1=1";
if ($search) $where .= " AND (p.p_name LIKE '%$search%' OR p.p_brand LIKE '%$search%')";
if ($cat_filter) $where .= " AND p.cat_id = $cat_filter";

$products = [];
$qp = mysqli_query($conn, "SELECT p.*, c.cat_name FROM products p LEFT JOIN categories c ON p.cat_id = c.cat_id WHERE $where ORDER BY p.id DESC");
if ($qp) while($r = mysqli_fetch_assoc($qp)) $products[] = $r;
$total = count($products);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TechGear Admin — Products</title>
<?php include 'admin-header.php'; ?>
<style>
.prod-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-top:16px;}
.prod-card{background:var(--bg);border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:all .3s cubic-bezier(.4,0,.2,1);animation:fadeSlideUp .5s both;}
.prod-card:hover{border-color:rgba(124,92,252,.3);transform:translateY(-4px);box-shadow:0 12px 40px rgba(0,0,0,.3);}
.prod-card-img{width:100%;height:180px;object-fit:cover;background:var(--bg3);display:flex;align-items:center;justify-content:center;}
.prod-card-img img{width:100%;height:100%;object-fit:cover;}
.prod-card-body{padding:16px;}
.prod-card-brand{font-size:10px;font-weight:700;color:var(--purple-lt);letter-spacing:.08em;text-transform:uppercase;}
.prod-card-name{font-size:14px;font-weight:700;margin:4px 0 8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.prod-card-meta{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;}
.prod-card-price{font-size:18px;font-weight:800;color:var(--green);}
.prod-card-stock{font-size:11px;font-weight:600;}
.prod-card-actions{display:flex;gap:6px;}
.tag-badge{position:absolute;top:12px;right:12px;padding:4px 10px;border-radius:8px;font-size:10px;font-weight:700;background:rgba(124,92,252,.85);color:#fff;backdrop-filter:blur(4px);}
.prod-card-img-wrap{position:relative;}

/* View Toggle */
.view-toggle{display:flex;gap:4px;}
.view-toggle button{width:34px;height:34px;border-radius:8px;background:var(--bg);border:1px solid var(--border);color:var(--muted);cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;}
.view-toggle button.active{background:rgba(124,92,252,.15);color:var(--purple-lt);border-color:rgba(124,92,252,.3);}
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
            <h1>📦 Products Management</h1>
            <p>จัดการสินค้า เพิ่ม แก้ไข ลบสินค้า (ทั้งหมด <?= $total ?> รายการ)</p>
        </div>
        <div class="topbar-right">
            <button class="btn btn-primary" onclick="openModal('addModal')"><i class='bx bx-plus'></i> เพิ่มสินค้าใหม่</button>
        </div>
    </div>

    <div class="toolbar">
        <div class="search-box">
            <i class='bx bx-search'></i>
            <input type="text" placeholder="ค้นหาสินค้า..." value="<?= htmlspecialchars($search) ?>" onkeyup="if(event.key==='Enter') location.href='products.php?q='+this.value">
        </div>
        <select class="form-control" style="width:180px;" onchange="location.href='products.php?cat='+this.value">
            <option value="">หมวดหมู่ทั้งหมด</option>
            <?php foreach($categories as $c): ?>
                <option value="<?= $c['cat_id'] ?>" <?= $cat_filter==$c['cat_id']?'selected':'' ?>><?= htmlspecialchars($c['cat_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if(empty($products)): ?>
        <div class="admin-content-card">
            <div class="empty-state">
                <i class='bx bx-package'></i>
                <h3>ไม่พบสินค้า</h3>
                <p>คลิกปุ่ม "เพิ่มสินค้าใหม่" เพื่อเริ่มต้น</p>
            </div>
        </div>
    <?php else: ?>
    <div class="prod-grid">
        <?php foreach($products as $i => $p): ?>
        <div class="prod-card" style="animation-delay:<?= $i * 0.05 ?>s;">
            <div class="prod-card-img-wrap">
                <div class="prod-card-img">
                    <?php if(!empty($p['p_image_url'])): ?>
                        <img src="<?= str_starts_with($p['p_image_url'],'http') ? $p['p_image_url'] : '../'.$p['p_image_url'] ?>" alt="<?= htmlspecialchars($p['p_name']) ?>">
                    <?php else: ?>
                        <i class='bx bx-image' style="font-size:40px;color:var(--muted);"></i>
                    <?php endif; ?>
                </div>
                <?php if($p['p_tag']): ?>
                    <span class="tag-badge"><?= htmlspecialchars($p['p_tag']) ?></span>
                <?php endif; ?>
            </div>
            <div class="prod-card-body">
                <div class="prod-card-brand"><?= htmlspecialchars($p['p_brand']) ?></div>
                <div class="prod-card-name" title="<?= htmlspecialchars($p['p_name']) ?>"><?= htmlspecialchars($p['p_name']) ?></div>
                <div style="font-size:11px;color:var(--muted);margin-bottom:8px;"><i class='bx bx-category'></i> <?= htmlspecialchars($p['cat_name'] ?? 'ไม่มีหมวดหมู่') ?></div>
                <div class="prod-card-meta">
                    <span class="prod-card-price">$<?= number_format($p['p_price'],2) ?></span>
                    <?php 
                    $stk = $p['p_stock'] ?? 0;
                    if($stk <= 0) echo '<span class="pill pill-out">หมด</span>';
                    elseif($stk <= 5) echo '<span class="pill pill-low-stock">เหลือ '.$stk.'</span>';
                    else echo '<span class="pill pill-in-stock">สต็อก '.$stk.'</span>';
                    ?>
                </div>
                <div class="prod-card-actions">
                    <button class="btn btn-outline btn-sm" style="flex:1;" onclick='openEditModal(<?= json_encode($p) ?>)'><i class='bx bx-edit'></i> แก้ไข</button>
                    <form method="POST" style="flex:0;" onsubmit="return confirm('ยืนยันลบสินค้านี้?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <button class="btn btn-danger btn-sm"><i class='bx bx-trash'></i></button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<!-- Add Product Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <h2><i class='bx bx-plus-circle' style="color:var(--purple-lt);"></i> เพิ่มสินค้าใหม่</h2>
        <div class="modal-sub">กรอกข้อมูลสินค้าด้านล่าง</div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group"><label>ชื่อสินค้า *</label><input type="text" name="p_name" class="form-control" required></div>
                <div class="form-group"><label>แบรนด์ *</label><input type="text" name="p_brand" class="form-control" required></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                <div class="form-group"><label>ราคา ($) *</label><input type="number" step="0.01" name="p_price" class="form-control" required></div>
                <div class="form-group"><label>สต็อก *</label><input type="number" name="p_stock" class="form-control" value="10" required></div>
                <div class="form-group"><label>Tag (ป้าย)</label><input type="text" name="p_tag" class="form-control" placeholder="NEW, -15%"></div>
            </div>
            <div class="form-group">
                <label>รายละเอียดสินค้า (Description)</label>
                <textarea name="p_desc" class="form-control" rows="3" placeholder="คำอธิบายสินค้า"></textarea>
            </div>
            <div class="form-group">
                <label>หมวดหมู่ *</label>
                <select name="cat_id" class="form-control" required>
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['cat_id'] ?>"><?= htmlspecialchars($c['cat_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>URL รูปภาพ</label><input type="url" name="p_image_url" class="form-control" placeholder="https://..."></div>
            <div class="form-group"><label>หรือ อัปโหลดรูปภาพ</label><input type="file" name="p_image_file" class="form-control" accept="image/*"></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">ยกเลิก</button>
                <button type="submit" class="btn btn-primary"><i class='bx bx-check'></i> เพิ่มสินค้า</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <h2><i class='bx bx-edit' style="color:var(--amber);"></i> แก้ไขสินค้า</h2>
        <div class="modal-sub">แก้ไขข้อมูลสินค้า</div>
        <form method="POST" enctype="multipart/form-data" id="editForm">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="product_id" id="edit_id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group"><label>ชื่อสินค้า *</label><input type="text" name="p_name" id="edit_name" class="form-control" required></div>
                <div class="form-group"><label>แบรนด์ *</label><input type="text" name="p_brand" id="edit_brand" class="form-control" required></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                <div class="form-group"><label>ราคา ($) *</label><input type="number" step="0.01" name="p_price" id="edit_price" class="form-control" required></div>
                <div class="form-group"><label>สต็อก *</label><input type="number" name="p_stock" id="edit_stock" class="form-control" required></div>
                <div class="form-group"><label>Tag</label><input type="text" name="p_tag" id="edit_tag" class="form-control"></div>
            </div>
            <div class="form-group">
                <label>รายละเอียดสินค้า (Description)</label>
                <textarea name="p_desc" id="edit_desc" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>หมวดหมู่ *</label>
                <select name="cat_id" id="edit_cat" class="form-control" required>
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['cat_id'] ?>"><?= htmlspecialchars($c['cat_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>URL รูปภาพ</label><input type="url" name="p_image_url" id="edit_img" class="form-control"></div>
            <div class="form-group"><label>หรือ อัปโหลดรูปใหม่</label><input type="file" name="p_image_file" class="form-control" accept="image/*"></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">ยกเลิก</button>
                <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

function openEditModal(p) {
    document.getElementById('edit_id').value = p.id;
    document.getElementById('edit_name').value = p.p_name;
    document.getElementById('edit_brand').value = p.p_brand;
    document.getElementById('edit_price').value = p.p_price;
    document.getElementById('edit_stock').value = p.p_stock || 0;
    document.getElementById('edit_tag').value = p.p_tag || '';
    document.getElementById('edit_desc').value = p.p_desc || '';
    document.getElementById('edit_cat').value = p.cat_id;
    document.getElementById('edit_img').value = p.p_image_url || '';
    openModal('editModal');
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('active'); });
});
</script>
</body>
</html>
