<?php
session_start();
include 'config/db.php';

// ถ้าล็อกอินอยู่แล้ว ไม่ควรให้สมัครใหม่ ให้เด้งไปหน้าแรกเลย
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. เช็คว่ารหัสตรงกันไหม
    if (empty($username)) {
        $error = "Username is required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } else {
        // 2. เช็คว่ามีอีเมลนี้หรือยัง
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "This email is already registered";
        } else {
            // 3. บันทึกข้อมูล (ใช้ Password Hash เพื่อความปลอดภัยสูงสุด)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";

            if (mysqli_query($conn, $sql)) {
                header("Location: login.php?registered=true"); // สมัครเสร็จไปหน้าล็อกอิน
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>Create Account - TechGear V2</title>
</head>
<body>

    <div class="body-card">
        <div class="auth-card">
            <a href="login.php" class="back-link"><i class='bx bx-left-arrow-alt'></i> Back to Sign In</a>

            <div class="auth-header">
                <h2 class="auth-title">Create Account</h2>
                <p class="auth-subtitle">Join TechGear and upgrade your setup</p>
            </div>

            <form action="register.php" method="POST">
                <?php if($error): ?>
                    <div class="error-msg">
                        <i class='bx bx-error-circle'></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <div class="input-group">
                    <label>Username</label>
                    <div class="input-box">
                        <i class='bx bx-user'></i>
                        <input type="text" name="username" placeholder="Your username" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Email Address</label>
                    <div class="input-box">
                        <i class='bx bx-envelope'></i>
                        <input type="email" name="email" placeholder="you@example.com" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Create Password</label>
                    <div class="input-box">
                        <i class='bx bx-lock-alt'></i>
                        <input type="password" name="password" placeholder="Min. 8 characters" required minlength="8">
                    </div>
                </div>

                <div class="input-group">
                    <label>Confirm Password</label>
                    <div class="input-box">
                        <i class='bx bx-lock-open-alt'></i>
                        <input type="password" name="confirm_password" placeholder="Re-enter password" required>
                    </div>
                </div>

                <button type="submit" class="btn-auth">Create Account</button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="login.php">Sign in</a>
            </div>
        </div>
    </div>

</body>
</html>