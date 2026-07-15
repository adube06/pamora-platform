export type Theme = 'light' | 'dark';

const STORAGE_KEY = 'pamora-theme';

export function getCurrentTheme(): Theme {
    if (typeof document === 'undefined') {
        return 'light';
    }

    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
}

export function applyTheme(theme: Theme): void {
    document.documentElement.classList.remove('light', 'dark');
    document.documentElement.classList.add(theme);
    localStorage.setItem(STORAGE_KEY, theme);
}
