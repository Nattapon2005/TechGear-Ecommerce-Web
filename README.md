# ⚡ TechGear E-commerce Application

ระบบ E-commerce สำหรับการซื้อขายอุปกรณ์เกมมิ่งและไอทีแบบครบวงจร พร้อมระบบจัดการสินค้าและผู้ใช้งาน (พัฒนาด้วย PHP, MySQL และ CSS/SCSS)

## 🚀 ฟีเจอร์ของระบบที่เสร็จสมบูรณ์ (System Features)

**ระบบสำหรับผู้ใช้งานทั่วไป (User Features):**
- **Authentication:** ระบบ Login และ Register สำหรับสมาชิก
- **Product Catalog:** หน้าแสดงรายการสินค้าทั้งหมด (`products.php`), หน้าแรก (`index.php`) แสดงสินค้าแยกตามหมวดหมู่และสินค้าแนะนำ
- **Product Details:** หน้ารายละเอียดสินค้า (`product-detail.php`)
- **Shopping Cart:** ระบบตะกร้าสินค้า (เพิ่ม/ลด จำนวนสินค้าได้แบบ Real-time)
- **Checkout (✅ New):** หน้า Checkout (`checkout.php`) ระบบสั่งซื้อสินค้า กรอกที่อยู่จัดส่ง และเลือกช่องทางการชำระเงิน (PromptPay / Bank Transfer)
- **Order Management (✅ New):** ดูประวัติการสั่งซื้อได้ในหน้า My Orders (`orders.php`) พร้อมหน้า Order Details (`order-detail.php`) เพื่อดูข้อมูลคำสั่งซื้ออย่างละเอียด
- **Payment & Slip Upload (✅ New):** รองรับการชำระเงินผ่าน QR Code พร้อมการแนบหลักฐานการโอนเงิน (Slip) ในหน้ารายละเอียดคำสั่งซื้อ อัปโหลดสลิปได้ทันทีและมีการเปลี่ยนสถานะ
- **Profile Management:** หน้า Account (`account.php`) สำหรับแก้ไขข้อมูลส่วนตัว เปลี่ยนรูปโปรไฟล์ ที่อยู่ และเบอร์โทรศัพท์

*(หมายเหตุ: ระบบพัฒนาเสร็จสมบูรณ์ในส่วนของ Client/User Flow แล้ว คุณสามารถใช้ Docker ในการรันเซิร์ฟเวอร์เพื่อทดสอบการทำงานได้ทั้งหมดตามคำแนะนำด้านล่าง)*

---

## 🐳 Docker (การเปิดใช้งาน Server)

ระบบนี้พัฒนาอยู่บนสถาปัตยกรรมที่ใช้ Docker ในการสร้าง Web Server (PHP/Apache) และ Database (MySQL) ให้พร้อมใช้งานโดยไม่ต้องติดตั้งโปรแกรมบนเครื่อง
**เนื่องจากขณะนี้คุณยังไม่ได้เปิดใช้งาน Docker กรุณารันคำสั่งด้านล่างใน Terminal เพื่อเริ่มระบบ:**

```bash
docker-compose up -d    # เปิด server (ทำงานใน background)
docker-compose up       # เปิด server (ดู log การทำงานแบบสด)
docker-compose down     # ปิด server
```

---

## 🔗 URLs ของระบบ (หลังจากรัน Docker สำเร็จ)

| หน้า | URL |
|------|-----|
| 🌐 เว็บ | http://localhost:8090 |
| 🗄️ phpMyAdmin | http://localhost:8091 |

---

## 🔐 Login Credentials (บัญชีสำหรับทดสอบ)

| Role  | Email                        | Password      |
|-------|------------------------------|---------------|
| Admin | admin@techgear.com           | password      |
| User  | nattapon09072548@gmail.com   | password      |

---

## 🎨 SCSS (สำหรับนักพัฒนา)
```bash
npm run sass-dev        # compile SCSS แบบ watch อัตโนมัติเมื่อแก้ไข
```


# 📦 PHP Form — POST & File Upload

### การอัปโหลดไฟล์ใน PHP (ฟอร์มและรูปภาพ)
ทุกฟอร์มที่มีการอัปโหลดไฟล์ เช่น เปลี่ยนรูปโปรไฟล์ หรือ แนบสลิปโอนเงิน จะใช้โครงสร้างดังนี้:

**HTML ฝั่งส่ง**
```html
<form action="account.php" method="POST" enctype="multipart/form-data">
    <input type="text" name="username">
    <input type="file" name="profile_img">
    <button type="submit">Save</button>
</form>
```

**PHP ฝั่งรับ**
```php
$name = $_POST['username'];
$file = $_FILES['profile_img'];
```

**สรุปแต่ละหน้า**
| หน้า | action | enctype |
|------|--------|---------|
| Login | `login.php` | ไม่ต้องใส่ |
| Profile | `account.php` | `multipart/form-data` ✅ |
| Checkout | `checkout.php` | ไม่ต้องใส่ (รับเป็นตัวหนังสือ) |
| แจ้งโอนเงิน | `upload_slip.php` | `multipart/form-data` ✅ |

> กฎง่ายๆ: มีอัปโหลดไฟล์ → ใส่ `enctype="multipart/form-data"` เสมอ


# 🎨 SCSS — บันทึกการใช้งาน

## & คืออะไร?
`&` = อ้างอิง parent selector

```scss
.dropdown {
    &.open .selected { ... }  // → .dropdown.open .selected
    &.open .options  { ... }  // → .dropdown.open .options
}
```

## Adjacent Sibling +
```scss
.options-box + .options-box {
    border-top: 1px solid var(--border);
}
// อันแรกไม่มีเส้น, อันที่ 2, 3, 4 มีเส้นแบ่งด้านบน
```

## Dropdown เปิด/ปิด
```scss
.options {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
    pointer-events: none;
}

&.open .selected {
    background: var(--surface2);
    border-color: rgba(124, 92, 252, 0.45);
}

&.open .options {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: all;
}
```

## Grid / List View
```scss
.product-item-con {
    &.grid-view { display: grid; }
    &.list-view  { display: flex; }
}
// → .product-item-con.grid-view {}
// → .product-item-con.list-view {}
```

## Pseudo-classes
```scss
.btn {
    &:hover    { background: red; }
    &:active   { ... }
    &.active   { ... }
    &.disabled { ... }
}
```

<!-- searchParams
หน้าที่ของมันคือโฟกัสและจัดการเฉพาะข้อความทุกอย่างที่อยู่ หลังเครื่องหมาย ? ใน URL เท่านั้น (เช่น ?cat=shoes&max=1000 -->

<!-- .set(...)
มันคือ "คำสั่ง (Function)" ของแผนก searchParams ครับ แปลตรงตัวว่า "ตั้งค่า" หรือ "กำหนดค่า"
ความฉลาดของคำสั่ง set คือ:
ถ้าใน URL ยังไม่มีคำนี้มาก่อน ➔ มันจะ เพิ่ม เข้าไปให้ (Append)
ถ้าใน URL มีคำนี้อยู่แล้ว ➔ มันจะ เขียนทับ ของเก่าให้ทันที (Overwrite) -->