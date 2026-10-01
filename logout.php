<?php
session_start();

// 1. ล้างข้อมูลในตัวแปร Session ทั้งหมด
session_unset();

// 2. ทำลาย Session ให้หายไป
session_destroy();

// 3. เด้งกลับไปหน้าแรก (จะกลายเป็นสถานะไม่ได้ล็อกอิน)
header("Location: index.php");
exit();