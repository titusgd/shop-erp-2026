function initMobileNav() {
    const nav = document.querySelector('[data-mobile-nav]');
    const panel = document.querySelector('[data-mobile-nav-panel]');
    const openButton = document.querySelector('[data-mobile-nav-open]');

    if (!nav || !panel || !openButton) {
        return;
    }

    const closeButtons = nav.querySelectorAll('[data-mobile-nav-close], [data-mobile-nav-backdrop]');

    const setOpen = (open) => {
        nav.classList.toggle('hidden', !open);
        panel.classList.toggle('-translate-x-full', !open);
        panel.classList.toggle('translate-x-0', open);
        openButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        nav.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.classList.toggle('overflow-hidden', open);
    };

    openButton.addEventListener('click', () => setOpen(true));
    closeButtons.forEach((button) => {
        button.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !nav.classList.contains('hidden')) {
            setOpen(false);
        }
    });

    window.addEventListener('resize', () => {
        if (window.matchMedia('(min-width: 1024px)').matches) {
            setOpen(false);
        }
    });
}

function initNavGroups() {
    document.querySelectorAll('[data-nav-group]').forEach((group) => {
        const toggle = group.querySelector('[data-nav-group-toggle]');
        const panel = group.querySelector('[data-nav-group-panel]');
        const chevron = group.querySelector('[data-nav-group-chevron]');

        if (!toggle || !panel) {
            return;
        }

        const setOpen = (open) => {
            group.dataset.open = open ? 'true' : 'false';
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            panel.classList.toggle('hidden', !open);
            chevron?.classList.toggle('rotate-180', open);
        };

        toggle.addEventListener('click', () => {
            setOpen(group.dataset.open !== 'true');
        });
    });
}

initMobileNav();
initNavGroups();
