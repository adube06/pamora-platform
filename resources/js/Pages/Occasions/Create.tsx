import { Form } from '@inertiajs/react';
import Button from '@/Components/Button';
import FormField from '@/Components/FormField';
import Input from '@/Components/Input';
import Select from '@/Components/Select';
import Textarea from '@/Components/Textarea';
import AppLayout from '@/Layouts/AppLayout';

interface Option {
    value: string;
    label: string;
}

interface Props {
    types: Option[];
    visibilities: Option[];
}

export default function Create({ types, visibilities }: Props) {
    return (
        <AppLayout>
            <h1 className="text-lg font-semibold text-text-primary">Create an Occasion</h1>

            <Form action={route('occasions.store')} method="post" className="mt-6 max-w-lg space-y-4">
                {({ errors, processing }) => (
                    <>
                        <FormField label="Title" htmlFor="title" required error={errors.title}>
                            <Input
                                id="title"
                                name="title"
                                type="text"
                                required
                                placeholder="Amina & John's Wedding"
                                invalid={!!errors.title}
                            />
                        </FormField>

                        <FormField label="Type" htmlFor="type" required error={errors.type}>
                            <Select id="type" name="type" required defaultValue="" invalid={!!errors.type}>
                                <option value="" disabled>
                                    Select a type
                                </option>
                                {types.map((type) => (
                                    <option key={type.value} value={type.value}>
                                        {type.label}
                                    </option>
                                ))}
                            </Select>
                        </FormField>

                        <FormField label="Date" htmlFor="primary_date" error={errors.primary_date}>
                            <Input id="primary_date" name="primary_date" type="date" invalid={!!errors.primary_date} />
                        </FormField>

                        <FormField label="Location" htmlFor="location">
                            <Input id="location" name="location" type="text" />
                        </FormField>

                        <FormField label="Visibility" htmlFor="visibility">
                            <Select id="visibility" name="visibility" defaultValue="private">
                                {visibilities.map((visibility) => (
                                    <option key={visibility.value} value={visibility.value}>
                                        {visibility.label}
                                    </option>
                                ))}
                            </Select>
                        </FormField>

                        <FormField label="Description" htmlFor="description">
                            <Textarea id="description" name="description" rows={3} />
                        </FormField>

                        <Button type="submit" loading={processing}>
                            {processing ? 'Creating…' : 'Create Occasion'}
                        </Button>
                    </>
                )}
            </Form>
        </AppLayout>
    );
}
