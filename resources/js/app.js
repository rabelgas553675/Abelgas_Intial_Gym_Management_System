import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/* =========================================================================
   GLOBAL THEME SYSTEM
   One centralized, app-wide theme state (Light / Dark).

   - Source of truth: the `dark` class on <html> (document.documentElement).
   - Persistence:     a single localStorage key shared by every page/layout.
   - There is NO per-role state: Admin, Staff, Instructor, Member and guest
     pages all read and write the same key.

   The theme is applied immediately when this module runs (before Alpine
   starts and before the toggle button is built) to minimise any flash of
   the wrong theme.
   ========================================================================= */
const THEME_STORAGE_KEY = 'apex-color-theme';
const THEME_LIGHT = 'light';
const THEME_DARK = 'dark';
const DEFAULT_THEME = THEME_DARK; // unchanged default from the previous behavior

const isValidTheme = (theme) => theme === THEME_LIGHT || theme === THEME_DARK;

// localStorage can throw (private mode, blocked storage) - never let that break the app.
const readStoredTheme = () => {
    try {
        const stored = localStorage.getItem(THEME_STORAGE_KEY);
        return isValidTheme(stored) ? stored : null;
    } catch (e) {
        return null;
    }
};

const writeStoredTheme = (theme) => {
    try {
        localStorage.setItem(THEME_STORAGE_KEY, theme);
    } catch (e) {
        /* storage unavailable - theme still applies for this page view */
    }
};

const getTheme = () =>
    document.documentElement.classList.contains('dark') ? THEME_DARK : THEME_LIGHT;

const updateToggleButton = (theme) => {
    const button = document.getElementById('themeToggle');
    if (!button) return;

    button.textContent = theme === THEME_DARK ? '☀ Light mode' : '☾ Dark mode';
    button.setAttribute('aria-label', `Switch to ${theme === THEME_DARK ? 'light' : 'dark'} mode`);
};

// Applies a theme to the document. `persist` controls whether it is saved.
const applyTheme = (theme, persist = true) => {
    if (!isValidTheme(theme)) theme = DEFAULT_THEME;

    const root = document.documentElement;

    // Canonical mechanism: `dark` class on <html> (read by `html.dark { ... }` in app.css).
    if (theme === THEME_DARK) {
        root.classList.add('dark');
    } else {
        root.classList.remove('dark');
    }

    // Legacy mirror: older :root[data-theme="light"] rules in app.css and the
    // per-layout styles still key off this attribute. Derived from the class,
    // never an independent state.
    root.dataset.theme = theme;

    if (persist) writeStoredTheme(theme);

    updateToggleButton(theme);

    window.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));
};

const setTheme = (theme) => applyTheme(theme, true);
const toggleTheme = () => setTheme(getTheme() === THEME_DARK ? THEME_LIGHT : THEME_DARK);

// Restore the saved theme right away (falls back to the default without saving it).
applyTheme(readStoredTheme() || DEFAULT_THEME, false);

// Public API so any Blade view / Alpine component can use the same single state.
window.theme = { get: getTheme, set: setTheme, toggle: toggleTheme };

// Keep multiple open tabs/pages in sync.
window.addEventListener('storage', (event) => {
    if (event.key === THEME_STORAGE_KEY && isValidTheme(event.newValue)) {
        applyTheme(event.newValue, false);
    }
});

Alpine.start();

// Floating toggle button (created once, only if a layout has not provided its own #themeToggle).
const mountThemeToggle = () => {
    let button = document.getElementById('themeToggle');

    if (!button) {
        button = document.createElement('button');
        button.id = 'themeToggle';
        button.type = 'button';
        button.className = 'theme-toggle';
        document.body.appendChild(button);
    }

    if (!button.dataset.themeBound) {
        button.dataset.themeBound = 'true';
        button.addEventListener('click', toggleTheme);
    }

    updateToggleButton(getTheme());
};

if (document.body) {
    mountThemeToggle();
} else {
    document.addEventListener('DOMContentLoaded', mountThemeToggle);
}
