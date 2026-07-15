import OccasionWorkspaceLayout from '@/Layouts/OccasionWorkspaceLayout';
import type { Occasion, OccasionMember, Readiness } from '@/types/models';

interface Props {
    occasion: Occasion;
    member: OccasionMember;
    readiness: Readiness;
}

function formatRole(role: string): string {
    return role
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

export default function Show({ occasion, member, readiness }: Props) {
    return (
        <OccasionWorkspaceLayout occasion={occasion} active="overview">
            <div className="mb-6 max-w-lg rounded-md border border-gray-200 bg-white p-4">
                <p className="text-xs text-gray-500">Readiness</p>
                {readiness.score === null ? (
                    <p className="mt-1 text-sm text-gray-500">Not enough data yet.</p>
                ) : (
                    <>
                        <p className="mt-1 text-3xl font-semibold text-gray-900">{readiness.score}%</p>
                        <ul className="mt-2 space-y-1">
                            {readiness.signals.map((signal) => (
                                <li key={signal.key} className="flex items-center justify-between text-xs text-gray-500">
                                    <span>{signal.label}</span>
                                    <span className="font-medium text-gray-700">{signal.value}%</span>
                                </li>
                            ))}
                        </ul>
                    </>
                )}
            </div>

            <dl className="grid max-w-lg grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt className="text-sm font-medium text-gray-500">Date</dt>
                    <dd className="text-sm text-gray-900">{occasion.primary_date ?? 'Not set'}</dd>
                </div>
                <div>
                    <dt className="text-sm font-medium text-gray-500">Location</dt>
                    <dd className="text-sm text-gray-900">{occasion.location ?? 'Not set'}</dd>
                </div>
                <div>
                    <dt className="text-sm font-medium text-gray-500">Visibility</dt>
                    <dd className="text-sm text-gray-900">{occasion.visibility}</dd>
                </div>
                <div>
                    <dt className="text-sm font-medium text-gray-500">Your role</dt>
                    <dd className="text-sm text-gray-900">{formatRole(member.role)}</dd>
                </div>
            </dl>

            {occasion.description && (
                <div className="mt-6 max-w-lg">
                    <dt className="text-sm font-medium text-gray-500">Description</dt>
                    <dd className="mt-1 text-sm text-gray-900">{occasion.description}</dd>
                </div>
            )}
        </OccasionWorkspaceLayout>
    );
}
