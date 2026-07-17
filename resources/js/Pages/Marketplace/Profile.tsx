import Badge from '@/Components/Badge';
import Card from '@/Components/Card';
import AppLayout from '@/Layouts/AppLayout';
import type { Vendor } from '@/types/models';

interface Props {
    vendor: Vendor;
}

const STATUS_BADGE: Record<string, { variant: 'success' | 'warning' | 'error'; label: string }> = {
    pending: { variant: 'warning', label: 'Pending Review' },
    verified: { variant: 'success', label: 'Verified' },
    rejected: { variant: 'error', label: 'Rejected' },
};

export default function Profile({ vendor }: Props) {
    const badge = STATUS_BADGE[vendor.verification_status] ?? { variant: 'warning' as const, label: vendor.verification_status };

    return (
        <AppLayout>
            <h1 className="text-lg font-semibold text-text-primary">Your Vendor Profile</h1>

            <Card className="mt-4 max-w-lg">
                <div className="flex items-center justify-between">
                    <p className="text-sm font-medium text-text-primary">{vendor.business_name}</p>
                    <Badge variant={badge.variant}>{badge.label}</Badge>
                </div>

                {vendor.verification_status === 'pending' && (
                    <p className="mt-2 text-xs text-text-secondary">
                        Your application is being reviewed. We'll notify you once a decision has been made.
                    </p>
                )}

                {vendor.verification_status === 'rejected' && (
                    <p className="mt-2 text-xs text-text-secondary">Your application was not approved.</p>
                )}

                <dl className="mt-4 space-y-3 border-t border-border pt-4">
                    <div>
                        <dt className="text-sm font-medium text-text-secondary">Categories</dt>
                        <dd className="mt-1 flex flex-wrap gap-1">
                            {vendor.categories.map((category) => (
                                <Badge key={category}>{category}</Badge>
                            ))}
                        </dd>
                    </div>

                    {vendor.service_areas && vendor.service_areas.length > 0 && (
                        <div>
                            <dt className="text-sm font-medium text-text-secondary">Service Areas</dt>
                            <dd className="text-sm text-text-primary">{vendor.service_areas.join(', ')}</dd>
                        </div>
                    )}

                    <div>
                        <dt className="text-sm font-medium text-text-secondary">Contact Email</dt>
                        <dd className="text-sm text-text-primary">{vendor.contact_email}</dd>
                    </div>

                    <div>
                        <dt className="text-sm font-medium text-text-secondary">Contact Phone</dt>
                        <dd className="text-sm text-text-primary">{vendor.contact_phone}</dd>
                    </div>
                </dl>
            </Card>
        </AppLayout>
    );
}
