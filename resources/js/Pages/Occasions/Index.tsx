import { Link } from '@inertiajs/react';
import Badge from '@/Components/Badge';
import EmptyState from '@/Components/EmptyState';
import AppLayout from '@/Layouts/AppLayout';

interface OccasionSummary {
    id: number;
    uuid: string;
    slug: string;
    title: string;
    type: string;
    status: string;
    primary_date: string | null;
}

interface Props {
    occasions: OccasionSummary[];
}

export default function Index({ occasions }: Props) {
    return (
        <AppLayout>
            <div className="flex items-center justify-between">
                <h1 className="text-lg font-semibold text-text-primary">My Occasions</h1>
                <Link
                    href={route('occasions.create')}
                    className="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-hover"
                >
                    Create Occasion
                </Link>
            </div>

            {occasions.length === 0 ? (
                <div className="mt-8">
                    <EmptyState title="You don't have any Occasions yet" description="Create your first one to start planning.">
                        <Link
                            href={route('occasions.create')}
                            className="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-hover"
                        >
                            Create Occasion
                        </Link>
                    </EmptyState>
                </div>
            ) : (
                <ul className="mt-6 divide-y divide-border rounded-lg border border-border bg-surface">
                    {occasions.map((occasion) => (
                        <li key={occasion.id}>
                            <Link
                                href={route('occasions.show', occasion.slug)}
                                className="flex items-center justify-between px-4 py-3 hover:bg-background"
                            >
                                <div>
                                    <p className="font-medium text-text-primary">{occasion.title}</p>
                                    <p className="text-sm text-text-secondary">
                                        {occasion.type} · {occasion.primary_date ?? 'No date set'}
                                    </p>
                                </div>
                                <Badge>{occasion.status}</Badge>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </AppLayout>
    );
}
