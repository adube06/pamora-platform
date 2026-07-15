import { useForm } from '@inertiajs/react';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import AppLayout from '@/Layouts/AppLayout';
import { cn } from '@/lib/cn';
import type { Notification } from '@/types/models';

interface Props {
    notifications: Notification[];
}

function formatCreatedAt(value: string): string {
    return new Date(value).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

function NotificationRow({ notification }: { notification: Notification }) {
    const { post, processing } = useForm({});
    const isUnread = notification.read_at === null;

    return (
        <Card className={cn('border-l-4', isUnread ? 'border-l-primary' : 'border-l-transparent')}>
            <div className="flex items-start justify-between gap-4">
                <div>
                    <div className="flex items-center gap-2">
                        <p className="text-sm font-medium text-text-primary">{notification.title}</p>
                        {isUnread && <Badge variant="info">New</Badge>}
                    </div>
                    <p className="mt-1 text-sm text-text-secondary">{notification.body}</p>
                </div>
                {isUnread && (
                    <Button
                        variant="ghost"
                        size="sm"
                        loading={processing}
                        onClick={() => post(route('notifications.read', notification.uuid), { preserveScroll: true })}
                    >
                        Mark as read
                    </Button>
                )}
            </div>
            <p className="mt-3 text-xs text-text-secondary">{formatCreatedAt(notification.created_at)}</p>
        </Card>
    );
}

export default function NotificationsIndex({ notifications }: Props) {
    return (
        <AppLayout>
            <h1 className="text-lg font-semibold text-text-primary">Notifications</h1>

            {notifications.length === 0 ? (
                <div className="mt-4">
                    <EmptyState title="No notifications yet" description="Updates about things that involve you will show up here." />
                </div>
            ) : (
                <div className="mt-4 space-y-3">
                    {notifications.map((notification) => (
                        <NotificationRow key={notification.id} notification={notification} />
                    ))}
                </div>
            )}
        </AppLayout>
    );
}
