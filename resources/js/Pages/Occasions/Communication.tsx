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
import type { Announcement, Occasion, ReminderRule, TimelineEvent } from '@/types/models';

interface Props {
    occasion: Occasion;
    announcements: Announcement[];
    timelineEvents: TimelineEvent[];
    reminderRules: ReminderRule[];
    canPublishAnnouncement: boolean;
    canScheduleReminder: boolean;
}

function formatPublishedAt(value: string): string {
    return new Date(value).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

const OFFSET_LABELS: Record<number, string> = {
    120: '2 hours before',
    1440: '24 hours before',
    10080: '7 days before',
};

export default function Communication({
    occasion,
    announcements,
    timelineEvents,
    reminderRules,
    canPublishAnnouncement,
    canScheduleReminder,
}: Props) {
    const [showForm, setShowForm] = useState(false);
    const [showReminderForm, setShowReminderForm] = useState(false);

    return (
        <OccasionWorkspaceLayout occasion={occasion} active="communication">
            {canScheduleReminder && timelineEvents.length > 0 && (
                <>
                    <div className="flex items-center justify-between">
                        <h2 className="text-sm font-medium text-text-primary">Reminders</h2>
                        <Button variant="ghost" size="sm" onClick={() => setShowReminderForm((v) => !v)}>
                            {showReminderForm ? 'Cancel' : 'New Reminder'}
                        </Button>
                    </div>

                    {showReminderForm && (
                        <Card className="mt-4 max-w-md">
                            <Form
                                action={route('occasions.reminder-rules.store', occasion.slug)}
                                method="post"
                                resetOnSuccess
                                onSuccess={() => setShowReminderForm(false)}
                                className="space-y-3"
                            >
                                {({ errors, processing }) => (
                                    <>
                                        <FormField label="Timeline Event" htmlFor="timeline_event_id" required error={errors.timeline_event_id}>
                                            <Select id="timeline_event_id" name="timeline_event_id" required invalid={!!errors.timeline_event_id}>
                                                {timelineEvents.map((event) => (
                                                    <option key={event.id} value={event.id}>
                                                        {event.name}
                                                    </option>
                                                ))}
                                            </Select>
                                        </FormField>

                                        <FormField label="Remind" htmlFor="offset_minutes" required error={errors.offset_minutes}>
                                            <Select id="offset_minutes" name="offset_minutes" defaultValue="120" required>
                                                <option value="120">2 hours before</option>
                                                <option value="1440">24 hours before</option>
                                                <option value="10080">7 days before</option>
                                            </Select>
                                        </FormField>

                                        <Button type="submit" loading={processing}>
                                            {processing ? 'Scheduling…' : 'Schedule Reminder'}
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </Card>
                    )}

                    {reminderRules.length > 0 && (
                        <ul className="mt-4 divide-y divide-border rounded-lg border border-border bg-surface">
                            {reminderRules.map((rule) => (
                                <li key={rule.id} className="flex items-center justify-between px-4 py-3">
                                    <div>
                                        <p className="text-sm font-medium text-text-primary">{rule.timeline_event.name}</p>
                                        <p className="text-xs text-text-secondary">{OFFSET_LABELS[rule.offset_minutes] ?? `${rule.offset_minutes} min before`}</p>
                                    </div>
                                    <Badge variant={rule.triggered_at ? 'neutral' : 'info'}>{rule.triggered_at ? 'Sent' : 'Pending'}</Badge>
                                </li>
                            ))}
                        </ul>
                    )}
                </>
            )}

            <div className="mt-8 flex items-center justify-between">
                <h2 className="text-sm font-medium text-text-primary">Announcements</h2>
                {canPublishAnnouncement && (
                    <Button size="sm" onClick={() => setShowForm((v) => !v)}>
                        {showForm ? 'Cancel' : 'New Announcement'}
                    </Button>
                )}
            </div>

            {showForm && (
                <Card className="mt-4 max-w-md">
                    <Form
                        action={route('occasions.announcements.store', occasion.slug)}
                        method="post"
                        resetOnSuccess
                        onSuccess={() => setShowForm(false)}
                        className="space-y-3"
                    >
                        {({ errors, processing }) => (
                            <>
                                <FormField label="Title" htmlFor="title" required error={errors.title}>
                                    <Input
                                        id="title"
                                        name="title"
                                        type="text"
                                        required
                                        placeholder="e.g. Venue update"
                                        invalid={!!errors.title}
                                    />
                                </FormField>

                                <FormField label="Message" htmlFor="message" required error={errors.message}>
                                    <Textarea id="message" name="message" rows={4} required invalid={!!errors.message} />
                                </FormField>

                                <Button type="submit" loading={processing}>
                                    {processing ? 'Publishing…' : 'Publish Announcement'}
                                </Button>
                            </>
                        )}
                    </Form>
                </Card>
            )}

            {announcements.length === 0 ? (
                <div className="mt-4">
                    <EmptyState title="No announcements yet" description="Official updates published to the Occasion will show up here." />
                </div>
            ) : (
                <div className="mt-4 space-y-4">
                    {announcements.map((announcement) => (
                        <Card key={announcement.id}>
                            <div className="flex items-start justify-between gap-4">
                                <p className="text-sm font-medium text-text-primary">{announcement.title}</p>
                                <Badge variant="info">{formatPublishedAt(announcement.published_at)}</Badge>
                            </div>
                            <p className="mt-2 text-sm whitespace-pre-line text-text-secondary">{announcement.message}</p>
                            {announcement.created_by && (
                                <p className="mt-3 text-xs text-text-secondary">Published by {announcement.created_by.name}</p>
                            )}
                        </Card>
                    ))}
                </div>
            )}
        </OccasionWorkspaceLayout>
    );
}
