import type { PropsWithChildren } from 'react';
import { cn } from '@/lib/cn';

type Variant = 'neutral' | 'success' | 'warning' | 'error' | 'info';

interface Props extends PropsWithChildren {
    variant?: Variant;
    className?: string;
}

const VARIANT_CLASSES: Record<Variant, string> = {
    neutral: 'bg-text-secondary/10 text-text-secondary',
    success: 'bg-success/10 text-success',
    warning: 'bg-warning/10 text-warning',
    error: 'bg-error/10 text-error',
    info: 'bg-info/10 text-info',
};

export default function Badge({ variant = 'neutral', className, children }: Props) {
    return (
        <span className={cn('inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', VARIANT_CLASSES[variant], className)}>
            {children}
        </span>
    );
}
