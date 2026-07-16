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

export default function Profile({ user }: { user: ProfileUser }) {
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
        </AppLayout>
    );
}
