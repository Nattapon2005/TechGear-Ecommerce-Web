<?php
include 'config/db.php';
include 'page/navbar.php';

// 1. ตรวจสอบสิทธิ์ (ต้องล็อกอินเท่านั้น)
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";

// 2. รับข้อมูลที่ส่งมาจากการกด Save Changes
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = trim($_POST['username']);
    $new_phone = trim($_POST['phone']);
    $new_address = trim($_POST['address']);

    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        $target_dir = "imgs/profiles/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = $_FILES["profile_img"]["name"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_filename = "user_" . $user_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_filename;

        $allowed = array("jpg", "jpeg", "png", "webp");
        if (in_array($file_ext, $allowed)) {
            if (move_uploaded_file($_FILES["profile_img"]["tmp_name"], $target_file)) {
                $update_stmt = $conn->prepare("UPDATE users SET username=?, phone=?, address=?, profile_img=? WHERE user_id=?");
                $update_stmt->bind_param("ssssi", $new_name, $new_phone, $new_address, $new_filename, $user_id);
            }
        }
    } else {
        $update_stmt = $conn->prepare("UPDATE users SET username=?, phone=?, address=? WHERE user_id=?");
        $update_stmt->bind_param("sssi", $new_name, $new_phone, $new_address, $user_id);
    }

    if (isset($update_stmt) && $update_stmt->execute()) {
        $_SESSION['username'] = $new_name;
        $success_msg = "Profile updated successfully!";
        // หน่วงเวลา 1.5 วินาทีแล้วรีเฟรชเพื่อให้เห็นการเปลี่ยนแปลง
        echo "<script>setTimeout(() => { window.location.href='account.php'; }, 1500);</script>";
    }
}

// 3. ดึงข้อมูลล่าสุดมาแสดง
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

$has_image = !empty($user_data['profile_img']);
$display_initial = strtoupper(mb_substr($user_data['username'] ?? $user_data['email'], 0, 1, 'UTF-8'));
?>

<style>
/* High-Tech UI for Account */
.body-account {
    background: radial-gradient(circle at top right, #111827 0%, #030712 100%);
    min-height: 100vh;
    padding: 100px 20px 40px;
}
.account-wrapper {
    max-width: 1200px;
    margin: 0 auto;
}
.page-title {
    color: #fff;
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 30px;
    text-shadow: 0 0 15px rgba(139,92,246,0.6);
}

.account-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 30px;
}

/* Sidebar */
.profile-sidebar {
    background: rgba(17, 24, 39, 0.6);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(139, 92, 246, 0.3);
    border-radius: 20px;
    padding: 40px 20px;
    text-align: center;
    box-shadow: 0 0 30px rgba(139,92,246,0.1);
}
.avatar-container {
    width: 120px;
    height: 120px;
    margin: 0 auto 20px;
    position: relative;
    cursor: pointer;
    border-radius: 50%;
    padding: 4px;
    background: linear-gradient(135deg, #7c3aed, #00ffcc);
    box-shadow: 0 0 20px rgba(124, 58, 237, 0.4);
}
.profile-img-circle {
    width: 100%;
    height: 100%;
    background: #1e293b;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    font-weight: 900;
    color: #00ffcc;
}
.sidebar-name {
    color: #fff;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 5px;
}
.sidebar-email {
    color: #94a3b8;
    font-size: 13px;
    margin-bottom: 30px;
}

.profile-links a {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    color: #cbd5e1;
    text-decoration: none;
    border-radius: 12px;
    margin-bottom: 10px;
    background: rgba(255,255,255,0.03);
    transition: 0.3s;
}
.profile-links a:hover {
    background: rgba(139, 92, 246, 0.15);
    color: #00ffcc;
    transform: translateX(5px);
}
.profile-links a span { display: flex; align-items: center; gap: 10px; font-weight: 500; }
.logout-btn {
    background: rgba(244, 63, 94, 0.1) !important;
    color: #fb7185 !important;
    border: 1px solid rgba(244, 63, 94, 0.2);
}
.logout-btn:hover { background: rgba(244, 63, 94, 0.2) !important; color: #fff !important; }

/* Main Content */
.content-box {
    background: rgba(17, 24, 39, 0.6);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(139, 92, 246, 0.3);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 0 30px rgba(139,92,246,0.1);
}
.box-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    border-bottom: 1px dashed rgba(148, 163, 184, 0.2);
    padding-bottom: 20px;
}
.box-title { color: #fff; font-size: 22px; font-weight: 700; }
.btn-edit {
    background: linear-gradient(135deg, #7c3aed, #3b82f6);
    color: #fff;
    border: none;
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-edit:hover { box-shadow: 0 5px 15px rgba(124,58,237,0.4); transform: translateY(-2px); }

.info-row {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 25px;
    padding: 15px;
    background: rgba(255,255,255,0.02);
    border-radius: 12px;
    transition: 0.3s;
}
.info-row:hover { background: rgba(139,92,246,0.05); border-left: 3px solid #00ffcc; }
.icon-box {
    width: 45px;
    height: 45px;
    background: rgba(139, 92, 246, 0.1);
    color: #a78bfa;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}
.info-text label { color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 700; margin-bottom: 5px; display: block; }
.info-text div { color: #f8fafc; font-size: 15px; font-weight: 500; }
.not-set { color: #f59e0b !important; font-style: italic; }

/* Edit Mode styling overrides */
.form-group label { color: #8b5cf6; font-size: 13px; text-transform: uppercase; font-weight: 700; margin-bottom: 8px; display: block; }
.form-input {
    width: 100%;
    padding: 15px;
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 10px;
    color: #00ffcc;
    font-size: 15px;
    outline: none;
    transition: 0.3s;
    margin-bottom: 20px;
}
.form-input:focus { border-color: #00ffcc; box-shadow: 0 0 10px rgba(0,255,204,0.2); }
.edit-actions { display: flex; gap: 10px; }
.btn-cancel { background: transparent; border: 1px solid #64748b; color: #cbd5e1; padding: 8px 20px; border-radius: 8px; cursor: pointer; transition: 0.3s; }
.btn-cancel:hover { background: rgba(255,255,255,0.1); color: #fff; }
.btn-save { background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 8px 25px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 5px; }
.btn-save:hover { box-shadow: 0 5px 15px rgba(16,185,129,0.4); transform: translateY(-2px); }

.avatar-profile-img-go { text-align: center; margin-bottom: 30px; }
.upload-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    opacity: 0;
    transition: 0.3s;
}
.avatar-container:hover .upload-overlay { opacity: 1; }
.profile-button { color: #94a3b8; font-size: 13px; margin-top: 10px; }
</style>

<div class="body-account">
    <div class="account-wrapper">
        <h1 class="page-title"><i class='bx bx-user-circle'></i> My Account</h1>

        <div class="account-grid">
            <aside class="profile-sidebar">
                <div class="avatar-container">
                    <div class="profile-img-circle">
                        <?php if($has_image) {?>
                            <img src="imgs/profiles/<?= $user_data['profile_img'] ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php } else { ?>
                            <?= $display_initial ?>
                        <?php } ?>
                    </div>
                </div>

                <h3 class="sidebar-name"><?php echo htmlspecialchars($user_data['username'] ?? 'TechGear Member') ?></h3>
                <div class="sidebar-email"><?php echo htmlspecialchars($user_data['email']) ?></div>

                <nav class="profile-links">
                    <a href="orders.php">
                        <span><i class='bx bx-box' style="font-size:18px;"></i> My Orders</span>
                        <i class='bx bx-chevron-right'></i>
                    </a>
                    <a href="wishlist.php">
                        <span><i class='bx bx-heart' style="font-size:18px;"></i> Wishlist</span>
                        <i class='bx bx-chevron-right'></i>
                    </a>
                    <div class="logout-container">
                        <a href="logout.php" class="logout-btn">
                            <span><i class="bx bx-arrow-out-right-square-half " style="font-size:18px;" ></i> Sign Out</span>
                        </a>
                    </div>
                </nav>
            </aside>

            <main class="content-box">

                <?php if(!empty($success_msg)): ?>
                <div class="alert-success"><i class='bx bxs-check-circle'></i> <?= $success_msg ?></div>
                <?php echo $success_msg ?>
                <?php endif; ?>

                <div id="view-mode" class="content-box-con">
                    <div class="content-box-con-profile">
                        <div class="box-header">
                            <h2 class="box-title">Profile Information</h2>
                            <button class="btn-edit" onclick="toggleEditMode()"><i class='bx bx-pencil'></i> Edit</button>
                        </div>

                        <div class="info-row">
                            <div class="icon-box"><i class='bx bx-user'></i></div>
                            <div class="info-text">
                                <label>Full Name</label>
                                <div class="info-text-user"><?php echo htmlspecialchars($user_data['username'] ?? 'Not set') ?></div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="icon-box"><i class='bx bx-envelope'></i></div>
                            <div class="info-text">
                                <label>Email</label>
                                <div><?php echo htmlspecialchars($user_data['email']) ?></div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="icon-box"><i class='bx bx-phone'></i></div>
                            <div class="info-text">
                                <label>Phone</label>
                                <?php
                                if (!empty($user_data['phone'])) {
                                    echo "<div>" . htmlspecialchars($user_data['phone']) . "</div>";
                                } else {
                                    echo "<div class='not-set'>Not set</div>";
                                }
                                ?>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="icon-box"><i class="bx bx-location-alt-2"></i></div>
                            <div class="info-text">
                                <label>Address</label>
                                <?php if(!empty($user_data['address'])) { ?>
                                    <div><?php echo htmlspecialchars($user_data['address']) ?></div>
                                <?php } else { ?>
                                    <div class="not-set">Not set</div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="edit-mode" class="edit-mode-con">
                    <div class="content-box-con-edit">
                        <form method="POST" action="account.php" enctype="multipart/form-data">
                            <div class="box-header">
                                <h2 class="box-title">Profile Information</h2>
                                <div class="edit-actions">
                                    <button type="button" class="btn-cancel" onclick="toggleEditMode()">Cancel</button>
                                    <button type="submit" class="btn-save"><i class='bx bx-save'></i> Save</button>
                                </div>
                            </div>

                            <!----------------- avatar-profile-img-go ------------------->

                            <div class="avatar-profile-img-go">
                                <div class="avatar-container" onclick="triggerUpload()"">
                                    <div class="profile-img-circle" id="preview-box">
                                        <?php if($has_image) { ?>
                                            <img id="img-preview" src="imgs/profiles/<?= $user_data['profile_img'] ?>" style="width:100%; height:100%; object-fit:cover;">
                                        <?php } else { ?>
                                            <span id="initial-preview"><?= $display_initial ?></span>
                                        <?php }?>
                                    </div>
                                    <div class="upload-overlay"><i class='bx bx-camera'></i></div>
                                </div>
                                <input type="file" name="profile_img" id="profile_upload" hidden accept="image/*" onchange="previewFile()">
                                <p  class="profile-button">Click circle to change photo</p>
                            </div>

                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="username" class="form-input" value="<?= htmlspecialchars($user_data['username'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" class="form-input" value="<?php echo htmlspecialchars($user_data['email']) ?>" readonly title="Email cannot be changed">
                            </div>

                            <div class="form-row">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label>Phone</label>
                                    <input type="text" name="phone" class="form-input" value="<?= htmlspecialchars($user_data['phone'] ?? '') ?>">
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label>Country</label>
                                    <input type="text" class="form-input" value="Thailand" readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Full Address</label>
                                <textarea name="address" class="form-input" style="height: 100px; resize: none;"><?= htmlspecialchars($user_data['address'] ?? '') ?></textarea>
                            </div>
                        </form>
                    </div>
                </div>

            </main>
        </div>
    </div>
</div>

<?php
include 'page/footer.php';
?>