# ใช้ PHP เวอร์ชัน 8.2 พร้อม Apache
FROM php:8.2-apache

# ติดตั้งตัวช่วยเชื่อมต่อฐานข้อมูล MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# เปิดโหมด Rewrite (เผื่อทำ URL สวยๆ ในอนาคต)
RUN a2enmod rewrite