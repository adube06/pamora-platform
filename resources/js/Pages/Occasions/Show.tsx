import Badge from '@/Components/Badge';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import ReadinessRing from '@/Components/ReadinessRing';
import OccasionWorkspaceLayout from '@/Layouts/OccasionWorkspaceLayout';
import { formatCurrency } from '@/lib/currency';
import type { BudgetSummary, Occasion, OccasionMember, Readiness, TaskProgress } from '@/types/models';

interface Props {
    occasion: Occasion;
    member: OccasionMember;
    readiness: Readiness;
    taskProgress: TaskProgress;
    financialSummary: BudgetSummary;
    canViewBudget: boolean;
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

export default function Show({ occasion, member, readiness, taskProgress, financialSummary, canViewBudget }: Props) {
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
        </OccasionWorkspaceLayout>
    );
}
