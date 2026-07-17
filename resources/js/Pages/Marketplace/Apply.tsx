import { useForm } from '@inertiajs/react';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import FormField from '@/Components/FormField';
import Input from '@/Components/Input';
import AppLayout from '@/Layouts/AppLayout';

interface CategoryOption {
    value: string;
    label: string;
}

interface Props {
    categoryOptions: CategoryOption[];
}

export default function Apply({ categoryOptions }: Props) {
    const { data, setData, transform, post, processing, errors } = useForm({
        business_name: '',
        categories: [] as string[],
        service_areas_text: '',
        contact_email: '',
        contact_phone: '',
    });

    function toggleCategory(value: string) {
        setData('categories', data.categories.includes(value) ? data.categories.filter((v) => v !== value) : [...data.categories, value]);
    }

    function submit(e: React.FormEvent) {
        e.preventDefault();

        transform((formData) => ({
            ...formData,
            service_areas: formData.service_areas_text
                .split(',')
                .map((area) => area.trim())
                .filter((area) => area !== ''),
        }));

        post(route('vendor.store'));
    }

    return (
        <AppLayout>
            <h1 className="text-lg font-semibold text-text-primary">Become a Vendor</h1>
            <p className="mt-1 text-sm text-text-secondary">
                Apply to list your business on Pamora. Applications are reviewed by our team before you're publicly listed.
            </p>

            <Card className="mt-4 max-w-lg">
                <form onSubmit={submit} className="space-y-4">
                    <FormField label="Business Name" htmlFor="business_name" required error={errors.business_name}>
                        <Input
                            id="business_name"
                            value={data.business_name}
                            onChange={(e) => setData('business_name', e.target.value)}
                            required
                            invalid={!!errors.business_name}
                        />
                    </FormField>

                    <FormField label="Categories" htmlFor="categories" required error={errors.categories}>
                        <div className="space-y-1">
                            {categoryOptions.map((category) => (
                                <label key={category.value} className="flex items-center gap-2 text-sm text-text-primary">
                                    <input
                                        type="checkbox"
                                        className="rounded"
                                        checked={data.categories.includes(category.value)}
                                        onChange={() => toggleCategory(category.value)}
                                    />
                                    {category.label}
                                </label>
                            ))}
                        </div>
                    </FormField>

                    <FormField label="Service Areas" htmlFor="service_areas_text" helperText="Comma-separated, e.g. Dar es Salaam, Arusha">
                        <Input
                            id="service_areas_text"
                            value={data.service_areas_text}
                            onChange={(e) => setData('service_areas_text', e.target.value)}
                        />
                    </FormField>

                    <FormField label="Contact Email" htmlFor="contact_email" required error={errors.contact_email}>
                        <Input
                            id="contact_email"
                            type="email"
                            value={data.contact_email}
                            onChange={(e) => setData('contact_email', e.target.value)}
                            required
                            invalid={!!errors.contact_email}
                        />
                    </FormField>

                    <FormField label="Contact Phone" htmlFor="contact_phone" required error={errors.contact_phone}>
                        <Input
                            id="contact_phone"
                            value={data.contact_phone}
                            onChange={(e) => setData('contact_phone', e.target.value)}
                            required
                            invalid={!!errors.contact_phone}
                        />
                    </FormField>

                    <Button type="submit" loading={processing}>
                        {processing ? 'Submitting…' : 'Submit Application'}
                    </Button>
                </form>
            </Card>
        </AppLayout>
    );
}
