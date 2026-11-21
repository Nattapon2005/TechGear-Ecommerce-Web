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
        $name = mysqli_real_escape_string($conn, $_POST['cat_name']);
        $img = mysqli_real_escape_string($conn, $_POST['cat_image_url']);
        $icon = !empty($_POST['cat_icon']) ? mysqli_real_escape_string($conn, $_POST['cat_icon']) : 'bx-category';
        
        if (!empty($_FILES['cat_image_file']['name'])) {
            $upload_dir = '../imgs/categories/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $ext = pathinfo($_FILES['cat_image_file']['name'], PATHINFO_EXTENSION);
            $filename = 'cat_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['cat_image_file']['tmp_name'], $upload_dir . $filename);
            $img = 'imgs/categories/' . $filename;
        }

        mysqli_query($conn, "INSERT INTO categories (cat_name, cat_image_url, cat_icon) VALUES ('$name', '$img', '$icon')");
        $msg = "เพิ่มหมวดหมู่ '$name' สำเร็จ!";
        $msg_type = 'success';
    }

    if ($action === 'edit') {
        $id = (int)$_POST['cat_id'];
        $name = mysqli_real_escape_string($conn, $_POST['cat_name']);
        $img = mysqli_real_escape_string($conn, $_POST['cat_image_url']);
        $icon = !empty($_POST['cat_icon']) ? mysqli_real_escape_string($conn, $_POST['cat_icon']) : 'bx-category';

        if (!empty($_FILES['cat_image_file']['name'])) {
            $upload_dir = '../imgs/categories/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $ext = pathinfo($_FILES['cat_image_file']['name'], PATHINFO_EXTENSION);
            $filename = 'cat_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['cat_image_file']['tmp_name'], $upload_dir . $filename);
            $img = 'imgs/categories/' . $filename;
        }

        mysqli_query($conn, "UPDATE categories SET cat_name='$name', cat_image_url='$img', cat_icon='$icon' WHERE cat_id=$id");
        $msg = "แก้ไขหมวดหมู่สำเร็จ!";
        $msg_type = 'success';
    }

    if ($action === 'delete') {
        $id = (int)$_POST['cat_id'];
        // Check if category has products
        $cnt = (int)mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM products WHERE cat_id=$id"))['0'];
        if ($cnt > 0) {
            $msg = "ไม่สามารถลบได้! มีสินค้า $cnt รายการในหมวดหมู่นี้";
            $msg_type = 'error';
        } else {
            mysqli_query($conn, "DELETE FROM categories WHERE cat_id=$id");
            $msg = "ลบหมวดหมู่สำเร็จ!";
            $msg_type = 'success';
        }
    }
}

// Fetch categories with product counts
$categories = [];
$qc = mysqli_query($conn, "SELECT c.*, COUNT(p.id) as prod_count FROM categories c LEFT JOIN products p ON c.cat_id = p.cat_id GROUP BY c.cat_id ORDER BY c.cat_id");
if ($qc) while($r = mysqli_fetch_assoc($qc)) $categories[] = $r;
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TechGear Admin — Categories</title>
<?php include 'admin-header.php'; ?>
<style>
.cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;margin-top:16px;}
.cat-card{background:var(--bg2);border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:all .3s cubic-bezier(.4,0,.2,1);animation:fadeSlideUp .5s both;display:flex;align-items:stretch;height:115px;}
.cat-card:hover{border-color:rgba(201, 172, 130,.4);transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.4);}
.cat-card-img{width:115px;height:115px;flex-shrink:0;background:var(--bg3);overflow:hidden;position:relative;}
.cat-card-img img{width:100%;height:100%;object-fit:cover;display:block;border-radius:0 !important;}
.cat-card-body{padding:14px 18px;flex:1;display:flex;flex-direction:column;justify-content:center;min-width:0;}
.cat-card-name{font-size:15px;font-weight:700;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.cat-card-count{font-size:12px;color:var(--muted);margin-bottom:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
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
            <h1>🏷️ Categories Management</h1>
            <p>จัดการหมวดหมู่สินค้า (ทั้งหมด <?= count($categories) ?> หมวดหมู่)</p>
        </div>
        <div class="topbar-right">
            <button class="btn btn-primary" onclick="openModal('addCatModal')"><i class='bx bx-plus'></i> เพิ่มหมวดหมู่</button>
        </div>
    </div>

    <?php if(empty($categories)): ?>
        <div class="admin-content-card">
            <div class="empty-state">
                <i class='bx bx-category'></i>
                <h3>ยังไม่มีหมวดหมู่</h3>
                <p>คลิกปุ่ม "เพิ่มหมวดหมู่" เพื่อเริ่มต้น</p>
            </div>
        </div>
    <?php else: ?>
    <div class="cat-grid">
        <?php foreach($categories as $i => $c): ?>
        <div class="cat-card" style="animation-delay:<?= $i * 0.08 ?>s;">
            <div class="cat-card-img">
                <?php 
                $cat_img = fixImageUrl($c['cat_image_url'] ?? '');
                $cat_src = str_starts_with($cat_img, 'http') ? $cat_img : '../' . $cat_img;
                ?>
                <img src="<?= htmlspecialchars($cat_src) ?>" alt="<?= htmlspecialchars($c['cat_name']) ?>" onerror="this.src='https://images.unsplash.com/photo-1612287230202-1ff1d85d1bdf?w=800&q=80'">
            </div>
            <div class="cat-card-body">
                <div class="cat-card-name">
                    <i class='bx <?= htmlspecialchars($c['cat_icon'] ?? 'bx-category') ?>' style="color:var(--purple-lt);margin-right:6px;font-size:18px;vertical-align:middle;"></i>
                    <?= htmlspecialchars($c['cat_name']) ?>
                </div>
                <div class="cat-card-count"><i class='bx bx-package'></i> <?= $c['prod_count'] ?> สินค้า &bull; <code><?= htmlspecialchars($c['cat_icon'] ?? 'bx-category') ?></code></div>
                <div style="display:flex;gap:6px;">
                    <button class="btn btn-outline btn-xs" onclick='openEditCat(<?= json_encode($c) ?>)'><i class='bx bx-edit'></i> แก้ไข</button>
                    <form method="POST" onsubmit="return confirm('ยืนยันลบหมวดหมู่นี้?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="cat_id" value="<?= $c['cat_id'] ?>">
                        <button class="btn btn-danger btn-xs"><i class='bx bx-trash'></i></button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<!-- Add Category Modal -->
<div class="modal-overlay" id="addCatModal">
    <div class="modal">
        <h2><i class='bx bx-plus-circle' style="color:var(--purple-lt);"></i> เพิ่มหมวดหมู่ใหม่</h2>
        <div class="modal-sub">กรอกข้อมูลหมวดหมู่</div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="form-group"><label>ชื่อหมวดหมู่ *</label><input type="text" name="cat_name" class="form-control" placeholder="เช่น Keyboards, Mice" required></div>
            <div class="form-group"><label>ไอคอน Boxicons (เช่น bx-keyboard, bx-mouse, bx-devices)</label><input type="text" name="cat_icon" class="form-control" placeholder="bx-category" value="bx-category"></div>
            <div class="form-group"><label>URL รูปภาพ</label><input type="url" name="cat_image_url" class="form-control" placeholder="https://..."></div>
            <div class="form-group"><label>หรือ อัปโหลดรูปภาพ</label><input type="file" name="cat_image_file" class="form-control" accept="image/*"></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('addCatModal')">ยกเลิก</button>
                <button type="submit" class="btn btn-primary"><i class='bx bx-check'></i> เพิ่มหมวดหมู่</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal-overlay" id="editCatModal">
    <div class="modal">
        <h2><i class='bx bx-edit' style="color:var(--amber);"></i> แก้ไขหมวดหมู่</h2>
        <div class="modal-sub">แก้ไขข้อมูลหมวดหมู่</div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="cat_id" id="ecat_id">
            <div class="form-group"><label>ชื่อหมวดหมู่ *</label><input type="text" name="cat_name" id="ecat_name" class="form-control" required></div>
            <div class="form-group"><label>ไอคอน Boxicons (เช่น bx-keyboard, bx-mouse, bx-devices)</label><input type="text" name="cat_icon" id="ecat_icon" class="form-control" placeholder="bx-category"></div>
            <div class="form-group"><label>URL รูปภาพ</label><input type="url" name="cat_image_url" id="ecat_img" class="form-control"></div>
            <div class="form-group"><label>หรือ อัปโหลดรูปใหม่</label><input type="file" name="cat_image_file" class="form-control" accept="image/*"></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('editCatModal')">ยกเลิก</button>
                <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

function openEditCat(c) {
    document.getElementById('ecat_id').value = c.cat_id;
    document.getElementById('ecat_name').value = c.cat_name;
    document.getElementById('ecat_icon').value = c.cat_icon || 'bx-category';
    document.getElementById('ecat_img').value = c.cat_image_url || '';
    openModal('editCatModal');
}

document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('active'); });
});
</script>
</body>
</html>
