//===================================== shared motion system (storefront)
// Adds scroll-reveal, a brass scroll-progress bar and the
// pointer-follow spotlight on product cards.
(function () {
    'use strict';

    var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ---------------------------------------------------------------
    // Toast / interactive feedback
    // ---------------------------------------------------------------
    function ensureToastStack() {
        var stack = document.querySelector('.toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.className = 'toast-stack';
            document.body.appendChild(stack);
        }
        return stack;
    }

    window.showToast = function (title, message, type, icon) {
        if (!document.body) return;

        var stack = ensureToastStack();
        var toast = document.createElement('div');
        toast.className = 'ui-toast' + (type ? ' ui-toast--' + type : '');

        var iconBox = document.createElement('div');
        iconBox.className = 'ui-toast__icon';
        var iconEl = document.createElement('i');
        iconEl.className = 'bx ' + (icon || 'bx-check');
        iconBox.appendChild(iconEl);

        var body = document.createElement('div');
        body.className = 'ui-toast__body';
        var titleEl = document.createElement('span');
        titleEl.className = 'ui-toast__title';
        titleEl.textContent = title;
        body.appendChild(titleEl);

        if (message) {
            var msgEl = document.createElement('span');
            msgEl.className = 'ui-toast__msg';
            msgEl.textContent = message;
            body.appendChild(msgEl);
        }

        var bar = document.createElement('span');
        bar.className = 'ui-toast__bar';

        toast.appendChild(iconBox);
        toast.appendChild(body);
        toast.appendChild(bar);
        stack.appendChild(toast);

        // Keep the stack short so rapid clicks don't flood the screen.
        while (stack.children.length > 4) {
            stack.removeChild(stack.firstChild);
        }

        window.requestAnimationFrame(function () {
            toast.classList.add('is-showing');
        });

        window.setTimeout(function () {
            toast.classList.remove('is-showing');
            toast.classList.add('is-hiding');
            window.setTimeout(function () {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 450);
        }, 3100);
    };

    // ---------------------------------------------------------------
    // Scroll progress bar
    // ---------------------------------------------------------------
    var progress = document.createElement('div');
    progress.className = 'scroll-progress';
    document.body.appendChild(progress);

    var progressTick = false;

    function updateProgress() {
        var doc = document.documentElement;
        var max = doc.scrollHeight - window.innerHeight;
        var ratio = max > 0 ? window.scrollY / max : 0;
        if (ratio < 0) ratio = 0;
        if (ratio > 1) ratio = 1;
        progress.style.transform = 'scaleX(' + ratio + ')';
        progressTick = false;
    }

    window.addEventListener('scroll', function () {
        if (!progressTick) {
            window.requestAnimationFrame(updateProgress);
            progressTick = true;
        }
    }, { passive: true });

    updateProgress();

    // ---------------------------------------------------------------
    // Pointer spotlight on product cards
    // ---------------------------------------------------------------
    function bindSpotlight() {
        document.querySelectorAll('.product-item-grid').forEach(function (card) {
            if (card.dataset.spotlight === '1') return;
            card.dataset.spotlight = '1';

            card.addEventListener('pointermove', function (e) {
                var rect = card.getBoundingClientRect();
                var mx = ((e.clientX - rect.left) / rect.width) * 100;
                var my = ((e.clientY - rect.top) / rect.height) * 100;
                card.style.setProperty('--mx', mx.toFixed(1) + '%');
                card.style.setProperty('--my', my.toFixed(1) + '%');
            });

            card.addEventListener('pointerleave', function () {
                card.style.setProperty('--mx', '50%');
                card.style.setProperty('--my', '0%');
            });
        });
    }

    bindSpotlight();
    window.refreshProductCards = bindSpotlight;

    if (prefersReduced) return;

    // ---------------------------------------------------------------
    // Scroll reveal
    // ---------------------------------------------------------------
    var selectors = [
        '.product-head',
        '.show-category-con-header',
        '.product-item-grid',
        '.filter-card',
        '.toolbar',
        '.pd-image-col',
        '.pd-info-col',
        '.reviews-card',
        '.auth-card',
        '.page-title',
        '.account-grid > *',
        '.box-header',
        '.content-box-con',
        '.order-card',
        '.empty-wishlist',
        '.footer-banner',
        '.footer-grid > *'
    ];

    var revealEls = [];

    selectors.forEach(function (selector) {
        document.querySelectorAll(selector).forEach(function (el) {
            if (el.dataset.revealReady === '1') return;
            el.dataset.revealReady = '1';
            revealEls.push(el);
        });
    });

    // Stagger elements that share the same parent so grids cascade in.
    var groups = new Map();
    revealEls.forEach(function (el) {
        var parent = el.parentElement;
        if (!groups.has(parent)) groups.set(parent, []);
        groups.get(parent).push(el);
    });

    groups.forEach(function (list) {
        list.forEach(function (el, index) {
            el.classList.add('js-reveal');
            el.style.setProperty('--reveal-delay', Math.min(index * 70, 350) + 'ms');
            if (el.classList.contains('auth-card')) {
                el.classList.add('reveal-scale');
            }
            if (el.classList.contains('pd-image-col')) {
                el.classList.add('reveal-left');
            }
            if (el.classList.contains('pd-info-col')) {
                el.classList.add('reveal-right');
            }
        });
    });

    if (!('IntersectionObserver' in window)) {
        revealEls.forEach(function (el) {
            el.classList.add('is-visible');
        });
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                var el = entry.target;
                el.classList.add('is-visible');
                observer.unobserve(el);
                // Hand control back to the element's own hover transitions
                // once the entrance animation has finished.
                window.setTimeout(function () {
                    el.classList.remove('js-reveal', 'reveal-scale', 'reveal-left', 'reveal-right', 'is-visible');
                    el.style.removeProperty('--reveal-delay');
                }, 1200);
            }
        });
    }, {
        threshold: 0.12,
        rootMargin: '0px 0px -40px 0px'
    });

    revealEls.forEach(function (el) {
        observer.observe(el);
    });
})();
