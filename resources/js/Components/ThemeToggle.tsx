import { useState } from 'react';
import { applyTheme, getCurrentTheme } from '@/lib/theme';

interface Props {
    className?: string;
}

export default function ThemeToggle({ className }: Props) {
    const [theme, setTheme] = useState(getCurrentTheme);

    function toggle() {
        const next = theme === 'dark' ? 'light' : 'dark';
        applyTheme(next);
        setTheme(next);
    }

    return (
        <button
            type="button"
            onClick={toggle}
            aria-label={theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme'}
            className={
                className ??
                'inline-flex h-8 w-8 items-center justify-center rounded-lg text-text-secondary hover:bg-background hover:text-text-primary'
            }
        >
            {theme === 'dark' ? (
                <svg viewBox="0 0 24 24" fill="none" className="h-4 w-4" aria-hidden="true">
                    <circle cx="12" cy="12" r="4" stroke="currentColor" strokeWidth="2" />
                    <path
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"
                    />
                </svg>
            ) : (
                <svg viewBox="0 0 24 24" fill="none" className="h-4 w-4" aria-hidden="true">
                    <path
                        fill="currentColor"
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 1020.354 15.354z"
                    />
                </svg>
            )}
        </button>
    );
}
