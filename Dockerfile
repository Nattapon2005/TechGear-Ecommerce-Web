# ใช้ PHP เวอร์ชัน 8.2 พร้อม Apache
FROM php:8.2-apache

# ติดตั้งตัวช่วยเชื่อมต่อฐานข้อมูล MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# เปิดโหมด Rewrite + ตั้งค่า Apache/PHP สำหรับ production
RUN a2enmod rewrite headers expires \
    && printf '<IfModule mod_dir.c>\nDirectoryIndex index.php index.html\n</IfModule>\n' \
        > /etc/apache2/mods-enabled/dir.conf \
    && printf '<Directory /var/www/html>\n    Options -Indexes +FollowSymLinks\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
        > /etc/apache2/conf-enabled/app.conf \
    && echo "ServerTokens Prod" >> /etc/apache2/apache2.conf \
    && echo "ServerSignature Off" >> /etc/apache2/apache2.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && { \
        echo "expose_php = Off"; \
        echo "display_errors = Off"; \
        echo "log_errors = On"; \
        echo "upload_max_filesize = 8M"; \
        echo "post_max_size = 10M"; \
        echo "max_execution_time = 60"; \
        echo "date.timezone = Asia/Bangkok"; \
    } > /usr/local/etc/php/conf.d/production.ini

# คัดลอกโค้ดเข้า image (สำคัญ: ถ้าไม่มีบรรทัดนี้ docroot จะว่าง -> Apache ตอบ 403)
COPY . /var/www/html

# โฟลเดอร์อัปโหลดต้องเขียนได้โดย www-data
RUN mkdir -p /var/www/html/imgs/profiles /var/www/html/imgs/slips \
    && chown -R www-data:www-data /var/www/html/imgs

WORKDIR /var/www/html

EXPOSE 80
