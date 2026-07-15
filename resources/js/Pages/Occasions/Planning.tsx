import { Form, useForm } from '@inertiajs/react';
import { useState } from 'react';
import OccasionWorkspaceLayout from '@/Layouts/OccasionWorkspaceLayout';
import type { Occasion, OccasionMember, Task } from '@/types/models';

interface Props {
    occasion: Occasion;
    tasks: Task[];
    members: OccasionMember[];
    canCreateTask: boolean;
}

const STATUS_LABELS: Record<string, string> = {
    draft: 'Draft',
    open: 'Open',
    in_progress: 'In Progress',
    completed: 'Completed',
    cancelled: 'Cancelled',
    deferred: 'Deferred',
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
        <select
            value={data.assignee_id}
            onChange={handleChange}
            disabled={processing}
            className="rounded-md border border-gray-300 px-2 py-1 text-xs"
        >
            <option value="">Unassigned</option>
            {members.map((member) => (
                <option key={member.id} value={member.id}>
                    {member.user?.name}
                </option>
            ))}
        </select>
    );
}

export default function Planning({ occasion, tasks, members, canCreateTask }: Props) {
    const [showForm, setShowForm] = useState(false);

    return (
        <OccasionWorkspaceLayout occasion={occasion} active="planning">
            <div className="flex items-center justify-between">
                <h2 className="text-sm font-medium text-gray-900">Tasks</h2>
                {canCreateTask && (
                    <button
                        onClick={() => setShowForm((v) => !v)}
                        className="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white"
                    >
                        {showForm ? 'Cancel' : 'New Task'}
                    </button>
                )}
            </div>

            {showForm && (
                <Form
                    action={route('occasions.tasks.store', occasion.slug)}
                    method="post"
                    resetOnSuccess
                    onSuccess={() => setShowForm(false)}
                    className="mt-4 max-w-md space-y-3 rounded-md border border-gray-200 bg-white p-4"
                >
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
                                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                />
                                {errors.title && <p className="mt-1 text-sm text-red-600">{errors.title}</p>}
                            </div>

                            <div>
                                <label htmlFor="priority" className="block text-sm font-medium text-gray-700">
                                    Priority
                                </label>
                                <select
                                    id="priority"
                                    name="priority"
                                    defaultValue="medium"
                                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                >
                                    <option value="critical">Critical</option>
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>

                            <div>
                                <label htmlFor="due_date" className="block text-sm font-medium text-gray-700">
                                    Due date
                                </label>
                                <input
                                    id="due_date"
                                    name="due_date"
                                    type="date"
                                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                                />
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            >
                                {processing ? 'Creating…' : 'Create Task'}
                            </button>
                        </>
                    )}
                </Form>
            )}

            {tasks.length === 0 ? (
                <div className="mt-6 rounded-md border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">
                    No tasks yet.
                </div>
            ) : (
                <ul className="mt-4 divide-y divide-gray-200 rounded-md border border-gray-200 bg-white">
                    {tasks.map((task) => (
                        <li key={task.id} className="flex items-center justify-between px-4 py-3">
                            <div>
                                <p className="text-sm font-medium text-gray-900">{task.title}</p>
                                <p className="text-xs text-gray-500">
                                    {STATUS_LABELS[task.status]} · {task.priority}
                                    {task.due_date && ` · Due ${task.due_date}`}
                                </p>
                            </div>
                            <AssignSelect task={task} members={members} />
                        </li>
                    ))}
                </ul>
            )}
        </OccasionWorkspaceLayout>
    );
}
