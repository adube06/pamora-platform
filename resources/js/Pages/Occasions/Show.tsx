import Card from '@/Components/Card';
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
            <Card className="mb-6 max-w-lg">
                <p className="text-xs text-text-secondary">Readiness</p>
                {readiness.score === null ? (
                    <p className="mt-1 text-sm text-text-secondary">Not enough data yet.</p>
                ) : (
                    <>
                        <p className="mt-1 text-3xl font-semibold text-text-primary">{readiness.score}%</p>
                        <ul className="mt-2 space-y-1">
                            {readiness.signals.map((signal) => (
                                <li key={signal.key} className="flex items-center justify-between text-xs text-text-secondary">
                                    <span>{signal.label}</span>
                                    <span className="font-medium text-text-primary">{signal.value}%</span>
                                </li>
                            ))}
                        </ul>
                    </>
                )}
            </Card>

            <dl className="grid max-w-lg grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt className="text-sm font-medium text-text-secondary">Date</dt>
                    <dd className="text-sm text-text-primary">{occasion.primary_date ?? 'Not set'}</dd>
                </div>
                <div>
                    <dt className="text-sm font-medium text-text-secondary">Location</dt>
                    <dd className="text-sm text-text-primary">{occasion.location ?? 'Not set'}</dd>
                </div>
                <div>
                    <dt className="text-sm font-medium text-text-secondary">Visibility</dt>
                    <dd className="text-sm text-text-primary">{occasion.visibility}</dd>
                </div>
                <div>
                    <dt className="text-sm font-medium text-text-secondary">Your role</dt>
                    <dd className="text-sm text-text-primary">{formatRole(member.role)}</dd>
                </div>
            </dl>

            {occasion.description && (
                <div className="mt-6 max-w-lg">
                    <dt className="text-sm font-medium text-text-secondary">Description</dt>
                    <dd className="mt-1 text-sm text-text-primary">{occasion.description}</dd>
                </div>
            )}
        </OccasionWorkspaceLayout>
    );
}
