import { useForm } from '@inertiajs/react';
import { useState } from 'react';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import FormField from '@/Components/FormField';
import Textarea from '@/Components/Textarea';
import OccasionWorkspaceLayout from '@/Layouts/OccasionWorkspaceLayout';
import type { Booking, Occasion, Quotation, Service } from '@/types/models';

interface Props {
    occasion: Occasion;
    services: Service[];
    quotations: Quotation[];
    bookings: Booking[];
    canRequestQuotation: boolean;
    canConfirmBooking: boolean;
}

const STATUS_VARIANTS: Record<string, 'success' | 'warning' | 'error' | 'neutral'> = {
    draft: 'warning',
    submitted: 'success',
    accepted: 'success',
    rejected: 'error',
    expired: 'neutral',
    confirmed: 'success',
    completed: 'success',
};

function RequestQuotationForm({ occasion, service, onClose }: { occasion: Occasion; service: Service; onClose: () => void }) {
    const { data, setData, post, processing, errors } = useForm({
        service_id: String(service.id),
        message: '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(route('occasions.quotations.store', occasion.slug), {
            preserveScroll: true,
            onSuccess: onClose,
        });
    }

    return (
        <form onSubmit={submit} className="mt-2 space-y-2">
            <FormField label="Message (optional)" htmlFor={`quotation_message_${service.id}`} error={errors.message}>
                <Textarea
                    id={`quotation_message_${service.id}`}
                    value={data.message}
                    onChange={(e) => setData('message', e.target.value)}
                    rows={2}
                    placeholder="Tell the vendor about your event…"
                />
            </FormField>
            <div className="flex gap-2">
                <Button type="submit" size="sm" loading={processing}>
                    Send Request
                </Button>
                <Button type="button" variant="ghost" size="sm" onClick={onClose}>
                    Cancel
                </Button>
            </div>
        </form>
    );
}

function AcceptRejectButtons({ quotation }: { quotation: Quotation }) {
    const { patch: acceptPatch, processing: accepting } = useForm({});
    const { patch: rejectPatch, processing: rejecting } = useForm({});

    return (
        <div className="flex gap-2">
            <Button size="sm" loading={accepting} onClick={() => acceptPatch(route('quotations.accept', quotation.uuid), { preserveScroll: true })}>
                Accept
            </Button>
            <Button
                variant="danger"
                size="sm"
                loading={rejecting}
                onClick={() => rejectPatch(route('quotations.reject', quotation.uuid), { preserveScroll: true })}
            >
                Reject
            </Button>
        </div>
    );
}

function ConfirmBookingButton({ quotation }: { quotation: Quotation }) {
    const { patch, processing } = useForm({});

    return (
        <Button size="sm" loading={processing} onClick={() => patch(route('quotations.confirm', quotation.uuid), { preserveScroll: true })}>
            Confirm Booking
        </Button>
    );
}

function ServiceCard({ occasion, service, canRequestQuotation }: { occasion: Occasion; service: Service; canRequestQuotation: boolean }) {
    const [requesting, setRequesting] = useState(false);

    return (
        <Card>
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-sm font-medium text-text-primary">{service.name}</p>
                    <p className="text-xs text-text-secondary">{service.vendor?.business_name}</p>
                </div>
                <Badge>{service.category}</Badge>
            </div>
            <div className="mt-2 text-xs text-text-secondary">
                {service.pricing_model === 'fixed' ? `${service.price} ${service.currency}` : 'Custom Quote'}
                {service.estimated_duration && ` · ${service.estimated_duration}`}
            </div>
            {service.description && <p className="mt-2 text-xs text-text-secondary">{service.description}</p>}

            {canRequestQuotation && !requesting && (
                <Button size="sm" className="mt-3" onClick={() => setRequesting(true)}>
                    Request Quotation
                </Button>
            )}

            {requesting && <RequestQuotationForm occasion={occasion} service={service} onClose={() => setRequesting(false)} />}
        </Card>
    );
}

export default function Marketplace({ occasion, services, quotations, bookings, canRequestQuotation, canConfirmBooking }: Props) {
    return (
        <OccasionWorkspaceLayout occasion={occasion} active="marketplace">
            <h2 className="text-sm font-medium text-text-primary">Your Quotation Requests</h2>

            {quotations.length === 0 ? (
                <div className="mt-3">
                    <EmptyState title="No quotation requests yet" description="Request a quotation from a Service below to get started." />
                </div>
            ) : (
                <ul className="mt-3 divide-y divide-border rounded-lg border border-border bg-surface">
                    {quotations.map((quotation) => (
                        <li key={quotation.id} className="flex items-center justify-between px-4 py-3">
                            <div>
                                <p className="text-sm font-medium text-text-primary">{quotation.service?.name}</p>
                                {quotation.quoted_price && (
                                    <p className="text-xs text-text-secondary">
                                        {quotation.quoted_price} {quotation.currency}
                                    </p>
                                )}
                            </div>
                            <div className="flex items-center gap-2">
                                <Badge variant={STATUS_VARIANTS[quotation.status] ?? 'neutral'}>{quotation.status}</Badge>
                                {canRequestQuotation && quotation.status === 'submitted' && <AcceptRejectButtons quotation={quotation} />}
                                {canConfirmBooking && quotation.status === 'accepted' && <ConfirmBookingButton quotation={quotation} />}
                            </div>
                        </li>
                    ))}
                </ul>
            )}

            <h2 className="mt-8 text-sm font-medium text-text-primary">Confirmed Bookings</h2>

            {bookings.length === 0 ? (
                <div className="mt-3">
                    <EmptyState title="No confirmed bookings yet" description="Accepted quotations can be confirmed into Bookings above." />
                </div>
            ) : (
                <ul className="mt-3 divide-y divide-border rounded-lg border border-border bg-surface">
                    {bookings.map((booking) => (
                        <li key={booking.id} className="flex items-center justify-between px-4 py-3">
                            <div>
                                <p className="text-sm font-medium text-text-primary">{booking.service?.name}</p>
                                <p className="text-xs text-text-secondary">
                                    {booking.agreed_price} {booking.currency}
                                </p>
                            </div>
                            <Badge variant={STATUS_VARIANTS[booking.status] ?? 'neutral'}>{booking.status}</Badge>
                        </li>
                    ))}
                </ul>
            )}

            <h2 className="mt-8 text-sm font-medium text-text-primary">Browse Services</h2>

            {services.length === 0 ? (
                <div className="mt-3">
                    <EmptyState title="No services available yet" description="Approved Vendors' published Services will show up here." />
                </div>
            ) : (
                <div className="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {services.map((service) => (
                        <ServiceCard key={service.id} occasion={occasion} service={service} canRequestQuotation={canRequestQuotation} />
                    ))}
                </div>
            )}
        </OccasionWorkspaceLayout>
    );
}
