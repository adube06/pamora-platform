import type { SelectHTMLAttributes } from 'react';
import { cn } from '@/lib/cn';

interface Props extends SelectHTMLAttributes<HTMLSelectElement> {
    invalid?: boolean;
}

export default function Select({ invalid = false, className, children, ...props }: Props) {
    return (
        <select
            {...props}
            aria-invalid={invalid || undefined}
            className={cn(
                'block w-full rounded-lg border bg-surface px-3 py-2 text-sm text-text-primary focus:outline-2 focus:outline-offset-2 focus:outline-primary disabled:cursor-not-allowed disabled:bg-background disabled:text-disabled',
                invalid ? 'border-error' : 'border-border',
                className,
            )}
        >
            {children}
        </select>
    );
}
