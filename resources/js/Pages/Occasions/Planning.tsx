import { Form, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import FormField from '@/Components/FormField';
import Input from '@/Components/Input';
import Select from '@/Components/Select';
import OccasionWorkspaceLayout from '@/Layouts/OccasionWorkspaceLayout';
import type { Occasion, OccasionMember, Task } from '@/types/models';

interface Props {
    occasion: Occasion;
    tasks: Task[];
    members: OccasionMember[];
    canCreateTask: boolean;
    canCompleteTask: boolean;
    canReopenTask: boolean;
}

const STATUS_LABELS: Record<string, string> = {
    draft: 'Draft',
    open: 'Open',
    in_progress: 'In Progress',
    completed: 'Completed',
    cancelled: 'Cancelled',
    deferred: 'Deferred',
};

const PRIORITY_VARIANTS: Record<string, 'error' | 'warning' | 'info' | 'neutral'> = {
    critical: 'error',
    high: 'warning',
    medium: 'info',
    low: 'neutral',
};

function AssignSelect({ task, members }: { task: Task; members: OccasionMember[] }) {
    const { data, setData, post, processing } = useForm({
        assignee_id: task.assignee_id ?? '',
    });

    function handleChange(e: React.ChangeEvent<HTMLSelectElement>) {
        setData('assignee_id', e.target.value);
        post(route('tasks.assign', task.uuid), { preserveScroll: true });
    }

    return (
        <Select value={data.assignee_id} onChange={handleChange} disabled={processing} className="w-auto px-2 py-1 text-xs">
            <option value="">Unassigned</option>
            {members.map((member) => (
                <option key={member.id} value={member.id}>
                    {member.user?.name}
                </option>
            ))}
        </Select>
    );
}

function TaskStatusAction({ task, canCompleteTask, canReopenTask }: { task: Task; canCompleteTask: boolean; canReopenTask: boolean }) {
    const { post, processing } = useForm({});

    if (task.status === 'completed') {
        if (!canReopenTask) {
            return null;
        }

        return (
            <Button
                variant="ghost"
                size="sm"
                loading={processing}
                onClick={() => post(route('tasks.reopen', task.uuid), { preserveScroll: true })}
            >
                Reopen
            </Button>
        );
    }

    if (task.status === 'cancelled' || !canCompleteTask) {
        return null;
    }

    return (
        <Button size="sm" loading={processing} onClick={() => post(route('tasks.complete', task.uuid), { preserveScroll: true })}>
            Complete
        </Button>
    );
}

export default function Planning({ occasion, tasks, members, canCreateTask, canCompleteTask, canReopenTask }: Props) {
    const [showForm, setShowForm] = useState(false);

    return (
        <OccasionWorkspaceLayout occasion={occasion} active="planning">
            <div className="flex items-center justify-between">
                <h2 className="text-sm font-medium text-text-primary">Tasks</h2>
                {canCreateTask && (
                    <Button size="sm" onClick={() => setShowForm((v) => !v)}>
                        {showForm ? 'Cancel' : 'New Task'}
                    </Button>
                )}
            </div>

            {showForm && (
                <Card className="mt-4 max-w-md">
                    <Form
                        action={route('occasions.tasks.store', occasion.slug)}
                        method="post"
                        resetOnSuccess
                        onSuccess={() => setShowForm(false)}
                        className="space-y-3"
                    >
                        {({ errors, processing }) => (
                            <>
                                <FormField label="Title" htmlFor="title" required error={errors.title}>
                                    <Input id="title" name="title" type="text" required invalid={!!errors.title} />
                                </FormField>

                                <FormField label="Priority" htmlFor="priority">
                                    <Select id="priority" name="priority" defaultValue="medium">
                                        <option value="critical">Critical</option>
                                        <option value="high">High</option>
                                        <option value="medium">Medium</option>
                                        <option value="low">Low</option>
                                    </Select>
                                </FormField>

                                <FormField label="Due date" htmlFor="due_date">
                                    <Input id="due_date" name="due_date" type="date" />
                                </FormField>

                                <Button type="submit" loading={processing}>
                                    {processing ? 'Creating…' : 'Create Task'}
                                </Button>
                            </>
                        )}
                    </Form>
                </Card>
            )}

            {tasks.length === 0 ? (
                <div className="mt-6">
                    <EmptyState title="No tasks yet" description="Tasks you create will show up here." />
                </div>
            ) : (
                <ul className="mt-4 divide-y divide-border rounded-lg border border-border bg-surface">
                    {tasks.map((task) => (
                        <li key={task.id} className="flex items-center justify-between px-4 py-3">
                            <div>
                                <p className="text-sm font-medium text-text-primary">{task.title}</p>
                                <div className="mt-1 flex items-center gap-2 text-xs text-text-secondary">
                                    <span>{STATUS_LABELS[task.status]}</span>
                                    <Badge variant={PRIORITY_VARIANTS[task.priority] ?? 'neutral'}>{task.priority}</Badge>
                                    {task.due_date && <span>Due {task.due_date}</span>}
                                </div>
                            </div>
                            <div className="flex items-center gap-2">
                                <AssignSelect task={task} members={members} />
                                <TaskStatusAction task={task} canCompleteTask={canCompleteTask} canReopenTask={canReopenTask} />
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </OccasionWorkspaceLayout>
    );
}
