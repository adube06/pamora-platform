import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import FormField from '@/Components/FormField';
import Input from '@/Components/Input';
import Select from '@/Components/Select';
import Textarea from '@/Components/Textarea';
import AppLayout from '@/Layouts/AppLayout';
import type { AvailabilityBlock, Booking, Quotation, RentalItem, Service, Vendor } from '@/types/models';

interface Option {
    value: string;
    label: string;
}

interface Props {
    vendor: Vendor;
    categoryOptions: Option[];
    pricingModelOptions: Option[];
}

const STATUS_BADGE: Record<string, { variant: 'success' | 'warning' | 'error'; label: string }> = {
    pending: { variant: 'warning', label: 'Pending Review' },
    verified: { variant: 'success', label: 'Verified' },
    rejected: { variant: 'error', label: 'Rejected' },
};

function ServiceForm({
    categoryOptions,
    pricingModelOptions,
    initial,
    onSubmit,
    onCancel,
    submitLabel,
}: {
    categoryOptions: Option[];
    pricingModelOptions: Option[];
    initial: { category: string; name: string; description: string; pricing_model: string; price: string; estimated_duration: string };
    onSubmit: (data: typeof initial, helpers: { setErrors: (errors: Record<string, string>) => void; finish: () => void }) => void;
    onCancel: () => void;
    submitLabel: string;
}) {
    const { data, setData, processing, errors, setError, clearErrors } = useForm(initial);
    const [submitting, setSubmitting] = useState(false);

    function submit(e: React.FormEvent) {
        e.preventDefault();
        clearErrors();
        setSubmitting(true);

        onSubmit(data, {
            setErrors: (newErrors) => {
                Object.entries(newErrors).forEach(([field, message]) => setError(field as keyof typeof data, message));
            },
            finish: () => setSubmitting(false),
        });
    }

    return (
        <form onSubmit={submit} className="space-y-2">
            <FormField label="Category" htmlFor="service_category" required error={errors.category}>
                <Select id="service_category" value={data.category} onChange={(e) => setData('category', e.target.value)} invalid={!!errors.category}>
                    {categoryOptions.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </Select>
            </FormField>

            <FormField label="Name" htmlFor="service_name" required error={errors.name}>
                <Input id="service_name" value={data.name} onChange={(e) => setData('name', e.target.value)} required invalid={!!errors.name} />
            </FormField>

            <FormField label="Description" htmlFor="service_description">
                <Textarea id="service_description" value={data.description} onChange={(e) => setData('description', e.target.value)} rows={2} />
            </FormField>

            <FormField label="Pricing" htmlFor="service_pricing_model" required error={errors.pricing_model}>
                <Select
                    id="service_pricing_model"
                    value={data.pricing_model}
                    onChange={(e) => setData('pricing_model', e.target.value)}
                    invalid={!!errors.pricing_model}
                >
                    {pricingModelOptions.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </Select>
            </FormField>

            {data.pricing_model === 'fixed' && (
                <FormField label="Price (TZS)" htmlFor="service_price" required error={errors.price}>
                    <Input
                        id="service_price"
                        type="number"
                        min={0}
                        value={data.price}
                        onChange={(e) => setData('price', e.target.value)}
                        invalid={!!errors.price}
                    />
                </FormField>
            )}

            <FormField label="Estimated Duration" htmlFor="service_duration" helperText="e.g. 2 hours, Full day">
                <Input id="service_duration" value={data.estimated_duration} onChange={(e) => setData('estimated_duration', e.target.value)} />
            </FormField>

            <div className="flex gap-2">
                <Button type="submit" size="sm" loading={processing || submitting}>
                    {submitLabel}
                </Button>
                <Button type="button" variant="ghost" size="sm" onClick={onCancel}>
                    Cancel
                </Button>
            </div>
        </form>
    );
}

function RentalItemForm({
    initial,
    onSubmit,
    onCancel,
    submitLabel,
}: {
    initial: { name: string; description: string; quantity_available: string; unit_price: string };
    onSubmit: (data: typeof initial, helpers: { setErrors: (errors: Record<string, string>) => void; finish: () => void }) => void;
    onCancel: () => void;
    submitLabel: string;
}) {
    const { data, setData, processing, errors, setError, clearErrors } = useForm(initial);
    const [submitting, setSubmitting] = useState(false);

    function submit(e: React.FormEvent) {
        e.preventDefault();
        clearErrors();
        setSubmitting(true);

        onSubmit(data, {
            setErrors: (newErrors) => {
                Object.entries(newErrors).forEach(([field, message]) => setError(field as keyof typeof data, message));
            },
            finish: () => setSubmitting(false),
        });
    }

    return (
        <form onSubmit={submit} className="space-y-2">
            <FormField label="Name" htmlFor="rental_item_name" required error={errors.name}>
                <Input id="rental_item_name" value={data.name} onChange={(e) => setData('name', e.target.value)} required invalid={!!errors.name} />
            </FormField>

            <FormField label="Description" htmlFor="rental_item_description">
                <Textarea
                    id="rental_item_description"
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                    rows={2}
                />
            </FormField>

            <FormField label="Quantity Available" htmlFor="rental_item_quantity" required error={errors.quantity_available}>
                <Input
                    id="rental_item_quantity"
                    type="number"
                    min={0}
                    value={data.quantity_available}
                    onChange={(e) => setData('quantity_available', e.target.value)}
                    invalid={!!errors.quantity_available}
                />
            </FormField>

            <FormField label="Unit Price (TZS)" htmlFor="rental_item_price" required error={errors.unit_price}>
                <Input
                    id="rental_item_price"
                    type="number"
                    min={0}
                    value={data.unit_price}
                    onChange={(e) => setData('unit_price', e.target.value)}
                    invalid={!!errors.unit_price}
                />
            </FormField>

            <div className="flex gap-2">
                <Button type="submit" size="sm" loading={processing || submitting}>
                    {submitLabel}
                </Button>
                <Button type="button" variant="ghost" size="sm" onClick={onCancel}>
                    Cancel
                </Button>
            </div>
        </form>
    );
}

function AvailabilityBlockForm({ vendor, onClose }: { vendor: Vendor; onClose: () => void }) {
    const { data, setData, post, processing, errors } = useForm({
        start_date: '',
        end_date: '',
        reason: '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(route('vendor.availability-blocks.store', vendor.uuid), {
            preserveScroll: true,
            onSuccess: onClose,
        });
    }

    return (
        <form onSubmit={submit} className="space-y-2">
            <FormField label="Start Date" htmlFor="availability_start_date" required error={errors.start_date}>
                <Input
                    id="availability_start_date"
                    type="date"
                    value={data.start_date}
                    onChange={(e) => setData('start_date', e.target.value)}
                    invalid={!!errors.start_date}
                />
            </FormField>

            <FormField label="End Date" htmlFor="availability_end_date" required error={errors.end_date}>
                <Input
                    id="availability_end_date"
                    type="date"
                    value={data.end_date}
                    onChange={(e) => setData('end_date', e.target.value)}
                    invalid={!!errors.end_date}
                />
            </FormField>

            <FormField label="Reason (optional)" htmlFor="availability_reason">
                <Input id="availability_reason" value={data.reason} onChange={(e) => setData('reason', e.target.value)} />
            </FormField>

            <div className="flex gap-2">
                <Button type="submit" size="sm" loading={processing}>
                    Add Block
                </Button>
                <Button type="button" variant="ghost" size="sm" onClick={onClose}>
                    Cancel
                </Button>
            </div>
        </form>
    );
}

function AvailabilityBlockRow({ availabilityBlock }: { availabilityBlock: AvailabilityBlock }) {
    const { delete: destroy, processing } = useForm({});

    return (
        <div className="mt-2 flex items-center justify-between rounded-lg border border-border p-2 text-xs">
            <div>
                <span className="text-text-primary">
                    {availabilityBlock.start_date} – {availabilityBlock.end_date}
                </span>
                {availabilityBlock.reason && <p className="mt-1 text-text-secondary">{availabilityBlock.reason}</p>}
            </div>
            <Button
                variant="danger"
                size="sm"
                loading={processing}
                onClick={() => destroy(route('vendor.availability-blocks.destroy', availabilityBlock.uuid), { preserveScroll: true })}
            >
                Remove
            </Button>
        </div>
    );
}

function AddAvailabilityBlockCard({ vendor }: { vendor: Vendor }) {
    const [adding, setAdding] = useState(false);

    if (!adding) {
        return (
            <Button size="sm" onClick={() => setAdding(true)}>
                Block Availability
            </Button>
        );
    }

    return (
        <Card className="max-w-md">
            <AvailabilityBlockForm vendor={vendor} onClose={() => setAdding(false)} />
        </Card>
    );
}

function SubmitQuotationForm({ quotation, onClose }: { quotation: Quotation; onClose: () => void }) {
    const { data, setData, patch, processing, errors } = useForm({
        quoted_price: '',
        vendor_notes: '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        patch(route('quotations.submit', quotation.uuid), {
            preserveScroll: true,
            onSuccess: onClose,
        });
    }

    return (
        <form onSubmit={submit} className="mt-2 space-y-2 rounded-lg border border-border p-3">
            <FormField label="Quoted Price (TZS)" htmlFor={`quoted_price_${quotation.id}`} required error={errors.quoted_price}>
                <Input
                    id={`quoted_price_${quotation.id}`}
                    type="number"
                    min={0}
                    value={data.quoted_price}
                    onChange={(e) => setData('quoted_price', e.target.value)}
                    invalid={!!errors.quoted_price}
                />
            </FormField>

            <FormField label="Notes" htmlFor={`vendor_notes_${quotation.id}`}>
                <Textarea
                    id={`vendor_notes_${quotation.id}`}
                    value={data.vendor_notes}
                    onChange={(e) => setData('vendor_notes', e.target.value)}
                    rows={2}
                />
            </FormField>

            <div className="flex gap-2">
                <Button type="submit" size="sm" loading={processing}>
                    Submit Quotation
                </Button>
                <Button type="button" variant="ghost" size="sm" onClick={onClose}>
                    Cancel
                </Button>
            </div>
        </form>
    );
}

function PendingQuotation({ quotation }: { quotation: Quotation }) {
    const [responding, setResponding] = useState(false);

    if (responding) {
        return <SubmitQuotationForm quotation={quotation} onClose={() => setResponding(false)} />;
    }

    return (
        <div className="mt-2 flex items-center justify-between rounded-lg border border-border p-2 text-xs">
            <span className="text-text-secondary">{quotation.message ?? 'A quotation request is awaiting your response.'}</span>
            <Button size="sm" onClick={() => setResponding(true)}>
                Respond
            </Button>
        </div>
    );
}

function ConfirmedBooking({ booking }: { booking: Booking }) {
    const { patch, processing } = useForm({});

    return (
        <div className="mt-2 flex items-center justify-between rounded-lg border border-border p-2 text-xs">
            <span className="text-text-secondary">
                {booking.agreed_price} {booking.currency}
            </span>
            <Button size="sm" loading={processing} onClick={() => patch(route('bookings.complete', booking.uuid), { preserveScroll: true })}>
                Mark Complete
            </Button>
        </div>
    );
}

function ServiceCard({ service, categoryOptions, pricingModelOptions }: { service: Service; categoryOptions: Option[]; pricingModelOptions: Option[] }) {
    const [editing, setEditing] = useState(false);
    const pendingQuotations = (service.quotations ?? []).filter((quotation) => quotation.status === 'draft');
    const confirmedBookings = (service.bookings ?? []).filter((booking) => booking.status === 'confirmed');

    if (editing) {
        return (
            <Card>
                <ServiceForm
                    categoryOptions={categoryOptions}
                    pricingModelOptions={pricingModelOptions}
                    initial={{
                        category: service.category,
                        name: service.name,
                        description: service.description ?? '',
                        pricing_model: service.pricing_model,
                        price: service.price ?? '',
                        estimated_duration: service.estimated_duration ?? '',
                    }}
                    submitLabel="Save"
                    onCancel={() => setEditing(false)}
                    onSubmit={(data, { setErrors, finish }) => {
                        router.patch(route('vendor.services.update', service.uuid), data, {
                            preserveScroll: true,
                            onSuccess: () => setEditing(false),
                            onError: setErrors,
                            onFinish: finish,
                        });
                    }}
                />
            </Card>
        );
    }

    return (
        <Card>
            <div className="flex items-center justify-between">
                <p className="text-sm font-medium text-text-primary">{service.name}</p>
                <Button variant="ghost" size="sm" onClick={() => setEditing(true)}>
                    Edit
                </Button>
            </div>
            <div className="mt-1 flex items-center gap-2 text-xs text-text-secondary">
                <Badge>{service.category}</Badge>
                <span>
                    {service.pricing_model === 'fixed' ? `${service.price} ${service.currency}` : 'Custom Quote'}
                </span>
                {service.estimated_duration && <span>· {service.estimated_duration}</span>}
            </div>
            {service.description && <p className="mt-2 text-xs text-text-secondary">{service.description}</p>}

            {pendingQuotations.length > 0 && (
                <div className="mt-3 border-t border-border pt-3">
                    <p className="text-xs font-medium text-text-secondary">Quotation Requests</p>
                    {pendingQuotations.map((quotation) => (
                        <PendingQuotation key={quotation.id} quotation={quotation} />
                    ))}
                </div>
            )}

            {confirmedBookings.length > 0 && (
                <div className="mt-3 border-t border-border pt-3">
                    <p className="text-xs font-medium text-text-secondary">Confirmed Bookings</p>
                    {confirmedBookings.map((booking) => (
                        <ConfirmedBooking key={booking.id} booking={booking} />
                    ))}
                </div>
            )}
        </Card>
    );
}

function RentalItemCard({ rentalItem }: { rentalItem: RentalItem }) {
    const [editing, setEditing] = useState(false);

    if (editing) {
        return (
            <Card>
                <RentalItemForm
                    initial={{
                        name: rentalItem.name,
                        description: rentalItem.description ?? '',
                        quantity_available: String(rentalItem.quantity_available),
                        unit_price: rentalItem.unit_price,
                    }}
                    submitLabel="Save"
                    onCancel={() => setEditing(false)}
                    onSubmit={(data, { setErrors, finish }) => {
                        router.patch(route('vendor.rental-items.update', rentalItem.uuid), data, {
                            preserveScroll: true,
                            onSuccess: () => setEditing(false),
                            onError: setErrors,
                            onFinish: finish,
                        });
                    }}
                />
            </Card>
        );
    }

    return (
        <Card>
            <div className="flex items-center justify-between">
                <p className="text-sm font-medium text-text-primary">{rentalItem.name}</p>
                <Button variant="ghost" size="sm" onClick={() => setEditing(true)}>
                    Edit
                </Button>
            </div>
            <div className="mt-1 flex items-center gap-2 text-xs text-text-secondary">
                <span>{rentalItem.quantity_available} available</span>
                <span>
                    · {rentalItem.unit_price} {rentalItem.currency} each
                </span>
            </div>
            {rentalItem.description && <p className="mt-2 text-xs text-text-secondary">{rentalItem.description}</p>}
        </Card>
    );
}

function AddRentalItemCard({ vendor }: { vendor: Vendor }) {
    const [adding, setAdding] = useState(false);

    if (!adding) {
        return (
            <Button size="sm" onClick={() => setAdding(true)}>
                Add a Rental Item
            </Button>
        );
    }

    return (
        <Card className="max-w-md">
            <RentalItemForm
                initial={{ name: '', description: '', quantity_available: '', unit_price: '' }}
                submitLabel="Publish"
                onCancel={() => setAdding(false)}
                onSubmit={(data, { setErrors, finish }) => {
                    router.post(route('vendor.rental-items.store', vendor.uuid), data, {
                        preserveScroll: true,
                        onSuccess: () => setAdding(false),
                        onError: setErrors,
                        onFinish: finish,
                    });
                }}
            />
        </Card>
    );
}

function AddServiceCard({ vendor, categoryOptions, pricingModelOptions }: { vendor: Vendor; categoryOptions: Option[]; pricingModelOptions: Option[] }) {
    const [adding, setAdding] = useState(false);

    if (!adding) {
        return (
            <Button size="sm" onClick={() => setAdding(true)}>
                Publish a Service
            </Button>
        );
    }

    return (
        <Card className="max-w-md">
            <ServiceForm
                categoryOptions={categoryOptions}
                pricingModelOptions={pricingModelOptions}
                initial={{
                    category: categoryOptions[0]?.value ?? '',
                    name: '',
                    description: '',
                    pricing_model: pricingModelOptions[0]?.value ?? 'fixed',
                    price: '',
                    estimated_duration: '',
                }}
                submitLabel="Publish"
                onCancel={() => setAdding(false)}
                onSubmit={(data, { setErrors, finish }) => {
                    router.post(route('vendor.services.store', vendor.uuid), data, {
                        preserveScroll: true,
                        onSuccess: () => setAdding(false),
                        onError: setErrors,
                        onFinish: finish,
                    });
                }}
            />
        </Card>
    );
}

export default function Profile({ vendor, categoryOptions, pricingModelOptions }: Props) {
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

            {vendor.verification_status === 'verified' && (
                <div className="mt-6 max-w-lg space-y-4">
                    <h2 className="text-sm font-medium text-text-primary">Services</h2>

                    {vendor.services.map((service) => (
                        <ServiceCard
                            key={service.id}
                            service={service}
                            categoryOptions={categoryOptions}
                            pricingModelOptions={pricingModelOptions}
                        />
                    ))}

                    <AddServiceCard vendor={vendor} categoryOptions={categoryOptions} pricingModelOptions={pricingModelOptions} />
                </div>
            )}

            {vendor.verification_status === 'verified' && (
                <div className="mt-6 max-w-lg space-y-4">
                    <h2 className="text-sm font-medium text-text-primary">Rental Inventory</h2>

                    {vendor.rental_items.map((rentalItem) => (
                        <RentalItemCard key={rentalItem.id} rentalItem={rentalItem} />
                    ))}

                    <AddRentalItemCard vendor={vendor} />
                </div>
            )}

            {vendor.verification_status === 'verified' && (
                <div className="mt-6 max-w-lg space-y-4">
                    <h2 className="text-sm font-medium text-text-primary">Availability</h2>

                    {vendor.availability_blocks.map((availabilityBlock) => (
                        <AvailabilityBlockRow key={availabilityBlock.id} availabilityBlock={availabilityBlock} />
                    ))}

                    <AddAvailabilityBlockCard vendor={vendor} />
                </div>
            )}
        </AppLayout>
    );
}
