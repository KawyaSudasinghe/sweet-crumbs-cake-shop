document.addEventListener('DOMContentLoaded', () => {
    const menu = document.querySelector('[data-mobile-menu]');
    const nav = document.querySelector('[data-nav]');
    if (menu && nav) menu.addEventListener('click', () => nav.classList.toggle('open'));

    document.querySelectorAll('[data-flash]').forEach((el) => {
        setTimeout(() => el.remove(), 5500);
    });

    document.querySelectorAll('[data-confirm]').forEach((el) => {
        el.addEventListener('click', (event) => {
            if (!confirm(el.dataset.confirm)) event.preventDefault();
        });
    });

    document.querySelectorAll('[data-search-filter]').forEach((input) => {
        input.addEventListener('input', () => {
            const query = input.value.trim().toLowerCase();
            document.querySelectorAll('[data-product-card]').forEach((card) => {
                card.hidden = query && !card.textContent.toLowerCase().includes(query);
            });
        });
    });
});
