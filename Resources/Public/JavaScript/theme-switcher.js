// Theme preview switcher (demo site only), see Templates/ThemeSwitcher/Banner.html.
// The <details> works without this; it adds Escape / click outside to close, keeps the
// panel open across a theme switch, and stops the attention pulse once it was opened.
(() => {
    const switcher = document.querySelector('.conn-demo-themes');
    if (!switcher) {
        return;
    }
    const KEEP_OPEN = 'conn-demo-themes-open';
    const SEEN = 'conn-demo-themes-seen';
    const storage = (area, action, key, value) => {
        try {
            return window[area][action](key, value);
        } catch (e) {
            return null;
        }
    };

    if (storage('localStorage', 'getItem', SEEN)) {
        switcher.classList.add('is-seen');
    }
    if (storage('sessionStorage', 'getItem', KEEP_OPEN)) {
        storage('sessionStorage', 'removeItem', KEEP_OPEN);
        switcher.classList.add('is-restored'); // no open animation after a theme switch
        switcher.open = true;
    }

    switcher.addEventListener('toggle', () => {
        if (switcher.open) {
            switcher.classList.add('is-seen');
            storage('localStorage', 'setItem', SEEN, '1');
        } else {
            switcher.classList.remove('is-restored');
        }
    });
    switcher.addEventListener('click', (event) => {
        if (event.target.closest('.conn-demo-themes__theme')) {
            storage('sessionStorage', 'setItem', KEEP_OPEN, '1');
        }
    });
    document.addEventListener('click', (event) => {
        if (switcher.open && !switcher.contains(event.target)) {
            switcher.open = false;
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && switcher.open) {
            switcher.open = false;
            switcher.querySelector('summary').focus();
        }
    });
})();
