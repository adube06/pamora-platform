import { Form, router } from '@inertiajs/react';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import FormField from '@/Components/FormField';
import Input from '@/Components/Input';
import ReadinessRing from '@/Components/ReadinessRing';
import Select from '@/Components/Select';
import Textarea from '@/Components/Textarea';
import OccasionWorkspaceLayout from '@/Layouts/OccasionWorkspaceLayout';
import { formatCurrency } from '@/lib/currency';
import type { BudgetSummary, Occasion, OccasionMember, Readiness, TaskProgress } from '@/types/models';

interface Option {
    value: string;
    label: string;
}

interface Props {
    occasion: Occasion;
    member: OccasionMember;
    readiness: Readiness;
    taskProgress: TaskProgress;
    financialSummary: BudgetSummary;
    canViewBudget: boolean;
    canEdit: boolean;
    canArchive: boolean;
    canCancel: boolean;
    types: Option[];
    visibilities: Option[];
    nextStatuses: Option[];
}

function formatRole(role: string): string {
    return role
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

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

const TASK_STATUS_LABELS: Record<string, string> = {
    draft: 'Draft',
    open: 'Open',
    in_progress: 'In Progress',
    completed: 'Completed',
    deferred: 'Deferred',
};

export default function Show({
    occasion,
    member,
    readiness,
    taskProgress,
    financialSummary,
    canViewBudget,
    canEdit,
    canArchive,
    canCancel,
    types,
    visibilities,
    nextStatuses,
}: Props) {
    function archive() {
        if (window.confirm('Archive this Occasion? It will become read-only.')) {
            router.post(route('occasions.archive', occasion.slug));
        }
    }

    function cancel() {
        if (window.confirm('Cancel this Occasion? This cannot be undone.')) {
            router.post(route('occasions.cancel', occasion.slug));
        }
    }

    return (
        <OccasionWorkspaceLayout occasion={occasion} active="overview">
            <div className="mb-6 grid max-w-3xl grid-cols-1 gap-4 sm:grid-cols-3">
                <Card title="Readiness">
                    {readiness.score === null ? (
                        <p className="mt-1 text-sm text-text-secondary">Not enough data yet.</p>
                    ) : (
                        <div className="mt-2 flex items-center gap-6">
                            <ReadinessRing score={readiness.score} />
                            <ul className="flex-1 space-y-1">
                                {readiness.signals.map((signal) => (
                                    <li key={signal.key} className="flex items-center justify-between text-xs text-text-secondary">
                                        <span>{signal.label}</span>
                                        <span className="font-medium text-text-primary">{signal.value}%</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </Card>

                <Card title="Financial Summary">
                    <p className="mt-1 text-lg font-semibold text-text-primary">{formatCurrency(financialSummary.total_received)}</p>
                    <p className="text-xs text-text-secondary">
                        received from {financialSummary.contribution_count} contribution
                        {financialSummary.contribution_count === 1 ? '' : 's'}
                    </p>
                    {canViewBudget && financialSummary.planned_amount !== undefined && financialSummary.planned_amount !== null && (
                        <>
                            <div className="mt-3 flex items-center justify-between text-xs text-text-secondary">
                                <span>Planned</span>
                                <span className="font-medium text-text-primary">{formatCurrency(financialSummary.planned_amount)}</span>
                            </div>
                            <div className="flex items-center justify-between text-xs text-text-secondary">
                                <span>Remaining</span>
                                <span className="font-medium text-text-primary">{formatCurrency(financialSummary.remaining_budget ?? 0)}</span>
                            </div>
                            {financialSummary.health && (
                                <Badge variant={HEALTH_VARIANTS[financialSummary.health] ?? 'neutral'} className="mt-2">
                                    {HEALTH_LABELS[financialSummary.health] ?? financialSummary.health}
                                </Badge>
                            )}
                        </>
                    )}
                </Card>

                <Card title="Task Progress">
                    {taskProgress.total === 0 ? (
                        <EmptyState title="No tasks yet" />
                    ) : (
                        <>
                            <p className="mt-1 text-lg font-semibold text-text-primary">{taskProgress.completion_percentage}% complete</p>
                            <ul className="mt-2 space-y-1">
                                {(['open', 'in_progress', 'completed', 'deferred'] as const)
                                    .filter((key) => taskProgress[key] > 0)
                                    .map((key) => (
                                        <li key={key} className="flex items-center justify-between text-xs text-text-secondary">
                                            <span>{TASK_STATUS_LABELS[key]}</span>
                                            <span className="font-medium text-text-primary">{taskProgress[key]}</span>
                                        </li>
                                    ))}
                            </ul>
                        </>
                    )}
                </Card>
            </div>

            <dl className="grid max-w-lg grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt className="text-sm font-medium text-text-secondary">Date</dt>
                    <dd className="text-sm text-text-primary">{occasion.primary_date ?? 'Not set'}</dd>
                </div>
                <div>
                    <dt className="text-sm font-medium text-text-secondary">Location</dt>
                    <dd className="text-sm text-text-primary">{occasion.location ?? 'Not set'}</dd>
                </div>
                <div>
                    <dt className="text-sm font-medium text-text-secondary">Visibility</dt>
                    <dd className="text-sm text-text-primary">{occasion.visibility}</dd>
                </div>
                <div>
                    <dt className="text-sm font-medium text-text-secondary">Your role</dt>
                    <dd className="text-sm text-text-primary">{formatRole(member.role)}</dd>
                </div>
            </dl>

            {occasion.description && (
                <div className="mt-6 max-w-lg">
                    <dt className="text-sm font-medium text-text-secondary">Description</dt>
                    <dd className="mt-1 text-sm text-text-primary">{occasion.description}</dd>
                </div>
            )}

            {((canEdit && occasion.status !== 'archived') || canArchive || canCancel) && (
                <Card title="Manage Occasion" className="mt-6 max-w-lg">
                    {canEdit && occasion.status !== 'archived' && (
                        <Form
                            action={route('occasions.update', occasion.slug)}
                            method="patch"
                            className="space-y-3"
                        >
                            {({ errors, processing }) => (
                                <>
                                    <FormField label="Title" htmlFor="title" required error={errors.title}>
                                        <Input id="title" name="title" type="text" required defaultValue={occasion.title} invalid={!!errors.title} />
                                    </FormField>

                                    <FormField label="Type" htmlFor="type" required error={errors.type}>
                                        <Select id="type" name="type" required defaultValue={occasion.type} invalid={!!errors.type}>
                                            {types.map((type) => (
                                                <option key={type.value} value={type.value}>
                                                    {type.label}
                                                </option>
                                            ))}
                                        </Select>
                                    </FormField>

                                    <FormField label="Date" htmlFor="primary_date" error={errors.primary_date}>
                                        <Input
                                            id="primary_date"
                                            name="primary_date"
                                            type="date"
                                            defaultValue={occasion.primary_date ?? ''}
                                            invalid={!!errors.primary_date}
                                        />
                                    </FormField>

                                    <FormField label="Location" htmlFor="location">
                                        <Input id="location" name="location" type="text" defaultValue={occasion.location ?? ''} />
                                    </FormField>

                                    <FormField label="Visibility" htmlFor="visibility">
                                        <Select id="visibility" name="visibility" defaultValue={occasion.visibility}>
                                            {visibilities.map((visibility) => (
                                                <option key={visibility.value} value={visibility.value}>
                                                    {visibility.label}
                                                </option>
                                            ))}
                                        </Select>
                                    </FormField>

                                    <FormField label="Description" htmlFor="description">
                                        <Textarea id="description" name="description" rows={3} defaultValue={occasion.description ?? ''} />
                                    </FormField>

                                    {nextStatuses.length > 1 && (
                                        <FormField label="Stage" htmlFor="status" error={errors.status}>
                                            <Select id="status" name="status" defaultValue={occasion.status} invalid={!!errors.status}>
                                                {nextStatuses.map((status) => (
                                                    <option key={status.value} value={status.value}>
                                                        {status.label}
                                                    </option>
                                                ))}
                                            </Select>
                                        </FormField>
                                    )}

                                    <Button type="submit" size="sm" loading={processing}>
                                        {processing ? 'Saving…' : 'Save Changes'}
                                    </Button>
                                </>
                            )}
                        </Form>
                    )}

                    {((canArchive && occasion.status === 'completed') ||
                        (canCancel && !['completed', 'archived', 'cancelled'].includes(occasion.status))) && (
                        <div className="mt-4 flex gap-2 border-t border-border pt-4">
                            {canArchive && occasion.status === 'completed' && (
                                <Button variant="ghost" size="sm" onClick={archive}>
                                    Archive Occasion
                                </Button>
                            )}
                            {canCancel && !['completed', 'archived', 'cancelled'].includes(occasion.status) && (
                                <Button variant="danger" size="sm" onClick={cancel}>
                                    Cancel Occasion
                                </Button>
                            )}
                        </div>
                    )}
                </Card>
            )}
        </OccasionWorkspaceLayout>
    );
}
