import type { TextareaHTMLAttributes } from 'react';
import { cn } from '@/lib/cn';

interface Props extends TextareaHTMLAttributes<HTMLTextAreaElement> {
    invalid?: boolean;
}

export default function Textarea({ invalid = false, className, ...props }: Props) {
    return (
        <textarea
            {...props}
            aria-invalid={invalid || undefined}
            className={cn(
                'block w-full rounded-lg border bg-surface px-3 py-2 text-sm text-text-primary placeholder:text-text-secondary focus:outline-2 focus:outline-offset-2 focus:outline-primary disabled:cursor-not-allowed disabled:bg-background disabled:text-disabled',
                invalid ? 'border-error' : 'border-border',
                className,
            )}
        />
    );
}
