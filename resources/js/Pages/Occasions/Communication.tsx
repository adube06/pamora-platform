import { Form } from '@inertiajs/react';
import { useState } from 'react';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import FormField from '@/Components/FormField';
import Input from '@/Components/Input';
import Textarea from '@/Components/Textarea';
import OccasionWorkspaceLayout from '@/Layouts/OccasionWorkspaceLayout';
import type { Announcement, Occasion } from '@/types/models';

interface Props {
    occasion: Occasion;
    announcements: Announcement[];
    canPublishAnnouncement: boolean;
}

function formatPublishedAt(value: string): string {
    return new Date(value).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

export default function Communication({ occasion, announcements, canPublishAnnouncement }: Props) {
    const [showForm, setShowForm] = useState(false);

    return (
        <OccasionWorkspaceLayout occasion={occasion} active="communication">
            <div className="flex items-center justify-between">
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
