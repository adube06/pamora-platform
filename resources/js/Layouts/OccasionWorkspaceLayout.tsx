import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import type { Occasion } from '@/types/models';

interface Props extends PropsWithChildren {
    occasion: Occasion;
    active: 'overview' | 'committee' | 'planning';
}

const tabs = [
    { key: 'overview', label: 'Overview', routeName: 'occasions.show' },
    { key: 'committee', label: 'Committee', routeName: 'occasions.committee' },
    { key: 'planning', label: 'Planning', routeName: 'occasions.planning' },
] as const;

export default function OccasionWorkspaceLayout({ occasion, active, children }: Props) {
    return (
        <AppLayout>
            <div className="mb-6">
                <h1 className="text-lg font-semibold text-gray-900">{occasion.title}</h1>
                <p className="text-sm text-gray-500">
                    {occasion.type} · {occasion.status}
                </p>
            </div>

            <div className="border-b border-gray-200">
                <nav className="-mb-px flex gap-6">
                    {tabs.map((tab) => (
                        <Link
                            key={tab.key}
                            href={route(tab.routeName, occasion.slug)}
                            className={`border-b-2 px-1 py-2 text-sm font-medium ${
                                active === tab.key
                                    ? 'border-gray-900 text-gray-900'
                                    : 'border-transparent text-gray-500 hover:text-gray-700'
                            }`}
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
