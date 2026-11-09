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
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);

        // Check duplicate email
        $check = mysqli_query($conn, "SELECT user_id FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $msg = "อีเมลนี้ถูกใช้แล้ว!";
            $msg_type = 'error';
        } else {
            mysqli_query($conn, "INSERT INTO users (username, email, password, phone, address, role) VALUES ('$username', '$email', '$password', '$phone', '$address', '$role')");
            $msg = "เพิ่มผู้ใช้ '$username' สำเร็จ!";
            $msg_type = 'success';
        }
    }

    if ($action === 'edit') {
        $id = (int)$_POST['user_id'];
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);

        $pwd_sql = '';
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $pwd_sql = ", password='$password'";
        }

        mysqli_query($conn, "UPDATE users SET username='$username', email='$email', phone='$phone', address='$address', role='$role' $pwd_sql WHERE user_id=$id");
        $msg = "แก้ไขผู้ใช้สำเร็จ!";
        $msg_type = 'success';
    }

    if ($action === 'delete') {
        $id = (int)$_POST['user_id'];
        // Don't allow deleting yourself
        $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE user_id=$id"));
        if ($check && $check['role'] === 'admin') {
            $msg = "ไม่สามารถลบบัญชี Admin ได้!";
            $msg_type = 'error';
        } else {
            mysqli_query($conn, "DELETE FROM users WHERE user_id=$id");
            $msg = "ลบผู้ใช้สำเร็จ!";
            $msg_type = 'success';
        }
    }
}

// Fetch users
$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$role_filter = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : '';
$where = "1=1";
if ($search) $where .= " AND (username LIKE '%$search%' OR email LIKE '%$search%')";
if ($role_filter) $where .= " AND role='$role_filter'";

$users = [];
$qu = mysqli_query($conn, "SELECT * FROM users WHERE $where ORDER BY created_at DESC");
if ($qu) while($r = mysqli_fetch_assoc($qu)) $users[] = $r;

$cnt_all = (int)mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM users"))['0'];
$cnt_admin = (int)mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='admin'"))['0'];
$cnt_cust = (int)mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='customer'"))['0'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TechGear Admin — Users</title>
<?php include 'admin-header.php'; ?>
<style>
.user-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;}
.user-stat{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:18px;text-align:center;transition:all .3s;animation:fadeSlideUp .5s both;}
.user-stat:hover{border-color:rgba(201, 172, 130,.3);transform:translateY(-2px);}
.user-stat-value{font-size:28px;font-weight:800;margin-bottom:4px;}
.user-stat-label{font-size:12px;color:var(--muted);font-weight:600;}

.user-avatar{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--purple),#8a6f4d);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:#17130d;flex-shrink:0;transition:transform .3s;}
.admin-table tbody tr:hover .user-avatar{transform:scale(1.15) rotate(-5deg);}
.user-avatar img{width:100%;height:100%;border-radius:50%;object-fit:cover;}

.role-tabs{display:flex;gap:8px;}
.role-tab{padding:7px 16px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;color:var(--muted);border:1px solid var(--border);transition:all .25s;}
.role-tab:hover{border-color:var(--purple);color:var(--purple-lt);}
.role-tab.active{background:rgba(201, 172, 130,.15);color:var(--purple-lt);border-color:rgba(201, 172, 130,.3);}
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
            <h1>👥 Users Management</h1>
            <p>จัดการผู้ใช้งาน เพิ่ม แก้ไข ลบบัญชี</p>
        </div>
        <div class="topbar-right">
            <button class="btn btn-primary" onclick="openModal('addUserModal')"><i class='bx bx-user-plus'></i> เพิ่มผู้ใช้</button>
        </div>
    </div>

    <div class="user-stats">
        <div class="user-stat" style="animation-delay:.1s;">
            <div class="user-stat-value" style="color:var(--purple-lt);"><?= $cnt_all ?></div>
            <div class="user-stat-label">ผู้ใช้ทั้งหมด</div>
        </div>
        <div class="user-stat" style="animation-delay:.2s;">
            <div class="user-stat-value" style="color:var(--red);"><?= $cnt_admin ?></div>
            <div class="user-stat-label">Admins</div>
        </div>
        <div class="user-stat" style="animation-delay:.3s;">
            <div class="user-stat-value" style="color:var(--green);"><?= $cnt_cust ?></div>
            <div class="user-stat-label">Customers</div>
        </div>
    </div>

    <div class="toolbar">
        <div class="search-box">
            <i class='bx bx-search'></i>
            <input type="text" placeholder="ค้นหาชื่อผู้ใช้ หรืออีเมล..." value="<?= htmlspecialchars($search) ?>" onkeyup="if(event.key==='Enter') location.href='users.php?q='+this.value">
        </div>
        <div class="role-tabs">
            <a href="users.php" class="role-tab <?= !$role_filter?'active':'' ?>">ทั้งหมด</a>
            <a href="users.php?role=admin" class="role-tab <?= $role_filter==='admin'?'active':'' ?>">Admin</a>
            <a href="users.php?role=customer" class="role-tab <?= $role_filter==='customer'?'active':'' ?>">Customer</a>
        </div>
    </div>

    <div class="admin-content-card">
        <?php if(empty($users)): ?>
            <div class="empty-state">
                <i class='bx bx-group'></i>
                <h3>ไม่พบผู้ใช้</h3>
                <p>ยังไม่มีผู้ใช้ที่ตรงกับเงื่อนไข</p>
            </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ผู้ใช้</th>
                    <th>อีเมล</th>
                    <th>โทรศัพท์</th>
                    <th>สิทธิ์</th>
                    <th>สมัครเมื่อ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($users as $u): ?>
                <tr>
                    <td style="font-weight:600;color:var(--muted);font-size:12px;">#<?= $u['user_id'] ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="user-avatar">
                                <?php if(!empty($u['profile_img'])): ?>
                                    <img src="../<?= htmlspecialchars($u['profile_img']) ?>">
                                <?php else: ?>
                                    <?= strtoupper(substr($u['username'],0,1)) ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div style="font-weight:600;"><?= htmlspecialchars($u['username']) ?></div>
                                <?php if(!empty($u['address'])): ?>
                                    <div style="font-size:11px;color:var(--muted);max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($u['address']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:12px;"><?= htmlspecialchars($u['email']) ?></td>
                    <td style="font-size:12px;color:var(--muted);"><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                    <td><span class="pill pill-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td style="font-size:12px;color:var(--muted);"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <button class="btn btn-outline btn-xs" onclick='openEditUser(<?= json_encode($u) ?>)'><i class='bx bx-edit'></i></button>
                            <?php if($u['role'] !== 'admin'): ?>
                            <form method="POST" onsubmit="return confirm('ยืนยันลบผู้ใช้นี้? ข้อมูลทั้งหมดจะถูกลบ')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                <button class="btn btn-danger btn-xs"><i class='bx bx-trash'></i></button>
                            </form>
                            <?php endif; ?>
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

<!-- Add User Modal -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal">
        <h2><i class='bx bx-user-plus' style="color:var(--purple-lt);"></i> เพิ่มผู้ใช้ใหม่</h2>
        <div class="modal-sub">กรอกข้อมูลผู้ใช้</div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group"><label>ชื่อผู้ใช้ *</label><input type="text" name="username" class="form-control" required></div>
                <div class="form-group"><label>อีเมล *</label><input type="email" name="email" class="form-control" required></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group"><label>รหัสผ่าน *</label><input type="password" name="password" class="form-control" required minlength="6"></div>
                <div class="form-group"><label>เบอร์โทร</label><input type="text" name="phone" class="form-control"></div>
            </div>
            <div class="form-group"><label>ที่อยู่</label><textarea name="address" class="form-control" rows="2"></textarea></div>
            <div class="form-group">
                <label>สิทธิ์ *</label>
                <select name="role" class="form-control" required>
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('addUserModal')">ยกเลิก</button>
                <button type="submit" class="btn btn-primary"><i class='bx bx-check'></i> เพิ่มผู้ใช้</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal">
        <h2><i class='bx bx-edit' style="color:var(--amber);"></i> แก้ไขผู้ใช้</h2>
        <div class="modal-sub">แก้ไขข้อมูลผู้ใช้ (เว้นรหัสผ่านว่างหากไม่ต้องการเปลี่ยน)</div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="user_id" id="eu_id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group"><label>ชื่อผู้ใช้ *</label><input type="text" name="username" id="eu_name" class="form-control" required></div>
                <div class="form-group"><label>อีเมล *</label><input type="email" name="email" id="eu_email" class="form-control" required></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group"><label>รหัสผ่านใหม่</label><input type="password" name="password" class="form-control" placeholder="เว้นว่างหากไม่เปลี่ยน"></div>
                <div class="form-group"><label>เบอร์โทร</label><input type="text" name="phone" id="eu_phone" class="form-control"></div>
            </div>
            <div class="form-group"><label>ที่อยู่</label><textarea name="address" id="eu_address" class="form-control" rows="2"></textarea></div>
            <div class="form-group">
                <label>สิทธิ์ *</label>
                <select name="role" id="eu_role" class="form-control" required>
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('editUserModal')">ยกเลิก</button>
                <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

function openEditUser(u) {
    document.getElementById('eu_id').value = u.user_id;
    document.getElementById('eu_name').value = u.username;
    document.getElementById('eu_email').value = u.email;
    document.getElementById('eu_phone').value = u.phone || '';
    document.getElementById('eu_address').value = u.address || '';
    document.getElementById('eu_role').value = u.role;
    openModal('editUserModal');
}

document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('active'); });
});
</script>
</body>
</html>
