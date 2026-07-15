import { cn } from '@/lib/cn';

type Size = 'sm' | 'md';
type Variant = 'gradient' | 'inverted';

interface Props {
    size?: Size;
    variant?: Variant;
    className?: string;
}

const SIZE_CLASSES: Record<Size, string> = {
    sm: 'h-8 w-8 rounded-lg text-sm',
    md: 'h-11 w-11 rounded-xl text-lg',
};

const VARIANT_CLASSES: Record<Variant, string> = {
    gradient: 'bg-gradient-to-br from-primary to-secondary text-white',
    // For use on top of the primary/secondary gradient panel itself
    // (GuestLayout), where the gradient variant would blend in.
    inverted: 'bg-white/15 text-white ring-1 ring-inset ring-white/30',
};

export default function BrandMark({ size = 'sm', variant = 'gradient', className }: Props) {
    return (
        <span
            aria-hidden="true"
            className={cn('inline-flex shrink-0 items-center justify-center font-bold', SIZE_CLASSES[size], VARIANT_CLASSES[variant], className)}
        >
            P
        </span>
    );
}
