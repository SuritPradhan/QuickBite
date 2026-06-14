document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('[data-nav-toggle]');
    var nav = document.querySelector('[data-nav]');

    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            nav.classList.toggle('is-open');
        });
    }

    document.querySelectorAll('[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var message = form.getAttribute('data-confirm') || 'Are you sure?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('.qty-input').forEach(function (input) {
        input.addEventListener('input', function () {
            var min = Number(input.getAttribute('min') || 0);
            var max = Number(input.getAttribute('max') || 20);
            var value = Number(input.value || min);
            if (value > max) input.value = String(max);
            if (value < min) input.value = String(min);
        });
    });
});

