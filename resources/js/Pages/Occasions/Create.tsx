import { Form } from '@inertiajs/react';
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
            <h1 className="text-lg font-semibold text-gray-900">Create an Occasion</h1>

            <Form action={route('occasions.store')} method="post" className="mt-6 max-w-lg space-y-4">
                {({ errors, processing }) => (
                    <>
                        <div>
                            <label htmlFor="title" className="block text-sm font-medium text-gray-700">
                                Title
                            </label>
                            <input
                                id="title"
                                name="title"
                                type="text"
                                required
                                placeholder="Amina & John's Wedding"
                                className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                            />
                            {errors.title && <p className="mt-1 text-sm text-red-600">{errors.title}</p>}
                        </div>

                        <div>
                            <label htmlFor="type" className="block text-sm font-medium text-gray-700">
                                Type
                            </label>
                            <select
                                id="type"
                                name="type"
                                required
                                defaultValue=""
                                className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                            >
                                <option value="" disabled>
                                    Select a type
                                </option>
                                {types.map((type) => (
                                    <option key={type.value} value={type.value}>
                                        {type.label}
                                    </option>
                                ))}
                            </select>
                            {errors.type && <p className="mt-1 text-sm text-red-600">{errors.type}</p>}
                        </div>

                        <div>
                            <label htmlFor="primary_date" className="block text-sm font-medium text-gray-700">
                                Date
                            </label>
                            <input
                                id="primary_date"
                                name="primary_date"
                                type="date"
                                className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                            />
                            {errors.primary_date && <p className="mt-1 text-sm text-red-600">{errors.primary_date}</p>}
                        </div>

                        <div>
                            <label htmlFor="location" className="block text-sm font-medium text-gray-700">
                                Location
                            </label>
                            <input
                                id="location"
                                name="location"
                                type="text"
                                className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                            />
                        </div>

                        <div>
                            <label htmlFor="visibility" className="block text-sm font-medium text-gray-700">
                                Visibility
                            </label>
                            <select
                                id="visibility"
                                name="visibility"
                                defaultValue="private"
                                className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                            >
                                {visibilities.map((visibility) => (
                                    <option key={visibility.value} value={visibility.value}>
                                        {visibility.label}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label htmlFor="description" className="block text-sm font-medium text-gray-700">
                                Description
                            </label>
                            <textarea
                                id="description"
                                name="description"
                                rows={3}
                                className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                        >
                            {processing ? 'Creating…' : 'Create Occasion'}
                        </button>
                    </>
                )}
            </Form>
        </AppLayout>
    );
}
