import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { cn } from '@/lib/cn';
import type { Occasion } from '@/types/models';

interface Props extends PropsWithChildren {
    occasion: Occasion;
    active: 'overview' | 'committee' | 'planning' | 'finance' | 'communication';
}

const tabs = [
    { key: 'overview', label: 'Overview', routeName: 'occasions.show' },
    { key: 'committee', label: 'Committee', routeName: 'occasions.committee' },
    { key: 'planning', label: 'Planning', routeName: 'occasions.planning' },
    { key: 'finance', label: 'Finance', routeName: 'occasions.finance' },
    { key: 'communication', label: 'Communication', routeName: 'occasions.communication' },
] as const;

export default function OccasionWorkspaceLayout({ occasion, active, children }: Props) {
    return (
        <AppLayout>
            <div className="mb-6">
                <h1 className="text-lg font-semibold text-text-primary">{occasion.title}</h1>
                <p className="text-sm text-text-secondary">
                    {occasion.type} · {occasion.status}
                </p>
            </div>

            <div className="border-b border-border">
                <nav className="-mb-px flex gap-6">
                    {tabs.map((tab) => (
                        <Link
                            key={tab.key}
                            href={route(tab.routeName, occasion.slug)}
                            className={cn(
                                'border-b-2 px-1 py-2 text-sm font-medium',
                                active === tab.key
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-text-secondary hover:text-text-primary',
                            )}
                        >
                            {tab.label}
                        </Link>
                    ))}
                </nav>
            </div>

            <div className="mt-6">{children}</div>
        </AppLayout>
    );
}
