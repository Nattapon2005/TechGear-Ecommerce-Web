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

    // Self-healing: ซ่อมแซม URL รูปที่เสียใน Database อัตโนมัติในเบื้องหลัง
    $dead_urls_update = "
        UPDATE categories SET cat_image_url = 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=800&q=80' WHERE cat_id = 2 AND cat_image_url LIKE '%photo-1527814050087-179f0011abf4%';
        UPDATE categories SET cat_image_url = 'https://images.unsplash.com/photo-1612287233002-91d0bc5367ff?w=800&q=80' WHERE cat_id = 5 AND cat_image_url LIKE '%photo-1600080972464-8e5f2840d437%';
        UPDATE products SET p_image_url = 'https://images.unsplash.com/photo-1617233083187-be4925d699d6?q=80&w=500' WHERE p_image_url LIKE '%photo-1615663245857-ac9310031881%' AND p_name LIKE '%Logitech%';
        UPDATE products SET p_image_url = 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?q=80&w=500' WHERE p_image_url LIKE '%photo-1527814050087-179f0011abf4%';
        UPDATE products SET p_image_url = 'https://images.unsplash.com/photo-1612287233002-91d0bc5367ff?q=80&w=500' WHERE p_image_url LIKE '%photo-1600080972464-8e5f2840d437%';
        UPDATE products SET p_image_url = 'https://images.unsplash.com/photo-1649197811770-756bb5f4a57f?q=80&w=500' WHERE p_image_url LIKE '%photo-1615663245857-ac9310031881%' AND p_name LIKE '%Desk Mat%';
        INSERT IGNORE INTO users (username, email, password, role, phone, address) VALUES 
        ('Admin TechGear', 'admin@techgear.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '0999999999', 'Admin HQ'),
        ('Demo Customer', 'demo@techgear.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '0812345678', '123 Sukhumvit Road, Bangkok');
    ";
    @mysqli_multi_query($conn, $dead_urls_update);
    while (@mysqli_next_result($conn)) {;}
} catch (mysqli_sql_exception $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' . (getenv('APP_ENV') === 'development' ? $e->getMessage() : 'กรุณาลองใหม่ภายหลัง'));
}
