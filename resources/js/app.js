// Sidebar shell: collapsible on desktop, drawer on mobile.
// The collapsed state is remembered between page loads.
const KEY = 'portal-sidebar';

function setup() {
    const shell = document.querySelector('[data-shell]');
    if (!shell) return;

    const toggle = shell.querySelector('[data-sidebar-toggle]');
    const backdrop = shell.querySelector('[data-sidebar-backdrop]');
    const isMobile = () => window.matchMedia('(max-width: 1023px)').matches;

    const apply = (open) => {
        shell.dataset.sidebarOpen = String(open);
        toggle?.setAttribute('aria-expanded', String(open));
    };

    apply(isMobile() ? false : localStorage.getItem(KEY) !== 'closed');

    toggle?.addEventListener('click', () => {
        const open = shell.dataset.sidebarOpen !== 'true';
        if (!isMobile()) localStorage.setItem(KEY, open ? 'open' : 'closed');
        apply(open);
    });

    backdrop?.addEventListener('click', () => apply(false));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isMobile()) apply(false);
    });
}

document.addEventListener('DOMContentLoaded', setup);
