//===================================== index.php file

// Small shared feedback helpers ------------------------------------------------
function bumpCartFeedback() {
    const cartIcon = document.querySelector('.menu-login-icon-tow');
    const countEl = document.querySelector('.count-cart');

    [cartIcon, countEl].forEach((el) => {
        if (!el) return;
        el.classList.remove('cart-bump');
        void el.offsetWidth;
        el.classList.add('cart-bump');
        setTimeout(() => el.classList.remove('cart-bump'), 650);
    });
}

function showAddedButtonState(form) {
    const wrap = form.querySelector('.btn-product-item');
    if (!wrap) return;
    const btn = wrap.querySelector('button');
    if (!btn || wrap.classList.contains('is-added')) return;

    const original = btn.innerHTML;
    wrap.classList.add('is-added');
    btn.innerHTML = "<i class='bx bx-check'></i> Added!";

    setTimeout(() => {
        wrap.classList.remove('is-added');
        btn.innerHTML = original;
    }, 1500);
}

async function addToCartAsync(event, form) {
    event.preventDefault();
    const formData = new FormData(form);
    formData.append('ajax', '1');

    const productName = formData.get('product_name') || 'Item';

    try {
        const response = await fetch('add_to_cart.php', { method: 'POST', body: formData });
        if (response.ok) {
            const result = await response.json();
            if (result.status === 'success') {
                document.getElementById('cart-count').innerText = result.cart_count;
                const newItem = result.item;
                let found = false;
                cartData.forEach(item => {
                    if (item.id == newItem.id) {
                        item.qty = parseInt(item.qty) + parseInt(newItem.qty);
                        found = true;
                    }
                });
                if (!found) { cartData.push(newItem); }
                if (typeof renderCart === "function") { renderCart(); }

                bumpCartFeedback();
                showAddedButtonState(form);

                if (window.showToast) {
                    window.showToast('Added to Cart!', productName + ' is now in your cart.', 'success', 'bx-cart-add');
                }
            } else if (window.showToast) {
                window.showToast('Could not add to cart', result.message || 'Please try again.', 'remove', 'bx-error-circle');
            }
        }
    } catch (error) {
        console.error("Fetch error:", error);
        if (window.showToast) {
            window.showToast('Connection problem', 'Could not reach the server. Please try again.', 'remove', 'bx-error-circle');
        }
    }
}
