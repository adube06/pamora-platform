import { cn } from '@/lib/cn';

type Size = 'sm' | 'md';

interface Props {
    name: string;
    size?: Size;
    className?: string;
}

const SIZE_CLASSES: Record<Size, string> = {
    sm: 'h-6 w-6 text-xs',
    md: 'h-9 w-9 text-sm',
};

function initialsFor(name: string): string {
    const parts = name.trim().split(/\s+/).filter(Boolean);
    const initials = parts
        .slice(0, 2)
        .map((part) => part[0])
        .join('');

    return initials.toUpperCase() || '?';
}

export default function Avatar({ name, size = 'sm', className }: Props) {
    return (
        <span
            aria-hidden="true"
            className={cn(
                'inline-flex shrink-0 items-center justify-center rounded-full bg-primary/10 font-medium text-primary',
                SIZE_CLASSES[size],
                className,
            )}
        >
            {initialsFor(name)}
        </span>
    );
}
