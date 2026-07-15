import { Form } from '@inertiajs/react';
import { useState } from 'react';
import OccasionWorkspaceLayout from '@/Layouts/OccasionWorkspaceLayout';
import type { Contribution, ContributionSummary, Occasion } from '@/types/models';

interface Props {
    occasion: Occasion;
    contributions: Contribution[];
    summary: ContributionSummary;
    canRecordContribution: boolean;
}

const METHOD_LABELS: Record<string, string> = {
    cash: 'Cash',
    mobile_money: 'Mobile Money',
    bank_transfer: 'Bank Transfer',
    other: 'Other',
};

export default function Finance({ occasion, contributions, summary, canRecordContribution }: Props) {
    const [showForm, setShowForm] = useState(false);

    return (
        <OccasionWorkspaceLayout occasion={occasion} active="finance">
            <div className="grid grid-cols-2 gap-4 sm:grid-cols-2">
                <div className="rounded-md border border-gray-200 bg-white p-4">
                    <p className="text-xs text-gray-500">Total Received</p>
                    <p className="mt-1 text-2xl font-semibold text-gray-900">{summary.total_received} TZS</p>
                </div>
                <div className="rounded-md border border-gray-200 bg-white p-4">
                    <p className="text-xs text-gray-500">Contributions</p>
                    <p className="mt-1 text-2xl font-semibold text-gray-900">{summary.contribution_count}</p>
                </div>
            </div>

            <div className="mt-6 flex items-center justify-between">
                <h2 className="text-sm font-medium text-gray-900">Contributions</h2>
                {canRecordContribution && (
                    <button
                        onClick={() => setShowForm((v) => !v)}
                        className="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white"
                    >
                        {showForm ? 'Cancel' : 'Record Contribution'}
                    </button>
                )}
            </div>

            {showForm && (
                <Form
                    action={route('occasions.contributions.store', occasion.slug)}
                    method="post"
                    resetOnSuccess
                    onSuccess={() => setShowForm(false)}
                    className="mt-4 max-w-md space-y-3 rounded-md border border-gray-200 bg-white p-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <div>
                                <label htmlFor="contributor_name" className="block text-sm font-medium text-gray-700">
                                    Contributor Name
                                </label>
                                <input
                                    id="contributor_name"
                                    name="contributor_name"
                                    type="text"
                                    required
                                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                />
                                {errors.contributor_name && (
                                    <p className="mt-1 text-sm text-red-600">{errors.contributor_name}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="contributor_phone" className="block text-sm font-medium text-gray-700">
                                    Phone (optional)
                                </label>
                                <input
                                    id="contributor_phone"
                                    name="contributor_phone"
                                    type="text"
                                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                />
                                {errors.contributor_phone && (
                                    <p className="mt-1 text-sm text-red-600">{errors.contributor_phone}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="amount" className="block text-sm font-medium text-gray-700">
                                    Amount
                                </label>
                                <input
                                    id="amount"
                                    name="amount"
                                    type="number"
                                    min="1"
                                    step="0.01"
                                    required
                                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                />
                                {errors.amount && <p className="mt-1 text-sm text-red-600">{errors.amount}</p>}
                            </div>

                            <div>
                                <label htmlFor="method" className="block text-sm font-medium text-gray-700">
                                    Method
                                </label>
                                <select
                                    id="method"
                                    name="method"
                                    defaultValue="cash"
                                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                >
                                    <option value="cash">Cash</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="other">Other</option>
                                </select>
                                {errors.method && <p className="mt-1 text-sm text-red-600">{errors.method}</p>}
                            </div>

                            <div>
                                <label htmlFor="contributed_at" className="block text-sm font-medium text-gray-700">
                                    Date
                                </label>
                                <input
                                    id="contributed_at"
                                    name="contributed_at"
                                    type="date"
                                    required
                                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                />
                                {errors.contributed_at && (
                                    <p className="mt-1 text-sm text-red-600">{errors.contributed_at}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="message" className="block text-sm font-medium text-gray-700">
                                    Message (optional)
                                </label>
                                <textarea
                                    id="message"
                                    name="message"
                                    rows={2}
                                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                />
                                {errors.message && <p className="mt-1 text-sm text-red-600">{errors.message}</p>}
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            >
                                {processing ? 'Recording…' : 'Record Contribution'}
                            </button>
                        </>
                    )}
                </Form>
            )}

            {contributions.length === 0 ? (
                <div className="mt-6 rounded-md border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">
                    No contributions recorded yet.
                </div>
            ) : (
                <ul className="mt-4 divide-y divide-gray-200 rounded-md border border-gray-200 bg-white">
                    {contributions.map((contribution) => (
                        <li key={contribution.id} className="flex items-center justify-between px-4 py-3">
                            <div>
                                <p className="text-sm font-medium text-gray-900">{contribution.contributor_name}</p>
                                <p className="text-xs text-gray-500">
                                    {METHOD_LABELS[contribution.method] ?? contribution.method} · {contribution.contributed_at}
                                    {contribution.message && ` · "${contribution.message}"`}
                                </p>
                            </div>
                            <p className="text-sm font-semibold text-gray-900">
                                {contribution.amount} {contribution.currency}
                            </p>
                        </li>
                    ))}
                </ul>
            )}
        </OccasionWorkspaceLayout>
    );
}
