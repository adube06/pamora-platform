import type { PropsWithChildren } from 'react';
import { cn } from '@/lib/cn';

type Variant = 'success' | 'error' | 'warning' | 'info';

interface Props extends PropsWithChildren {
    variant?: Variant;
    className?: string;
}

const VARIANT_CLASSES: Record<Variant, string> = {
    success: 'bg-success/10 text-success',
    error: 'bg-error/10 text-error',
    warning: 'bg-warning/10 text-warning',
    info: 'bg-info/10 text-info',
};

// Icons are not decorative here — Accessibility Standards require meaning to
// never rely on color alone, so each variant gets a distinct glyph as well.
const VARIANT_ICONS: Record<Variant, string> = {
    success: '✓',
    error: '✕',
    warning: '!',
    info: 'i',
};

export default function Alert({ variant = 'info', className, children }: Props) {
    return (
        <div
            role={variant === 'error' ? 'alert' : 'status'}
            className={cn('flex items-start gap-2 rounded-md px-4 py-2 text-sm', VARIANT_CLASSES[variant], className)}
        >
            <span aria-hidden="true" className="mt-0.5 font-bold">
                {VARIANT_ICONS[variant]}
            </span>
            <span>{children}</span>
        </div>
    );
}
