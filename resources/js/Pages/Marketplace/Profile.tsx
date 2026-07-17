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
import type { Quotation, Service, Vendor } from '@/types/models';

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

function ServiceCard({ service, categoryOptions, pricingModelOptions }: { service: Service; categoryOptions: Option[]; pricingModelOptions: Option[] }) {
    const [editing, setEditing] = useState(false);
    const pendingQuotations = (service.quotations ?? []).filter((quotation) => quotation.status === 'draft');

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
        </AppLayout>
    );
}
