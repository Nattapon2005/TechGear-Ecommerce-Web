//===================================== index.php file

async function addToCartAsync(event, form) {
    event.preventDefault();
    const formData = new FormData(form);
    formData.append('ajax', '1');
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
                console.log("Added to cart!");
            }
        }
    } catch (error) { console.error("Fetch error:", error); }
}