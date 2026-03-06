import './bootstrap';

const THEME_STORAGE_KEY = 'lgu-theme-preference';

const isThemeValue = (value) => value === 'light' || value === 'dark';

const getPreferredTheme = () => {
    const storedTheme = window.localStorage.getItem(THEME_STORAGE_KEY);

    if (isThemeValue(storedTheme)) {
        return storedTheme;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

const updateThemeButtons = (theme) => {
    document.querySelectorAll('[data-theme-choice]').forEach((button) => {
        const isActive = button.getAttribute('data-theme-choice') === theme;
        button.setAttribute('aria-pressed', String(isActive));
        button.dataset.active = isActive ? 'true' : 'false';
    });
};

const applyTheme = (theme) => {
    if (!isThemeValue(theme)) {
        return;
    }

    document.documentElement.dataset.theme = theme;
    document.documentElement.classList.toggle('dark', theme === 'dark');
    document.documentElement.style.colorScheme = theme;
    updateThemeButtons(theme);
};

const persistAndApplyTheme = (theme) => {
    if (!isThemeValue(theme)) {
        return;
    }

    window.localStorage.setItem(THEME_STORAGE_KEY, theme);
    applyTheme(theme);
};

document.addEventListener('DOMContentLoaded', () => {
    applyTheme(getPreferredTheme());

    document.addEventListener('click', (event) => {
        const themeButton = event.target.closest('[data-theme-choice]');
        if (!themeButton) {
            return;
        }

        persistAndApplyTheme(themeButton.getAttribute('data-theme-choice'));
    });
});
