import type { PropsWithChildren } from 'react';
import { cn } from '@/lib/cn';

interface Props extends PropsWithChildren {
    title?: string;
    description?: string;
    className?: string;
}

export default function Card({ title, description, className, children }: Props) {
    return (
        <div className={cn('rounded-md border border-border bg-surface p-4', className)}>
            {title && <h3 className="text-sm font-medium text-text-primary">{title}</h3>}
            {description && <p className="mt-1 text-xs text-text-secondary">{description}</p>}
            {(title || description) && children ? <div className="mt-3">{children}</div> : children}
        </div>
    );
}
