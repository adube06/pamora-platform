import type { PropsWithChildren } from 'react';

interface Props extends PropsWithChildren {
    label: string;
    htmlFor: string;
    required?: boolean;
    error?: string;
    helperText?: string;
}

export default function FormField({ label, htmlFor, required = false, error, helperText, children }: Props) {
    return (
        <div>
            <label htmlFor={htmlFor} className="block text-sm font-medium text-text-primary">
                {label}
                {required && (
                    <span className="text-error" aria-hidden="true">
                        {' '}
                        *
                    </span>
                )}
            </label>

            <div className="mt-1">{children}</div>

            {helperText && !error && <p className="mt-1 text-xs text-text-secondary">{helperText}</p>}
            {error && (
                <p className="mt-1 text-sm text-error" role="alert">
                    {error}
                </p>
            )}
        </div>
    );
}
