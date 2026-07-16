import { useForm } from '@inertiajs/react';
import Alert from '@/Components/Alert';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import AppLayout from '@/Layouts/AppLayout';

interface ProfileUser {
    name: string;
    email: string;
    email_verified_at: string | null;
}

interface NotificationType {
    value: string;
    label: string;
}

interface Session {
    id: string;
    ip_address: string | null;
    device: string;
    last_active_at: string;
    expires_at: string;
    is_current: boolean;
}

interface Props {
    user: ProfileUser;
    notificationTypes: NotificationType[];
    notificationPreferences: Record<string, boolean>;
    sessions: Session[];
}

function NotificationPreferencesCard({ notificationTypes, notificationPreferences }: Pick<Props, 'notificationTypes' | 'notificationPreferences'>) {
    const initialData = Object.fromEntries(notificationTypes.map((type) => [type.value, notificationPreferences[type.value] ?? true]));
    const { data, setData, patch, processing, wasSuccessful } = useForm(initialData);

    function submit() {
        patch(route('preferences.update'), { preserveScroll: true });
    }

    return (
        <Card title="Notification Preferences" className="mt-6 max-w-lg">
            <ul className="space-y-2">
                {notificationTypes.map((type) => (
                    <li key={type.value} className="flex items-center justify-between">
                        <label htmlFor={`pref-${type.value}`} className="text-sm text-text-primary">
                            {type.label}
                        </label>
                        <input
                            id={`pref-${type.value}`}
                            type="checkbox"
                            checked={data[type.value]}
                            onChange={(e) => setData(type.value, e.target.checked)}
                            className="h-4 w-4 rounded border-border"
                        />
                    </li>
                ))}
            </ul>

            <div className="mt-4 border-t border-border pt-4">
                <Button size="sm" loading={processing} onClick={submit}>
                    {processing ? 'Saving…' : 'Save Preferences'}
                </Button>
                {wasSuccessful && (
                    <Alert variant="success" className="mt-2">
                        Preferences updated.
                    </Alert>
                )}
            </div>
        </Card>
    );
}

function RevokeSessionButton({ session }: { session: Session }) {
    const { delete: destroy, processing } = useForm({});

    function revoke() {
        if (window.confirm(`Revoke this session (${session.device})?`)) {
            destroy(route('sessions.destroy', session.id), { preserveScroll: true });
        }
    }

    return (
        <Button variant="ghost" size="sm" loading={processing} onClick={revoke}>
            Revoke
        </Button>
    );
}

function ActiveSessionsCard({ sessions }: { sessions: Session[] }) {
    const { post, processing, wasSuccessful } = useForm({});

    function signOutOthers() {
        if (window.confirm('Sign out of all other devices?')) {
            post(route('sessions.destroy-others'), { preserveScroll: true });
        }
    }

    return (
        <Card title="Active Sessions" className="mt-6 max-w-lg">
            <ul className="divide-y divide-border">
                {sessions.map((session) => (
                    <li key={session.id} className="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div>
                            <p className="flex items-center gap-2 text-sm font-medium text-text-primary">
                                {session.device}
                                {session.is_current && <Badge variant="success">Current</Badge>}
                            </p>
                            <p className="text-xs text-text-secondary">
                                {session.ip_address ?? 'Unknown IP'} · last active {new Date(session.last_active_at).toLocaleString()}
                            </p>
                        </div>
                        {!session.is_current && <RevokeSessionButton session={session} />}
                    </li>
                ))}
            </ul>

            {sessions.length > 1 && (
                <div className="mt-4 border-t border-border pt-4">
                    <Button variant="danger" size="sm" loading={processing} onClick={signOutOthers}>
                        Sign out other devices
                    </Button>
                    {wasSuccessful && (
                        <Alert variant="success" className="mt-2">
                            Other sessions signed out.
                        </Alert>
                    )}
                </div>
            )}
        </Card>
    );
}

export default function Profile({ user, notificationTypes, notificationPreferences, sessions }: Props) {
    const { post, processing, wasSuccessful } = useForm({});

    function resend() {
        post(route('verification.send'));
    }

    return (
        <AppLayout>
            <h1 className="text-lg font-semibold text-text-primary">Profile</h1>

            <Card className="mt-4 max-w-lg">
                <dl className="space-y-3">
                    <div>
                        <dt className="text-sm font-medium text-text-secondary">Name</dt>
                        <dd className="text-sm text-text-primary">{user.name}</dd>
                    </div>
                    <div>
                        <dt className="text-sm font-medium text-text-secondary">Email</dt>
                        <dd className="flex items-center gap-2 text-sm text-text-primary">
                            {user.email}
                            {user.email_verified_at ? (
                                <Badge variant="success">Verified</Badge>
                            ) : (
                                <Badge variant="warning">Unverified</Badge>
                            )}
                        </dd>
                    </div>
                </dl>

                {!user.email_verified_at && (
                    <div className="mt-4 border-t border-border pt-4">
                        <Button variant="ghost" size="sm" loading={processing} onClick={resend}>
                            {processing ? 'Sending…' : 'Resend verification email'}
                        </Button>
                        {wasSuccessful && (
                            <Alert variant="success" className="mt-2">
                                Verification link sent.
                            </Alert>
                        )}
                    </div>
                )}
            </Card>

            <NotificationPreferencesCard notificationTypes={notificationTypes} notificationPreferences={notificationPreferences} />

            <ActiveSessionsCard sessions={sessions} />
        </AppLayout>
    );
}
