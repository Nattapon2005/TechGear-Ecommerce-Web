
// ดักจับการเลื่อนหน้าจอ (Scroll Event)
window.addEventListener('scroll', function () {
    const navbar = document.querySelector('.navbar');

    // ถ้าเลื่อนลงมามากกว่า 50px
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        // ถ้ากลับไปอยู่บนสุด
        navbar.classList.remove('scrolled');
    }
});

// ฟังก์ชันเปิด-ปิดตะกร้า
function toggleCart(show) {
    const sidebar = document.getElementById('cSidebar');
    const overlay = document.getElementById('cOverlay');
    if (show) {
        sidebar.classList.add('active');
        overlay.classList.add('active');
    } else {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    }
}

// ฟังก์ชันเปิด-ปิดโปรไฟล์
function toggleProfile() {
    document.getElementById('pDropdown').classList.toggle('show');
}

// ฟังก์ชันอัปเดตตะกร้าแบบไม่รีเฟรชหน้า (เพิ่มใหม่)
async function updateQty(action, index) {
    try {
        // ส่งคำสั่งไปที่ update_cart.php หลังบ้านเงียบๆ
        const response = await fetch(`update_cart.php?action=${action}&id=${index}&ajax=1`);

        if (response.ok) {
            // อัปเดตข้อมูลใน cartData (ฝั่งหน้าจอ) ทันทีเพื่อให้เห็นผลเร็ว
            if (action === 'increase') {
                cartData[index].qty++;
            } else if (action === 'decrease') {
                if (cartData[index].qty > 1) {
                    cartData[index].qty--;
                } else {
                    cartData.splice(index, 1); // ถ้าเหลือ 1 แล้วกดลด ให้ลบออก
                }
            } else if (action === 'remove') {
                cartData.splice(index, 1);
            }

            // วาดตะกร้าใหม่ทันทีโดยที่ Sidebar ยังเปิดอยู่
            renderCart();
        }
    } catch (error) {
        console.error("Update failed:", error);
    }
}


function renderCart() {
    const container = document.getElementById('cartItems');
    let total = 0;
    let qty = 0;


    container.innerHTML = cartData.length === 0 ? '<p class="empty-msg" style="text-align:center; color:var(--text-muted); margin-top:50px;">Your cart is empty.</p>' : '';

    cartData.forEach((item, index) => {
        qty += parseInt(item.qty);
        total += parseFloat(item.price) * item.qty;


        container.innerHTML += `
            <div class="cart-items-product">
                <div class="cart-items-product-right">
                    <div class="cart-items-product-con">
                        <div class="cart-items-product-box-img">
                            <img src="${item.image}" alt="${item.name}">
                            <div class="cart-items-product-show-t-p">
                                <p>${item.name}</p>
                                <span>$${parseFloat(item.price).toFixed(2)}</span>

                                <div class="cart-items-product-box-button">
                                    <div class="minus-plus">
                                        <span onclick="updateQty('decrease', ${index})" class="minus-and-plus">
                                            <i class="bx bx-minus" ></i>
                                        </span>
                                        <span>${item.qty}</span>
                                        <span onclick="updateQty('increase', ${index})" class="minus-and-plus">
                                            <i class="bx bx-plus"></i>
                                        </span>
                                    </div>

                                    <div class="delete-product-cart">
                                        <span onclick="updateQty('remove', ${index})">
                                            <i class='bx bx-trash'></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cart-items-product-left">
                    <p class="cart-items-product-price">
                        $${(parseFloat(item.price) * item.qty).toFixed(2)}
                    </p>
                </div>
            </div>
        `;
    });

    // อัปเดตตัวเลขสรุปผล
    document.getElementById('cart-qty').innerText = qty;
    document.getElementById('cart-count').innerText = qty;
    document.getElementById('cart-total').innerText = '$' + total.toFixed(2);
}


// รันครั้งแรกตอนโหลดหน้า
document.addEventListener('DOMContentLoaded', renderCart);

// ปิด Dropdown
window.addEventListener('click', (e) => {
    if (!e.target.closest('.profile-container')) {
        document.getElementById('pDropdown').classList.remove('show');
    }
});