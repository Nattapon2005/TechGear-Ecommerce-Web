<?php
// บังคับเปิด Session ตั้งแต่บรรทัดแรกสุดของทุกหน้า
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// อ่านค่าจาก environment variable ก่อน (ใช้ได้ทั้ง Docker, Vercel และ hosting ทั่วไป)
// ถ้าไม่มีการตั้งค่า จะ fallback เป็นค่าของ docker-compose (db / root / root)
$host   = getenv('DB_HOST') ?: 'db';
$user   = getenv('DB_USER') ?: 'root';
$pass   = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'root';
$dbname = getenv('DB_NAME') ?: 'techgear_v2_db';
$port   = (int) (getenv('DB_PORT') ?: 3306);

$conn = @mysqli_connect($host, $user, $pass, $dbname, $port);

if (!$conn) {
    // ไม่เปิดเผยรายละเอียดการเชื่อมต่อให้ผู้ใช้เห็น
    error_log('DB connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    die('ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาลองใหม่ภายหลัง');
}

mysqli_set_charset($conn, 'utf8mb4');
