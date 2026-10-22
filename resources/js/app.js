import 'bootstrap';

document.querySelectorAll('[data-confirm]').forEach((element) => {
    element.addEventListener('submit', (event) => {
        if (!window.confirm(element.getAttribute('data-confirm'))) {
            event.preventDefault();
        }
    });
});

const colorSrc = (root, file, color, original) => (
    color === 'black' ? original : `${root}/${color}/${file}`
);

document.querySelectorAll('.collection-card').forEach((card) => {
    const photo = card.querySelector('.collection-photo');
    const root = card.closest('[data-color-root]')?.dataset.colorRoot;

    if (!photo || !root) {
        return;
    }

    card.querySelectorAll('.color-swatch').forEach((swatch) => {
        swatch.addEventListener('click', () => {
            const color = swatch.dataset.color;

            card.querySelectorAll('.color-swatch').forEach((button) => {
                const selected = button === swatch;
                button.classList.toggle('is-selected', selected);
                button.setAttribute('aria-pressed', selected ? 'true' : 'false');
            });

            photo.src = colorSrc(root, photo.dataset.file, color, photo.dataset.original);

            const shop = card.querySelector('.collection-shop');

            if (shop) {
                const url = new URL(shop.href, window.location.origin);
                url.searchParams.set('color', color);
                shop.href = `${url.pathname}${url.search}`;
            }
        });
    });
});

const gallery = document.querySelector('.product-gallery');

if (gallery) {
    const root = gallery.dataset.colorRoot || '';
    const main = document.getElementById('product-photo');
    const select = document.getElementById('variant_id');
    let color = gallery.querySelector('.color-swatch.is-selected')?.dataset.color || 'black';

    const paint = (nextColor) => {
        color = nextColor;

        if (root === '') {
            return;
        }

        gallery.querySelectorAll('[data-recolor]').forEach((img) => {
            img.src = colorSrc(root, img.dataset.file, nextColor, img.dataset.original);
        });
    };

    const mark = (nextColor) => {
        gallery.querySelectorAll('.color-swatch').forEach((button) => {
            const selected = button.dataset.color === nextColor;
            button.classList.toggle('is-selected', selected);
            button.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });
    };

    gallery.querySelectorAll('.color-swatch').forEach((swatch) => {
        swatch.addEventListener('click', () => {
            const nextColor = swatch.dataset.color;
            mark(nextColor);

            if (select) {
                const currentSize = select.selectedOptions[0]?.dataset.size;
                const options = [...select.options].filter((option) => option.dataset.color === nextColor && !option.disabled);
                const match = options.find((option) => option.dataset.size === currentSize)
                    || options.find((option) => option.dataset.size === 'm')
                    || options[0];

                if (match) {
                    select.value = match.value;
                }
            }

            paint(nextColor);
        });
    });

    select?.addEventListener('change', () => {
        const nextColor = select.selectedOptions[0]?.dataset.color;

        if (!nextColor) {
            return;
        }

        mark(nextColor);
        paint(nextColor);
    });

    gallery.querySelectorAll('.thumb-btn').forEach((button) => {
        button.addEventListener('click', () => {
            main.dataset.file = button.dataset.file;
            main.dataset.original = button.dataset.original;
            main.alt = button.querySelector('img')?.alt || main.alt;
            main.src = root === '' || color === 'black'
                ? button.dataset.original
                : `${root}/${color}/${button.dataset.file}`;
        });
    });

    if (root !== '' && color !== 'black') {
        paint(color);
    }
}
