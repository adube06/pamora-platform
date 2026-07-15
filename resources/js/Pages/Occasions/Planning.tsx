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
import type { Checklist, Milestone, Occasion, OccasionMember, Task } from '@/types/models';

interface Props {
    occasion: Occasion;
    tasks: Task[];
    checklists: Checklist[];
    milestones: Milestone[];
    members: OccasionMember[];
    canCreateTask: boolean;
    canCompleteTask: boolean;
    canReopenTask: boolean;
    canManageChecklist: boolean;
    canManageMilestone: boolean;
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

function TaskList({
    tasks,
    members,
    canCompleteTask,
    canReopenTask,
}: {
    tasks: Task[];
    members: OccasionMember[];
    canCompleteTask: boolean;
    canReopenTask: boolean;
}) {
    return (
        <ul className="divide-y divide-border rounded-lg border border-border bg-surface">
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
    );
}

export default function Planning({
    occasion,
    tasks,
    checklists,
    milestones,
    members,
    canCreateTask,
    canCompleteTask,
    canReopenTask,
    canManageChecklist,
    canManageMilestone,
}: Props) {
    const [showTaskForm, setShowTaskForm] = useState(false);
    const [showChecklistForm, setShowChecklistForm] = useState(false);
    const [showMilestoneForm, setShowMilestoneForm] = useState(false);

    const ungroupedTasks = tasks.filter((task) => task.checklist_id === null);

    return (
        <OccasionWorkspaceLayout occasion={occasion} active="planning">
            <div className="flex items-center justify-between">
                <h2 className="text-sm font-medium text-text-primary">Milestones</h2>
                {canManageMilestone && (
                    <Button variant="ghost" size="sm" onClick={() => setShowMilestoneForm((v) => !v)}>
                        {showMilestoneForm ? 'Cancel' : 'New Milestone'}
                    </Button>
                )}
            </div>

            {showMilestoneForm && (
                <Card className="mt-4 max-w-md">
                    <Form
                        action={route('occasions.milestones.store', occasion.slug)}
                        method="post"
                        resetOnSuccess
                        onSuccess={() => setShowMilestoneForm(false)}
                        className="space-y-3"
                    >
                        {({ errors, processing }) => (
                            <>
                                <FormField label="Milestone Name" htmlFor="milestone_name" required error={errors.name}>
                                    <Input
                                        id="milestone_name"
                                        name="name"
                                        type="text"
                                        required
                                        placeholder="e.g. Venue Confirmed"
                                        invalid={!!errors.name}
                                    />
                                </FormField>

                                {tasks.length > 0 && (
                                    <FormField label="Depends on Tasks" htmlFor="task_ids">
                                        <div className="max-h-40 space-y-1 overflow-y-auto rounded-lg border border-border p-2">
                                            {tasks.map((task) => (
                                                <label key={task.id} className="flex items-center gap-2 text-sm text-text-primary">
                                                    <input type="checkbox" name="task_ids[]" value={task.id} className="rounded" />
                                                    {task.title}
                                                </label>
                                            ))}
                                        </div>
                                    </FormField>
                                )}

                                <Button type="submit" loading={processing}>
                                    {processing ? 'Creating…' : 'Create Milestone'}
                                </Button>
                            </>
                        )}
                    </Form>
                </Card>
            )}

            {milestones.length === 0 ? (
                <div className="mt-4">
                    <EmptyState title="No milestones yet" description="Milestones summarize progress across related Tasks." />
                </div>
            ) : (
                <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {milestones.map((milestone) => (
                        <Card key={milestone.id}>
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium text-text-primary">{milestone.name}</p>
                                <Badge variant={milestone.is_achieved ? 'success' : 'neutral'}>
                                    {milestone.is_achieved ? 'Achieved' : 'Pending'}
                                </Badge>
                            </div>
                            {milestone.tasks.length > 0 && (
                                <ul className="mt-2 space-y-0.5 text-xs text-text-secondary">
                                    {milestone.tasks.map((task) => (
                                        <li key={task.id}>
                                            {task.status === 'completed' ? '✓' : '○'} {task.title}
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </Card>
                    ))}
                </div>
            )}

            <div className="mt-8 flex items-center justify-between">
                <h2 className="text-sm font-medium text-text-primary">Tasks</h2>
                <div className="flex gap-2">
                    {canManageChecklist && (
                        <Button variant="ghost" size="sm" onClick={() => setShowChecklistForm((v) => !v)}>
                            {showChecklistForm ? 'Cancel' : 'New Checklist'}
                        </Button>
                    )}
                    {canCreateTask && (
                        <Button size="sm" onClick={() => setShowTaskForm((v) => !v)}>
                            {showTaskForm ? 'Cancel' : 'New Task'}
                        </Button>
                    )}
                </div>
            </div>

            {showChecklistForm && (
                <Card className="mt-4 max-w-md">
                    <Form
                        action={route('occasions.checklists.store', occasion.slug)}
                        method="post"
                        resetOnSuccess
                        onSuccess={() => setShowChecklistForm(false)}
                        className="space-y-3"
                    >
                        {({ errors, processing }) => (
                            <>
                                <FormField label="Checklist Name" htmlFor="checklist_name" required error={errors.name}>
                                    <Input
                                        id="checklist_name"
                                        name="name"
                                        type="text"
                                        required
                                        placeholder="e.g. Catering"
                                        invalid={!!errors.name}
                                    />
                                </FormField>

                                <Button type="submit" loading={processing}>
                                    {processing ? 'Creating…' : 'Create Checklist'}
                                </Button>
                            </>
                        )}
                    </Form>
                </Card>
            )}

            {showTaskForm && (
                <Card className="mt-4 max-w-md">
                    <Form
                        action={route('occasions.tasks.store', occasion.slug)}
                        method="post"
                        resetOnSuccess
                        onSuccess={() => setShowTaskForm(false)}
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

                                {checklists.length > 0 && (
                                    <FormField label="Checklist (optional)" htmlFor="checklist_id" error={errors.checklist_id}>
                                        <Select id="checklist_id" name="checklist_id" defaultValue="" invalid={!!errors.checklist_id}>
                                            <option value="">None</option>
                                            {checklists.map((checklist) => (
                                                <option key={checklist.id} value={checklist.id}>
                                                    {checklist.name}
                                                </option>
                                            ))}
                                        </Select>
                                    </FormField>
                                )}

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
                <div className="mt-4 space-y-6">
                    {checklists.map((checklist) => {
                        const checklistTasks = tasks.filter((task) => task.checklist_id === checklist.id);

                        if (checklistTasks.length === 0) {
                            return null;
                        }

                        return (
                            <div key={checklist.id}>
                                <h3 className="mb-2 text-xs font-medium tracking-wide text-text-secondary uppercase">{checklist.name}</h3>
                                <TaskList
                                    tasks={checklistTasks}
                                    members={members}
                                    canCompleteTask={canCompleteTask}
                                    canReopenTask={canReopenTask}
                                />
                            </div>
                        );
                    })}

                    {ungroupedTasks.length > 0 && (
                        <div>
                            {checklists.length > 0 && (
                                <h3 className="mb-2 text-xs font-medium tracking-wide text-text-secondary uppercase">Ungrouped</h3>
                            )}
                            <TaskList
                                tasks={ungroupedTasks}
                                members={members}
                                canCompleteTask={canCompleteTask}
                                canReopenTask={canReopenTask}
                            />
                        </div>
                    )}
                </div>
            )}
        </OccasionWorkspaceLayout>
    );
}
