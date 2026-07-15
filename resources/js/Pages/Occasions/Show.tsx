import OccasionWorkspaceLayout from '@/Layouts/OccasionWorkspaceLayout';
import type { Occasion, OccasionMember } from '@/types/models';

interface Props {
    occasion: Occasion;
    member: OccasionMember;
}

export default function Show({ occasion, member }: Props) {
    return (
        <OccasionWorkspaceLayout occasion={occasion} active="overview">
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
                    <dd className="text-sm text-gray-900">
                        {member.responsibilities.length > 0 ? member.responsibilities.join(', ') : 'Host'}
                    </dd>
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
