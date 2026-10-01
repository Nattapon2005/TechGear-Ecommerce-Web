<?php
// บังคับเปิด Session ตั้งแต่บรรทัดแรกสุดของทุกหน้า
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "db";
$user = "root";
$pass = "root";
$dbname = "techgear_v2_db"; // ฐานข้อมูล ใน Docker

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");
?>