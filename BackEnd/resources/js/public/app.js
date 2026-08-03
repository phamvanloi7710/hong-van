import $ from 'jquery';
import * as bootstrap from 'bootstrap';

window.$ = window.jQuery = $;
window.bootstrap = bootstrap;
document.documentElement.dataset.publicAssets = 'ready';

const menuToggle = document.querySelector('[data-menu-toggle]');
const primaryNavigation = document.querySelector('[data-primary-navigation]');

const closeMenu = () => {
    if (!(menuToggle instanceof HTMLButtonElement) || !(primaryNavigation instanceof HTMLElement)) {
        return;
    }

    menuToggle.setAttribute('aria-expanded', 'false');
    primaryNavigation.dataset.open = 'false';
};

if (menuToggle instanceof HTMLButtonElement && primaryNavigation instanceof HTMLElement) {
    menuToggle.addEventListener('click', () => {
        const open = menuToggle.getAttribute('aria-expanded') !== 'true';
        menuToggle.setAttribute('aria-expanded', String(open));
        primaryNavigation.dataset.open = String(open);
    });

    primaryNavigation.addEventListener('click', (event) => {
        if (event.target instanceof HTMLAnchorElement) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
            menuToggle.focus();
        }
    });
}
