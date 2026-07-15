import { Form } from '@inertiajs/react';
import { useState } from 'react';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import FormField from '@/Components/FormField';
import Input from '@/Components/Input';
import Select from '@/Components/Select';
import Textarea from '@/Components/Textarea';
import OccasionWorkspaceLayout from '@/Layouts/OccasionWorkspaceLayout';
import { formatCurrency } from '@/lib/currency';
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

const HEALTH_VARIANTS: Record<string, 'info' | 'success' | 'warning' | 'error'> = {
    under_budget: 'info',
    on_track: 'success',
    at_risk: 'warning',
    over_budget: 'error',
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
                        <h2 className="text-sm font-medium text-text-primary">Budget</h2>
                        {budget && summary.health && (
                            <Badge variant={HEALTH_VARIANTS[summary.health] ?? 'neutral'}>
                                {HEALTH_LABELS[summary.health] ?? summary.health}
                            </Badge>
                        )}
                    </div>

                    {budget ? (
                        <>
                            <div className="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <Card>
                                    <p className="text-xs text-text-secondary">Planned</p>
                                    <p className="mt-1 text-xl font-semibold text-text-primary">
                                        {formatCurrency(summary.planned_amount ?? 0)} {budget.currency}
                                    </p>
                                </Card>
                                <Card>
                                    <p className="text-xs text-text-secondary">Received</p>
                                    <p className="mt-1 text-xl font-semibold text-text-primary">
                                        {formatCurrency(summary.total_received)} {budget.currency}
                                    </p>
                                    <p className="text-xs text-text-secondary">{summary.funding_progress}% funded</p>
                                </Card>
                                <Card>
                                    <p className="text-xs text-text-secondary">Spent</p>
                                    <p className="mt-1 text-xl font-semibold text-text-primary">
                                        {formatCurrency(summary.total_expense ?? 0)} {budget.currency}
                                    </p>
                                    <p className="text-xs text-text-secondary">{summary.spending_progress}% spent</p>
                                </Card>
                                <Card>
                                    <p className="text-xs text-text-secondary">Remaining</p>
                                    <p className="mt-1 text-xl font-semibold text-text-primary">
                                        {formatCurrency(summary.remaining_budget ?? 0)} {budget.currency}
                                    </p>
                                </Card>
                            </div>

                            <div className="mt-6 flex items-center justify-between">
                                <h3 className="text-sm font-medium text-text-primary">Expenses</h3>
                                {canRecordExpense && (
                                    <Button size="sm" onClick={() => setShowExpenseForm((v) => !v)}>
                                        {showExpenseForm ? 'Cancel' : 'Record Expense'}
                                    </Button>
                                )}
                            </div>

                            {showExpenseForm && (
                                <Card className="mt-4 max-w-md">
                                    <Form
                                        action={route('occasions.expenses.store', occasion.slug)}
                                        method="post"
                                        resetOnSuccess
                                        onSuccess={() => setShowExpenseForm(false)}
                                        className="space-y-3"
                                    >
                                        {({ errors, processing }) => (
                                            <>
                                                <FormField label="Category" htmlFor="budget_category_id" error={errors.budget_category_id}>
                                                    <Select
                                                        id="budget_category_id"
                                                        name="budget_category_id"
                                                        invalid={!!errors.budget_category_id}
                                                    >
                                                        {budget.categories.map((category) => (
                                                            <option key={category.id} value={category.id}>
                                                                {category.name}
                                                            </option>
                                                        ))}
                                                    </Select>
                                                </FormField>

                                                <FormField label="Amount" htmlFor="expense_amount" required error={errors.amount}>
                                                    <Input
                                                        id="expense_amount"
                                                        name="amount"
                                                        type="number"
                                                        min="1"
                                                        step="0.01"
                                                        required
                                                        invalid={!!errors.amount}
                                                    />
                                                </FormField>

                                                <FormField label="Date" htmlFor="spent_at" required error={errors.spent_at}>
                                                    <Input id="spent_at" name="spent_at" type="date" required invalid={!!errors.spent_at} />
                                                </FormField>

                                                <FormField label="Description (optional)" htmlFor="description" error={errors.description}>
                                                    <Textarea id="description" name="description" rows={2} invalid={!!errors.description} />
                                                </FormField>

                                                <Button type="submit" loading={processing}>
                                                    {processing ? 'Recording…' : 'Record Expense'}
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                </Card>
                            )}

                            {expenses.length === 0 ? (
                                <div className="mt-4">
                                    <EmptyState title="No expenses recorded yet" />
                                </div>
                            ) : (
                                <ul className="mt-4 divide-y divide-border rounded-lg border border-border bg-surface">
                                    {expenses.map((expense) => (
                                        <li key={expense.id} className="flex items-center justify-between px-4 py-3">
                                            <div>
                                                <p className="text-sm font-medium text-text-primary">
                                                    {expense.category?.name ?? 'Uncategorized'}
                                                </p>
                                                <p className="text-xs text-text-secondary">
                                                    {expense.spent_at}
                                                    {expense.description && ` · ${expense.description}`}
                                                </p>
                                            </div>
                                            <p className="text-sm font-semibold text-text-primary">
                                                {formatCurrency(expense.amount)} {expense.currency}
                                            </p>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </>
                    ) : canEditBudget ? (
                        <div className="mt-3">
                            {!showBudgetForm ? (
                                <Button size="sm" onClick={() => setShowBudgetForm(true)}>
                                    Create Budget
                                </Button>
                            ) : (
                                <Card className="max-w-md">
                                    <Form action={route('occasions.budget.store', occasion.slug)} method="post" className="space-y-3">
                                        {({ errors, processing }) => (
                                            <>
                                                <FormField label="Budget Name" htmlFor="budget_name" required error={errors.name}>
                                                    <Input
                                                        id="budget_name"
                                                        name="name"
                                                        type="text"
                                                        required
                                                        defaultValue={`${occasion.title} Budget`}
                                                        invalid={!!errors.name}
                                                    />
                                                </FormField>

                                                <FormField
                                                    label="Planned Amount"
                                                    htmlFor="planned_amount"
                                                    required
                                                    error={errors.planned_amount}
                                                >
                                                    <Input
                                                        id="planned_amount"
                                                        name="planned_amount"
                                                        type="number"
                                                        min="1"
                                                        step="0.01"
                                                        required
                                                        invalid={!!errors.planned_amount}
                                                    />
                                                </FormField>

                                                <Button type="submit" loading={processing}>
                                                    {processing ? 'Creating…' : 'Create Budget'}
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                </Card>
                            )}
                        </div>
                    ) : (
                        <div className="mt-3">
                            <EmptyState title="No Budget has been created for this Occasion yet" />
                        </div>
                    )}
                </div>
            )}

            <div className="flex items-center justify-between">
                <h2 className="text-sm font-medium text-text-primary">Contributions</h2>
                {canRecordContribution && (
                    <Button size="sm" onClick={() => setShowContributionForm((v) => !v)}>
                        {showContributionForm ? 'Cancel' : 'Record Contribution'}
                    </Button>
                )}
            </div>

            <p className="mt-1 text-xs text-text-secondary">
                {formatCurrency(summary.total_received)} TZS received from {summary.contribution_count} contribution
                {summary.contribution_count === 1 ? '' : 's'}.
            </p>

            {showContributionForm && (
                <Card className="mt-4 max-w-md">
                    <Form
                        action={route('occasions.contributions.store', occasion.slug)}
                        method="post"
                        resetOnSuccess
                        onSuccess={() => setShowContributionForm(false)}
                        className="space-y-3"
                    >
                        {({ errors, processing }) => (
                            <>
                                <FormField label="Contributor Name" htmlFor="contributor_name" required error={errors.contributor_name}>
                                    <Input
                                        id="contributor_name"
                                        name="contributor_name"
                                        type="text"
                                        required
                                        invalid={!!errors.contributor_name}
                                    />
                                </FormField>

                                <FormField label="Phone (optional)" htmlFor="contributor_phone" error={errors.contributor_phone}>
                                    <Input id="contributor_phone" name="contributor_phone" type="text" invalid={!!errors.contributor_phone} />
                                </FormField>

                                <FormField label="Amount" htmlFor="amount" required error={errors.amount}>
                                    <Input
                                        id="amount"
                                        name="amount"
                                        type="number"
                                        min="1"
                                        step="0.01"
                                        required
                                        invalid={!!errors.amount}
                                    />
                                </FormField>

                                <FormField label="Method" htmlFor="method" error={errors.method}>
                                    <Select id="method" name="method" defaultValue="cash" invalid={!!errors.method}>
                                        <option value="cash">Cash</option>
                                        <option value="mobile_money">Mobile Money</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="other">Other</option>
                                    </Select>
                                </FormField>

                                <FormField label="Date" htmlFor="contributed_at" required error={errors.contributed_at}>
                                    <Input id="contributed_at" name="contributed_at" type="date" required invalid={!!errors.contributed_at} />
                                </FormField>

                                <FormField label="Message (optional)" htmlFor="message" error={errors.message}>
                                    <Textarea id="message" name="message" rows={2} invalid={!!errors.message} />
                                </FormField>

                                <Button type="submit" loading={processing}>
                                    {processing ? 'Recording…' : 'Record Contribution'}
                                </Button>
                            </>
                        )}
                    </Form>
                </Card>
            )}

            {contributions.length === 0 ? (
                <div className="mt-6">
                    <EmptyState title="No contributions recorded yet" />
                </div>
            ) : (
                <ul className="mt-4 divide-y divide-border rounded-lg border border-border bg-surface">
                    {contributions.map((contribution) => (
                        <li key={contribution.id} className="flex items-center justify-between px-4 py-3">
                            <div>
                                <p className="text-sm font-medium text-text-primary">{contribution.contributor_name}</p>
                                <p className="text-xs text-text-secondary">
                                    {METHOD_LABELS[contribution.method] ?? contribution.method} · {contribution.contributed_at}
                                    {contribution.message && ` · "${contribution.message}"`}
                                </p>
                            </div>
                            <p className="text-sm font-semibold text-text-primary">
                                {formatCurrency(contribution.amount)} {contribution.currency}
                            </p>
                        </li>
                    ))}
                </ul>
            )}
        </OccasionWorkspaceLayout>
    );
}
