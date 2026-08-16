/* NepMart — interactivity (vanilla JS, no dependencies) */
(function () {
    'use strict';

    document.addEventListener('click', function (e) {
        // ---- Quantity steppers ----
        var minus = e.target.closest('.js-qty-minus');
        var plus = e.target.closest('.js-qty-plus');
        if (minus || plus) {
            var wrap = (minus || plus).closest('.qty-selector');
            var input = wrap && wrap.querySelector('.js-qty');
            if (input) {
                var step = 1;
                var min = parseInt((minus || plus).getAttribute('data-min') || input.min || 1, 10);
                var max = parseInt((minus || plus).getAttribute('data-max') || input.max || 9999, 10);
                var val = parseInt(input.value || 1, 10) || 1;
                if (minus) val = val - step;
                if (plus) val = val + step;
                if (!isNaN(min)) val = Math.max(min, val);
                if (!isNaN(max) && max > 0) val = Math.min(max, val);
                input.value = val;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        // ---- Product gallery thumbnail swap ----
        var thumb = e.target.closest('.js-thumb');
        if (thumb) {
            var main = document.getElementById('galleryMain');
            if (main) {
                main.src = thumb.src;
                document.querySelectorAll('.js-thumb').forEach(function (t) { t.classList.remove('active'); });
                thumb.classList.add('active');
            }
        }
    });

    // ---- Checkout address selection highlight ----
    document.querySelectorAll('.address-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            var group = radio.closest('.card-body, .collapse, form');
            if (!group) return;
            group.querySelectorAll('.address-option').forEach(function (o) { o.classList.remove('selected'); });
            radio.closest('.address-option').classList.add('selected');
        });
    });

    // ---- Password strength meter ----
    function scorePassword(pw) {
        var score = 0;
        if (!pw) return 0;
        if (pw.length >= 8) score += 1;
        if (pw.length >= 12) score += 1;
        if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) score += 1;
        if (/\d/.test(pw)) score += 1;
        if (/[^A-Za-z0-9]/.test(pw)) score += 1;
        return Math.min(score, 4);
    }
    document.querySelectorAll('.js-password').forEach(function (input) {
        input.addEventListener('input', function () {
            var card = input.closest('form') || document;
            var bar = card.querySelector('.js-strength-bar');
            var txt = card.querySelector('.js-strength-text');
            var s = scorePassword(input.value);
            var labels = ['Too short', 'Weak', 'Fair', 'Good', 'Strong'];
            var colors = ['#e5e7eb', '#ef4444', '#f59e0b', '#3b82f6', '#16a34a'];
            if (bar) {
                bar.style.width = (s === 0 ? 0 : (s / 4) * 100) + '%';
                bar.style.background = colors[s];
            }
            if (txt) txt.textContent = labels[s] + ' · use 8+ chars with letters & numbers.';
        });
    });

    // ---- Auto-submit sort select on category pages ----
    var sortForm = document.getElementById('sortForm');
    if (sortForm) {
        sortForm.querySelectorAll('input[name="min"],input[name="max"],input[name="brand"]').forEach(function (el) {
            el.addEventListener('change', function () { sortForm.submit(); });
        });
    }
})();
