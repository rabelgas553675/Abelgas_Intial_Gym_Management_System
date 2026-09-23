import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Shared, persistent color mode for all authenticated app layouts.
const themeStorageKey = 'apex-color-theme';
const applyTheme = (theme) => {
    document.documentElement.dataset.theme = theme;
    localStorage.setItem(themeStorageKey, theme);
    const button = document.getElementById('themeToggle');
    if (button) {
        button.textContent = theme === 'dark' ? '☀ Light mode' : '☾ Dark mode';
        button.setAttribute('aria-label', `Switch to ${theme === 'dark' ? 'light' : 'dark'} mode`);
    }
};

applyTheme(localStorage.getItem(themeStorageKey) || 'dark');

if (!document.getElementById('themeToggle')) {
    const button = document.createElement('button');
    button.id = 'themeToggle';
    button.type = 'button';
    button.className = 'theme-toggle';
    button.addEventListener('click', () => {
        applyTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
    });
    document.body.appendChild(button);
    applyTheme(document.documentElement.dataset.theme);
}
