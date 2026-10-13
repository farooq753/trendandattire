import 'bootstrap';

document.querySelectorAll('[data-confirm]').forEach((element) => {
    element.addEventListener('submit', (event) => {
        if (!window.confirm(element.getAttribute('data-confirm'))) {
            event.preventDefault();
        }
    });
});
