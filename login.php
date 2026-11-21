<?php
session_start();

include 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
//     header("Location: admin/index.php");
//     exit();
// }

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);


    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);


        if (password_verify($password, $user['password']) || $password === $user['password']) {

            // ----------------------------------------------------
            // ระบบแยกสิทธิ์ (Role Management)
            // เช็คว่า User คนนี้เป็น Admin หรือ ลูกค้าทั่วไป
            // ----------------------------------------------------
            if (isset($user['role']) && $user['role'] == 'admin') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['user_id'] = isset($user['user_id']) ? $user['user_id'] : $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = 'admin';
                header("Location: admin/index.php");
                exit();
            } else {
                $_SESSION['user_id'] = isset($user['user_id']) ? $user['user_id'] : $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = 'customer';
                header("Location: index.php");
                exit();
            }

        } else {
            $error = "รหัสผ่านไม่ถูกต้อง! กรุณาลองอีกครั้ง";
        }
    } else {
        $error = "ไม่พบอีเมลนี้ในระบบ!";
    }
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/dist/css/style.css?v=<?= @filemtime(__DIR__ . '/dist/css/style.css') ?: time() ?>">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" href="/Favicon/arrow-with-bright-neon-colors.png">

    <!-- Font Awesome Icon Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Basic Icons -->
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <!-- Filled Icons -->
    <link href="https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css" rel="stylesheet">
    <!-- Brand Icons -->
    <link href="https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css" rel="stylesheet">
    <link rel="icon" href="Favicon/arrow-with-bright-neon-colors.png">
    <title>TechGear Login</title>
</head>
<body>

    <div class="auth-body">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="auth-logo-login">TG</div>
            </div>

            <h2 class="auth-title">Welcome Back</h2>
            <p class="auth-subtitle">Sign in to continue</p>

            <button type="button" class="btn-google" onclick="alert('Google Sign-In จำลองไว้เพื่อความสวยงามใน Portfolio กรุณาใช้ปุ่ม Quick Demo Login ด้านล่างเพื่อทดลองใช้งานจริงครับ!')">
                <img src="imgs/google.png" width="20" alt="G">
                Continue with Google
            </button>

            <!-- Quick Demo Login Section for Portfolio / Reviewers -->
            <div style="margin: 18px 0; background: rgba(201, 172, 130, 0.08); border: 1px dashed rgba(201, 172, 130, 0.35); border-radius: 12px; padding: 14px; text-align: left;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <span style="font-size: 11px; font-weight: 700; color: #c9ac82; letter-spacing: 0.05em; text-transform: uppercase;">
                        <i class='bx bxs-zap' style="color: #c9ac82;"></i> Quick Demo Login
                    </span>
                    <span style="font-size: 10px; color: #8d9098;">คลิกเดียวเข้าสู่ระบบ</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <button type="button" onclick="fillDemo('admin')" style="background: rgba(201, 172, 130, 0.18); border: 1px solid rgba(201, 172, 130, 0.4); color: #f3f1ec; padding: 9px 8px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s;">
                        <i class='bx bxs-shield-quarter' style="color: #c9ac82; font-size: 14px;"></i> Demo Admin
                    </button>
                    <button type="button" onclick="fillDemo('customer')" style="background: rgba(127, 174, 143, 0.15); border: 1px solid rgba(127, 174, 143, 0.3); color: #f3f1ec; padding: 9px 8px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s;">
                        <i class='bx bxs-user-check' style="color: #7fae8f; font-size: 14px;"></i> Demo User
                    </button>
                </div>
                <div style="margin-top: 8px; font-size: 10.5px; color: #7f838b; text-align: center;">
                    รหัสผ่านสำหรับทั้งสองบัญชี: <code style="color: #c9ac82; background: rgba(0,0,0,0.3); padding: 1px 5px; border-radius: 4px;">password</code>
                </div>
            </div>

            <div class="divider"><span>OR SIGN IN MANUALLY</span></div>

            <form action="login.php" method="POST" id="loginForm">
                <?php if($error): ?><p style="color:red; font-size:0.9rem; margin-bottom:10px;"><?= $error ?></p><?php endif; ?>

                <div class="input-group">
                    <label>Email</label>
                    <div class="input-box">
                        <span class="input-icon"><i class="bx bx-envelope"></i></span>
                        <input type="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <div class="input-box">
                        <span class="input-icon"><i class="bx bx-lock"></i></span>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-auth">Sign In</button>
            </form>

            <div class="auth-footer">
                <a href="#">Forgot password?</a>
                <a  href="register.php" class="auth-end"><span>Need an account?</span> Sign up</a>
            </div>
        </div>
    </div>

    <script src="src/animations.js?v=<?= time() ?>"></script>
    <script>
        function fillDemo(role) {
            const form = document.getElementById('loginForm');
            const emailInput = form.querySelector('input[name="email"]');
            const passInput = form.querySelector('input[name="password"]');
            
            if (role === 'admin') {
                emailInput.value = 'admin@techgear.com';
                passInput.value = 'password';
            } else {
                emailInput.value = 'demo@techgear.com';
                passInput.value = 'password';
            }
            form.submit();
        }
    </script>
</body>
</html>

