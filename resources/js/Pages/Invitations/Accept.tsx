import { Link, useForm, usePage } from '@inertiajs/react';
import Alert from '@/Components/Alert';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import GuestLayout from '@/Layouts/GuestLayout';

interface InvitationProps {
    token: string;
    email: string;
    status: string;
    is_pending: boolean;
    role: string;
    role_label: string;
    notes: string | null;
    occasion: {
        title: string;
        type: string;
    };
}

interface PageProps {
    auth: {
        user: { id: number; name: string; email: string } | null;
    };
    [key: string]: unknown;
}

export default function Accept({ invitation }: { invitation: InvitationProps }) {
    const { auth } = usePage<PageProps>().props;
    const { post, processing, errors } = useForm({});
    const decline = useForm({});

    // "invitation" is a business-rule validation key returned by
    // AcceptInvitationService (e.g. expired/revoked/wrong-email) — it has
    // no corresponding form field, so it isn't part of useForm's inferred
    // error type.
    const invitationError = (errors as Record<string, string>).invitation;

    const emailMatches = auth.user?.email.toLowerCase() === invitation.email.toLowerCase();

    function accept() {
        post(route('invitations.accept', invitation.token));
    }

    function declineInvitation() {
        if (window.confirm('Decline this invitation?')) {
            decline.post(route('invitations.decline', invitation.token));
        }
    }

    return (
        <GuestLayout title="Plan occasions together" subtitle="Accept your invitation to join the committee.">
            <div className="text-center">
                <h1 className="text-xl font-semibold text-text-primary">You&apos;re invited to {invitation.occasion.title}</h1>
                <p className="mt-2 text-sm text-text-secondary">
                    {invitation.occasion.type} · Invited as {invitation.role_label}
                    {invitation.notes && ` · ${invitation.notes}`}
                </p>

                {!invitation.is_pending && (
                    <Alert variant="error" className="mt-6 justify-center">
                        This invitation is no longer valid. Ask the Host to send a new one.
                    </Alert>
                )}

                {invitation.is_pending && !auth.user && (
                    <Card className="mt-6 space-y-3">
                        <p className="text-sm text-text-secondary">Create an account or log in with {invitation.email} to accept.</p>
                        <div className="flex justify-center gap-3">
                            <Link
                                href={route('register', { invitation: invitation.token })}
                                className="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-hover"
                            >
                                Create account
                            </Link>
                            <Link
                                href={route('login', { invitation: invitation.token })}
                                className="rounded-lg border border-border px-4 py-2 text-sm font-medium text-text-primary hover:bg-background"
                            >
                                Log in
                            </Link>
                        </div>
                    </Card>
                )}

                {invitation.is_pending && auth.user && !emailMatches && (
                    <Alert variant="error" className="mt-6 justify-center">
                        This invitation was sent to {invitation.email}, but you&apos;re logged in as {auth.user.email}.
                    </Alert>
                )}

                {invitation.is_pending && auth.user && emailMatches && (
                    <div className="mt-6">
                        <div className="flex justify-center gap-3">
                            <Button onClick={accept} loading={processing}>
                                {processing ? 'Joining…' : 'Accept invitation'}
                            </Button>
                            <Button variant="ghost" onClick={declineInvitation} loading={decline.processing}>
                                Decline
                            </Button>
                        </div>
                        {invitationError && (
                            <Alert variant="error" className="mt-3 justify-center">
                                {invitationError}
                            </Alert>
                        )}
                    </div>
                )}

                {invitation.is_pending && (!auth.user || !emailMatches) && (
                    <div className="mt-4">
                        <Button variant="ghost" size="sm" onClick={declineInvitation} loading={decline.processing}>
                            Decline invitation
                        </Button>
                    </div>
                )}
            </div>
        </GuestLayout>
    );
}
