<?php
// บังคับเปิด Session ตั้งแต่บรรทัดแรกสุดของทุกหน้า
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// อ่านค่าจาก environment variable ก่อน (ใช้ได้ทั้ง Docker, TiDB Cloud, Aiven, Vercel และ hosting ทั่วไป)
// ถ้าไม่มีการตั้งค่า จะ fallback เป็นค่าของ docker-compose (db / root / root)
$host    = getenv('DB_HOST') ?: 'db';
$user    = getenv('DB_USER') ?: 'root';
$pass    = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'root';
$dbname  = getenv('DB_NAME') ?: 'techgear_v2_db';
$port    = (int) (getenv('DB_PORT') ?: 3306);
$use_ssl = (getenv('DB_SSL') === 'true' || str_contains($host, 'tidbcloud.com') || str_contains($host, 'aivencloud.com'));

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = mysqli_init();

    if ($use_ssl) {
        // สำหรับ Cloud Database (เช่น TiDB Cloud / Aiven) ที่ต้องใช้การเชื่อมต่อแบบเข้ารหัส SSL
        mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
        mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        mysqli_real_connect($conn, $host, $user, $pass, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);
    } else {
        mysqli_real_connect($conn, $host, $user, $pass, $dbname, $port);
    }

    mysqli_set_charset($conn, 'utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' . (getenv('APP_ENV') === 'development' ? $e->getMessage() : 'กรุณาลองใหม่ภายหลัง'));
}
