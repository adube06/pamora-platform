import { Form } from '@inertiajs/react';
import { useState } from 'react';
import OccasionWorkspaceLayout from '@/Layouts/OccasionWorkspaceLayout';
import type { Budget, BudgetSummary, Contribution, Expense, Occasion } from '@/types/models';

interface Props {
    occasion: Occasion;
    budget: Budget | null;
    contributions: Contribution[];
    expenses: Expense[];
    summary: BudgetSummary;
    canRecordContribution: boolean;
    canViewBudget: boolean;
    canEditBudget: boolean;
    canRecordExpense: boolean;
}

const METHOD_LABELS: Record<string, string> = {
    cash: 'Cash',
    mobile_money: 'Mobile Money',
    bank_transfer: 'Bank Transfer',
    other: 'Other',
};

const HEALTH_LABELS: Record<string, string> = {
    under_budget: 'Under Budget',
    on_track: 'On Track',
    at_risk: 'At Risk',
    over_budget: 'Over Budget',
};

const HEALTH_CLASSES: Record<string, string> = {
    under_budget: 'bg-blue-100 text-blue-800',
    on_track: 'bg-green-100 text-green-800',
    at_risk: 'bg-yellow-100 text-yellow-800',
    over_budget: 'bg-red-100 text-red-800',
};

export default function Finance({
    occasion,
    budget,
    contributions,
    expenses,
    summary,
    canRecordContribution,
    canViewBudget,
    canEditBudget,
    canRecordExpense,
}: Props) {
    const [showContributionForm, setShowContributionForm] = useState(false);
    const [showBudgetForm, setShowBudgetForm] = useState(false);
    const [showExpenseForm, setShowExpenseForm] = useState(false);

    return (
        <OccasionWorkspaceLayout occasion={occasion} active="finance">
            {canViewBudget && (
                <div className="mb-8">
                    <div className="flex items-center justify-between">
                        <h2 className="text-sm font-medium text-gray-900">Budget</h2>
                        {budget && summary.health && (
                            <span className={`rounded-full px-2.5 py-0.5 text-xs font-medium ${HEALTH_CLASSES[summary.health]}`}>
                                {HEALTH_LABELS[summary.health] ?? summary.health}
                            </span>
                        )}
                    </div>

                    {budget ? (
                        <>
                            <div className="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div className="rounded-md border border-gray-200 bg-white p-4">
                                    <p className="text-xs text-gray-500">Planned</p>
                                    <p className="mt-1 text-xl font-semibold text-gray-900">
                                        {summary.planned_amount} {budget.currency}
                                    </p>
                                </div>
                                <div className="rounded-md border border-gray-200 bg-white p-4">
                                    <p className="text-xs text-gray-500">Received</p>
                                    <p className="mt-1 text-xl font-semibold text-gray-900">
                                        {summary.total_received} {budget.currency}
                                    </p>
                                    <p className="text-xs text-gray-400">{summary.funding_progress}% funded</p>
                                </div>
                                <div className="rounded-md border border-gray-200 bg-white p-4">
                                    <p className="text-xs text-gray-500">Spent</p>
                                    <p className="mt-1 text-xl font-semibold text-gray-900">
                                        {summary.total_expense} {budget.currency}
                                    </p>
                                    <p className="text-xs text-gray-400">{summary.spending_progress}% spent</p>
                                </div>
                                <div className="rounded-md border border-gray-200 bg-white p-4">
                                    <p className="text-xs text-gray-500">Remaining</p>
                                    <p className="mt-1 text-xl font-semibold text-gray-900">
                                        {summary.remaining_budget} {budget.currency}
                                    </p>
                                </div>
                            </div>

                            <div className="mt-6 flex items-center justify-between">
                                <h3 className="text-sm font-medium text-gray-900">Expenses</h3>
                                {canRecordExpense && (
                                    <button
                                        onClick={() => setShowExpenseForm((v) => !v)}
                                        className="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white"
                                    >
                                        {showExpenseForm ? 'Cancel' : 'Record Expense'}
                                    </button>
                                )}
                            </div>

                            {showExpenseForm && (
                                <Form
                                    action={route('occasions.expenses.store', occasion.slug)}
                                    method="post"
                                    resetOnSuccess
                                    onSuccess={() => setShowExpenseForm(false)}
                                    className="mt-4 max-w-md space-y-3 rounded-md border border-gray-200 bg-white p-4"
                                >
                                    {({ errors, processing }) => (
                                        <>
                                            <div>
                                                <label htmlFor="budget_category_id" className="block text-sm font-medium text-gray-700">
                                                    Category
                                                </label>
                                                <select
                                                    id="budget_category_id"
                                                    name="budget_category_id"
                                                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                                >
                                                    {budget.categories.map((category) => (
                                                        <option key={category.id} value={category.id}>
                                                            {category.name}
                                                        </option>
                                                    ))}
                                                </select>
                                                {errors.budget_category_id && (
                                                    <p className="mt-1 text-sm text-red-600">{errors.budget_category_id}</p>
                                                )}
                                            </div>

                                            <div>
                                                <label htmlFor="expense_amount" className="block text-sm font-medium text-gray-700">
                                                    Amount
                                                </label>
                                                <input
                                                    id="expense_amount"
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
                                                <label htmlFor="spent_at" className="block text-sm font-medium text-gray-700">
                                                    Date
                                                </label>
                                                <input
                                                    id="spent_at"
                                                    name="spent_at"
                                                    type="date"
                                                    required
                                                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                                />
                                                {errors.spent_at && <p className="mt-1 text-sm text-red-600">{errors.spent_at}</p>}
                                            </div>

                                            <div>
                                                <label htmlFor="description" className="block text-sm font-medium text-gray-700">
                                                    Description (optional)
                                                </label>
                                                <textarea
                                                    id="description"
                                                    name="description"
                                                    rows={2}
                                                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                                />
                                                {errors.description && (
                                                    <p className="mt-1 text-sm text-red-600">{errors.description}</p>
                                                )}
                                            </div>

                                            <button
                                                type="submit"
                                                disabled={processing}
                                                className="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                                            >
                                                {processing ? 'Recording…' : 'Record Expense'}
                                            </button>
                                        </>
                                    )}
                                </Form>
                            )}

                            {expenses.length === 0 ? (
                                <div className="mt-4 rounded-md border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500">
                                    No expenses recorded yet.
                                </div>
                            ) : (
                                <ul className="mt-4 divide-y divide-gray-200 rounded-md border border-gray-200 bg-white">
                                    {expenses.map((expense) => (
                                        <li key={expense.id} className="flex items-center justify-between px-4 py-3">
                                            <div>
                                                <p className="text-sm font-medium text-gray-900">
                                                    {expense.category?.name ?? 'Uncategorized'}
                                                </p>
                                                <p className="text-xs text-gray-500">
                                                    {expense.spent_at}
                                                    {expense.description && ` · ${expense.description}`}
                                                </p>
                                            </div>
                                            <p className="text-sm font-semibold text-gray-900">
                                                {expense.amount} {expense.currency}
                                            </p>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </>
                    ) : canEditBudget ? (
                        <div className="mt-3">
                            {!showBudgetForm ? (
                                <button
                                    onClick={() => setShowBudgetForm(true)}
                                    className="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white"
                                >
                                    Create Budget
                                </button>
                            ) : (
                                <Form
                                    action={route('occasions.budget.store', occasion.slug)}
                                    method="post"
                                    className="max-w-md space-y-3 rounded-md border border-gray-200 bg-white p-4"
                                >
                                    {({ errors, processing }) => (
                                        <>
                                            <div>
                                                <label htmlFor="budget_name" className="block text-sm font-medium text-gray-700">
                                                    Budget Name
                                                </label>
                                                <input
                                                    id="budget_name"
                                                    name="name"
                                                    type="text"
                                                    required
                                                    defaultValue={`${occasion.title} Budget`}
                                                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                                />
                                                {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                                            </div>

                                            <div>
                                                <label htmlFor="planned_amount" className="block text-sm font-medium text-gray-700">
                                                    Planned Amount
                                                </label>
                                                <input
                                                    id="planned_amount"
                                                    name="planned_amount"
                                                    type="number"
                                                    min="1"
                                                    step="0.01"
                                                    required
                                                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                                />
                                                {errors.planned_amount && (
                                                    <p className="mt-1 text-sm text-red-600">{errors.planned_amount}</p>
                                                )}
                                            </div>

                                            <button
                                                type="submit"
                                                disabled={processing}
                                                className="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                                            >
                                                {processing ? 'Creating…' : 'Create Budget'}
                                            </button>
                                        </>
                                    )}
                                </Form>
                            )}
                        </div>
                    ) : (
                        <div className="mt-3 rounded-md border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500">
                            No Budget has been created for this Occasion yet.
                        </div>
                    )}
                </div>
            )}

            <div className="flex items-center justify-between">
                <h2 className="text-sm font-medium text-gray-900">Contributions</h2>
                {canRecordContribution && (
                    <button
                        onClick={() => setShowContributionForm((v) => !v)}
                        className="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white"
                    >
                        {showContributionForm ? 'Cancel' : 'Record Contribution'}
                    </button>
                )}
            </div>

            <p className="mt-1 text-xs text-gray-500">
                {summary.total_received} TZS received from {summary.contribution_count} contribution
                {summary.contribution_count === 1 ? '' : 's'}.
            </p>

            {showContributionForm && (
                <Form
                    action={route('occasions.contributions.store', occasion.slug)}
                    method="post"
                    resetOnSuccess
                    onSuccess={() => setShowContributionForm(false)}
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
