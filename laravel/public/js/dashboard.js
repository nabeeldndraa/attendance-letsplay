// Menutup dropdown user (Logout) saat klik di luar atau menekan Escape.
(function () {
    const closeMenus = (except) => {
        document.querySelectorAll('.user-menu[open]').forEach((menu) => {
            if (menu !== except) menu.removeAttribute('open');
        });
    };

    document.addEventListener('click', (e) => {
        closeMenus(e.target.closest('.user-menu'));
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeMenus(null);
    });
})();
