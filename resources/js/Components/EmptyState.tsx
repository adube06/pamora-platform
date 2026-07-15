import type { PropsWithChildren } from 'react';

interface Props extends PropsWithChildren {
    title: string;
    description?: string;
}

export default function EmptyState({ title, description, children }: Props) {
    return (
        <div className="rounded-lg border border-dashed border-border p-8 text-center">
            <p className="text-sm font-medium text-text-primary">{title}</p>
            {description && <p className="mt-1 text-sm text-text-secondary">{description}</p>}
            {children && <div className="mt-4">{children}</div>}
        </div>
    );
}
