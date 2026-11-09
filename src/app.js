


// 犧歩ｸｱ犹霞ｸ・ｸ・ｹ謂ｸｲ犧ｭ犧吭ｸｴ犹犧｡犧癌ｸｱ犹謂ｸ吭ｸ樅ｸｴ犧｡犧樅ｹ呉ｸもｹ霞ｸｭ犧・ｸｧ犧ｲ犧｡
if (document.getElementById('typing-text')) {
    var typed = new Typed('#typing-text', {
        strings: [
            'Level Up Your',
            'Upgrade Your',
            'Build Your Dream'
        ], // 犧ｪ犧ｲ犧｡犧ｲ犧｣犧籾ｹ犧樅ｸｴ犹謂ｸ｡犧もｹ霞ｸｭ犧・ｸｧ犧ｲ犧｡犧ｭ犧ｷ犹謂ｸ吭ｹ・ｸｫ犹霞ｸ｡犧ｱ犧吭ｸ樅ｸｴ犧｡犧樅ｹ呉ｸｪ犧･犧ｱ犧壟ｸ≒ｸｱ犧吭ｹ・ｸ扉ｹ霞ｸ・ｸ｣犧ｱ犧・
        typeSpeed: 80,      // 犧・ｸｧ犧ｲ犧｡犹犧｣犹・ｸｧ犧歩ｸｭ犧吭ｸ樅ｸｴ犧｡犧樅ｹ・(犧｡犧ｴ犧･犧･犧ｴ犧ｧ犧ｴ犧吭ｸｲ犧伶ｸｵ)
        backSpeed: 50,      // 犧・ｸｧ犧ｲ犧｡犹犧｣犹・ｸｧ犧歩ｸｭ犧吭ｸ･犧・
        backDelay: 1500,    // 犧｣犧ｭ犧ｫ犧吭ｹ謂ｸｧ犧・ｹ犧ｧ犧･犧ｲ犧≒ｸｵ犹謂ｸｧ犧ｴ犧吭ｸｲ犧伶ｸｵ犧≒ｹ謂ｸｭ犧吭ｸ謂ｸｰ犹犧｣犧ｴ犹謂ｸ｡犧･犧・(1500 = 1.5 犧ｧ犧ｴ犧吭ｸｲ犧伶ｸｵ)
        loop: true,         // 犹・ｸｫ犹霞ｸｧ犧吭ｸ･犧ｹ犧巵ｹ・ｸ巵ｹ犧｣犧ｷ犹謂ｸｭ犧｢犹・
        showCursor: true,   // 犹≒ｸｪ犧扉ｸ・ｸもｸｵ犧扉ｸ≒ｸ｣犧ｰ犧樅ｸ｣犧ｴ犧壟ｹ・(Cursor)
        cursorChar: '|'     // 犧ｫ犧吭ｹ霞ｸｲ犧歩ｸｲ犧もｸｭ犧・ｸもｸｵ犧扉ｸ≒ｸ｣犧ｰ犧樅ｸ｣犧ｴ犧・
    });
}

function toggleWishlist(productId, element) {
    console.log("Toggle Wishlist called for product:", productId);
    const icon = element.querySelector('i');
    if (!icon) return;

    const card = element.closest('.product-item-grid');
    const productNameEl = card ? card.querySelector('.product-item-title h4') : null;
    const productName = productNameEl ? productNameEl.textContent.trim() : 'Item';

    // Check current state before modifying
    const isCurrentlyAdded = icon.classList.contains('bxf');

    // Play animation
    icon.classList.remove('heart-pop');
    void icon.offsetWidth; // Trigger reflow
    icon.classList.add('heart-pop');

    // Optimistic UI Update - change color IMMEDIATELY
    if (!isCurrentlyAdded) {
        // Was NOT added, so we ADD it
        icon.classList.remove('bx');
        icon.classList.add('bxf');
        icon.style.setProperty('color', '#d98a91', 'important');
    } else {
        // Was ADDED, so we REMOVE it
        icon.classList.remove('bxf');
        icon.classList.add('bx');
        icon.style.setProperty('color', '', '');
        // Remove the style attribute completely if empty to let CSS take over
        if(icon.getAttribute('style') === '') icon.removeAttribute('style');
    }

    fetch('toggle_wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        console.log("Wishlist response:", data);
        if (data.status === 'added') {
            const badge = document.querySelector('.wishlist-badge');
            if (badge) badge.style.display = 'block';

            // navbar heart feedback
            const heartIcon = document.querySelector('.menu-login-icon-one');
            if (heartIcon) {
                heartIcon.classList.remove('wishlist-bump');
                void heartIcon.offsetWidth;
                heartIcon.classList.add('wishlist-bump');
            }

            if (window.showToast) {
                window.showToast('Added to Wishlist', productName + ' saved for later.', 'wishlist', 'bx-heart');
            }
        } else if (data.status === 'removed') {
            if (window.showToast) {
                window.showToast('Removed from Wishlist', productName + ' removed from your wishlist.', 'remove', 'bx-trash');
            }

            // If removed on the wishlist page, fade out and remove the element
            if (window.location.pathname.includes('wishlist.php')) {
                const gridItem = element.closest('.product-item-grid');
                if (gridItem) {
                    gridItem.classList.add('is-removing');
                    setTimeout(() => {
                        gridItem.remove();
                        updateWishlistCountUI();
                    }, 380);
                }
            }
        }
    })
    .catch(error => {
        console.error('Error toggling wishlist:', error);
        // Revert UI on error
        if (!isCurrentlyAdded) {
            // We tried to add, but it failed, so revert to empty
            icon.classList.remove('bxf');
            icon.classList.add('bx');
            icon.style.setProperty('color', '', '');
        } else {
            // We tried to remove, but it failed, so revert to solid
            icon.classList.remove('bx');
            icon.classList.add('bxf');
            icon.style.setProperty('color', '#d98a91', 'important');
        }

        if (window.showToast) {
            window.showToast('Something went wrong', 'Could not update your wishlist. Please try again.', 'remove', 'bx-error-circle');
        }
    });
}

// Update the wishlist header count and empty state without a full page reload.
function updateWishlistCountUI() {
    const countEl = document.getElementById('wishlist-count');
    const cards = document.querySelectorAll('.wishlist-grid-container .product-item-grid');

    if (countEl) countEl.textContent = cards.length;

    if (cards.length === 0) {
        const grid = document.querySelector('.wishlist-grid-container');
        const empty = document.getElementById('wishlistEmpty');
        if (grid) grid.style.display = 'none';
        if (empty) {
            empty.style.display = '';
            empty.classList.add('is-shown');
        }
    }
}

// Used by the explicit trash button on the wishlist page.
function removeFromWishlist(productId, element) {
    const card = element.closest('.product-item-grid');
    const heart = card ? card.querySelector('.show-icon-love') : null;
    if (heart) {
        toggleWishlist(productId, heart);
    }
}


