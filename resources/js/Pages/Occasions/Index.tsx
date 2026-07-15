import { Link } from '@inertiajs/react';
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
                <h1 className="text-lg font-semibold text-gray-900">My Occasions</h1>
                <Link
                    href={route('occasions.create')}
                    className="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white"
                >
                    Create Occasion
                </Link>
            </div>

            {occasions.length === 0 ? (
                <div className="mt-8 rounded-md border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">
                    You don&apos;t have any Occasions yet. Create your first one to start planning.
                </div>
            ) : (
                <ul className="mt-6 divide-y divide-gray-200 rounded-md border border-gray-200 bg-white">
                    {occasions.map((occasion) => (
                        <li key={occasion.id}>
                            <Link
                                href={route('occasions.show', occasion.slug)}
                                className="flex items-center justify-between px-4 py-3 hover:bg-gray-50"
                            >
                                <div>
                                    <p className="font-medium text-gray-900">{occasion.title}</p>
                                    <p className="text-sm text-gray-500">
                                        {occasion.type} · {occasion.primary_date ?? 'No date set'}
                                    </p>
                                </div>
                                <span className="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                    {occasion.status}
                                </span>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </AppLayout>
    );
}
