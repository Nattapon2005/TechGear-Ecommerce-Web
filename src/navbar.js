
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
        // Send the change to the backend silently
        const response = await fetch(`update_cart.php?action=${action}&id=${index}&ajax=1`);

        if (response.ok) {
            let removedItem = null;

            // Update local cart immediately so the UI feels instant
            if (action === 'increase') {
                cartData[index].qty++;
            } else if (action === 'decrease') {
                if (cartData[index].qty > 1) {
                    cartData[index].qty--;
                } else {
                    removedItem = cartData[index];
                    cartData.splice(index, 1); // qty was 1 -> remove
                }
            } else if (action === 'remove') {
                removedItem = cartData[index];
                cartData.splice(index, 1);
            }

            // Redraw the cart while keeping the sidebar open
            renderCart();

            if (removedItem) {
                const cartIcon = document.querySelector('.menu-login-icon-tow');
                const countEl = document.querySelector('.count-cart');
                [cartIcon, countEl].forEach((el) => {
                    if (!el) return;
                    el.classList.remove('cart-bump');
                    void el.offsetWidth;
                    el.classList.add('cart-bump');
                    setTimeout(() => el.classList.remove('cart-bump'), 650);
                });

                if (window.showToast) {
                    window.showToast('Item Removed', (removedItem.name || 'Item') + ' removed from your cart.', 'remove', 'bx-trash');
                }
            }
        }
    } catch (error) {
        console.error("Update failed:", error);
        if (window.showToast) {
            window.showToast('Could not update cart', 'Please try again.', 'remove', 'bx-error-circle');
        }
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