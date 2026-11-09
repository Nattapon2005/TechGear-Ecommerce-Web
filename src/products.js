//===================================== products.php — AJAX filtering
// Categories, max price, search and sort update the grid without a page reload.
(function () {
    const dropdown = document.getElementById('sortDropdown');
    const trigger = document.getElementById('sortTrigger');
    const grid = document.getElementById('productsGrid');

    if (!dropdown || !trigger || !grid) return;

    const labelEl = document.getElementById('sortLabel');
    const optBoxes = document.querySelectorAll('.options-box');
    const priceRange = document.getElementById('priceRange');
    const priceTitle = document.getElementById('priceTitle');
    const searchForm = document.querySelector('.search-bar-lg');
    const filterForm = document.getElementById('filterForm');
    const clearBtn = document.getElementById('clearFilters');
    const countEl = document.getElementById('product-count');
    const gridBtn = document.getElementById('gridBtn');
    const listBtn = document.getElementById('listBtn');

    let viewMode = 'grid';
    let styleImg = 'grid';
    let priceTimer = null;
    let requestId = 0;

    // ------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------
    function currentParams() {
        return new URLSearchParams(window.location.search);
    }

    function categoryOfLink(link) {
        try {
            const url = new URL(link.getAttribute('href'), window.location.href);
            return url.searchParams.get('cat_id') || 'all';
        } catch (e) {
            return 'all';
        }
    }

    function setHidden(form, name, value) {
        if (!form) return;
        let input = form.querySelector('input[type="hidden"][name="' + name + '"]');
        if (value === null || value === undefined || value === '') {
            if (input) input.remove();
            return;
        }
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            form.appendChild(input);
        }
        input.value = value;
    }

    function syncControls(params) {
        const cat = params.get('cat_id') || 'all';
        const search = (params.get('search') || '').trim();
        const max = params.get('max_price') || '1000';
        const sort = params.get('sort') || 'new';

        // category links
        document.querySelectorAll('.cat-link').forEach((link) => {
            link.classList.toggle('active', categoryOfLink(link) === cat);
        });

        // price slider + label
        if (priceRange) priceRange.value = max;
        if (priceTitle) priceTitle.textContent = 'Max Price ($' + max + ')';

        // search box
        if (searchForm) {
            const searchInput = searchForm.querySelector('input[name="search"]');
            if (searchInput) searchInput.value = search;
        }

        // sort dropdown
        if (labelEl) {
            optBoxes.forEach((opt) => {
                const isActive = opt.dataset.value === sort;
                opt.classList.toggle('active', isActive);
                const check = opt.querySelector('i');
                if (check) check.style.opacity = isActive ? '1' : '0';
                if (isActive) {
                    const clone = opt.cloneNode(true);
                    const icon = clone.querySelector('i');
                    if (icon) icon.remove();
                    labelEl.textContent = clone.textContent.trim();
                }
            });
        }

        // keep the fallback forms in sync (useful for no-JS and direct submits)
        [filterForm, searchForm].forEach((form) => {
            if (!form) return;
            setHidden(form, 'cat_id', cat !== 'all' ? cat : null);
            setHidden(form, 'sort', sort !== 'new' ? sort : null);
        });
        setHidden(filterForm, 'search', search || null);
        setHidden(searchForm, 'max_price', max);
    }

    function cleanParams(params) {
        const clean = new URLSearchParams();
        const cat = params.get('cat_id');
        const search = (params.get('search') || '').trim();
        const max = params.get('max_price');
        const sort = params.get('sort');

        if (cat && cat !== 'all') clean.set('cat_id', cat);
        if (search) clean.set('search', search);
        if (max && max !== '1000') clean.set('max_price', max);
        if (sort && sort !== 'new') clean.set('sort', sort);

        return clean;
    }

    // ------------------------------------------------------------
    // Fetch + render
    // ------------------------------------------------------------
    async function load(params, pushHistory) {
        const myRequestId = ++requestId;
        const requestParams = new URLSearchParams(params);
        requestParams.set('ajax', '1');

        grid.classList.add('is-loading');
        grid.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch('products.php?' + requestParams.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error('Request failed');

            const data = await response.json();
            if (myRequestId !== requestId) return;

            grid.innerHTML = data.html;
            grid.classList.remove('is-loading');
            grid.removeAttribute('aria-busy');

            if (countEl) countEl.textContent = data.count;

            if (pushHistory) {
                const clean = cleanParams(params);
                const url = 'products.php' + (clean.toString() ? '?' + clean.toString() : '');
                window.history.pushState({ productsFilter: true }, '', url);
            }

            syncControls(params);

            // Re-bind the pointer spotlight for freshly rendered cards.
            if (typeof window.refreshProductCards === 'function') {
                window.refreshProductCards();
            }
        } catch (error) {
            if (myRequestId !== requestId) return;
            console.error('Product filter failed:', error);
            grid.classList.remove('is-loading');
            grid.removeAttribute('aria-busy');
            if (window.showToast) {
                window.showToast('Could not load products', 'Please try again.', 'remove', 'bx-error-circle');
            }
        }
    }

    function applyPatch(patch, reset) {
        clearTimeout(priceTimer);
        const params = reset ? new URLSearchParams() : currentParams();

        Object.keys(patch).forEach((key) => {
            const value = patch[key];
            const isDefault =
                value === null ||
                value === undefined ||
                value === '' ||
                value === 'all' ||
                (key === 'sort' && value === 'new') ||
                (key === 'max_price' && String(value) === '1000');

            if (isDefault) {
                params.delete(key);
            } else {
                params.set(key, String(value));
            }
        });

        load(params, true);
    }

    // ------------------------------------------------------------
    // Sort dropdown
    // ------------------------------------------------------------
    trigger.addEventListener('click', () => dropdown.classList.toggle('open'));

    optBoxes.forEach((opt) => {
        opt.addEventListener('click', () => {
            const sortVal = opt.dataset.value;
            const activeSort = currentParams().get('sort') || 'new';

            optBoxes.forEach((o) => o.classList.remove('active'));
            opt.classList.add('active');

            const clone = opt.cloneNode(true);
            const icon = clone.querySelector('i');
            if (icon) icon.remove();
            if (labelEl) labelEl.textContent = clone.textContent.trim();

            dropdown.classList.remove('open');

            if (sortVal !== activeSort) {
                applyPatch({ sort: sortVal });
            }
        });
    });

    document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target)) dropdown.classList.remove('open');
    });

    // ------------------------------------------------------------
    // Categories
    // ------------------------------------------------------------
    document.querySelectorAll('.cat-link').forEach((link) => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const cat = categoryOfLink(link);
            const activeCat = currentParams().get('cat_id') || 'all';

            if (cat !== activeCat) {
                applyPatch({ cat_id: cat });
            }
        });
    });

    // ------------------------------------------------------------
    // Max price
    // ------------------------------------------------------------
    if (priceRange) {
        priceRange.addEventListener('input', () => {
            if (priceTitle) priceTitle.textContent = 'Max Price ($' + priceRange.value + ')';

            clearTimeout(priceTimer);
            priceTimer = setTimeout(() => {
                applyPatch({ max_price: priceRange.value });
            }, 350);
        });
    }

    // ------------------------------------------------------------
    // Search
    // ------------------------------------------------------------
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const input = searchForm.querySelector('input[name="search"]');
            applyPatch({ search: input ? input.value.trim() : '' });
        });
    }

    // ------------------------------------------------------------
    // Clear all filters
    // ------------------------------------------------------------
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            if (priceRange) priceRange.value = 1000;
            if (priceTitle) priceTitle.textContent = 'Max Price ($1000)';
            load(new URLSearchParams(), true);
        });
    }

    // ------------------------------------------------------------
    // Browser back / forward
    // ------------------------------------------------------------
    window.addEventListener('popstate', () => {
        load(currentParams(), false);
    });

    // ------------------------------------------------------------
    // Grid / list view toggle
    // ------------------------------------------------------------
    function render() {
        grid.className = 'product-item-con-grid ' + (viewMode === 'grid' ? 'grid-view' : 'list-view');

        document.querySelectorAll('.product-img-g').forEach((img) => {
            img.classList.toggle('img-grid', styleImg === 'grid');
            img.classList.toggle('img-list', styleImg === 'list');
        });
    }

    if (gridBtn) {
        gridBtn.addEventListener('click', () => {
            viewMode = 'grid';
            styleImg = 'grid';
            gridBtn.classList.add('active');
            if (listBtn) listBtn.classList.remove('active');
            render();
        });
    }

    if (listBtn) {
        listBtn.addEventListener('click', () => {
            viewMode = 'list';
            styleImg = 'list';
            listBtn.classList.add('active');
            if (gridBtn) gridBtn.classList.remove('active');
            render();
        });
    }

    // Make sure the controls match the initial server-rendered state.
    syncControls(currentParams());
})();
