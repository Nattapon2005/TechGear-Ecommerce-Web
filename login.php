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
                header("Location: admin/index.php");
                exit();
            } else {

                $_SESSION['user_id'] = isset($user['user_id']) ? $user['user_id'] : $user['id'];
                $_SESSION['username'] = $user['username'];
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
    <link rel="stylesheet" href="dist/css/style.css">

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

            <button class="btn-google">
                <img src="imgs/google.png" width="20" alt="G">
                Continue with Google
            </button>

            <div class="divider"><span>OR</span></div>

            <form action="login.php" method="POST">
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

</body>
</html>

