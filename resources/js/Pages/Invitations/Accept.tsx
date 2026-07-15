import { Link, useForm, usePage } from '@inertiajs/react';

interface InvitationProps {
    token: string;
    email: string;
    status: string;
    is_pending: boolean;
    responsibilities: string[];
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

    // "invitation" is a business-rule validation key returned by
    // AcceptInvitationService (e.g. expired/revoked/wrong-email) — it has
    // no corresponding form field, so it isn't part of useForm's inferred
    // error type.
    const invitationError = (errors as Record<string, string>).invitation;

    const emailMatches = auth.user?.email.toLowerCase() === invitation.email.toLowerCase();

    function accept() {
        post(route('invitations.accept', invitation.token));
    }

    return (
        <div className="mx-auto mt-16 max-w-md text-center">
            <h1 className="text-xl font-semibold text-gray-900">
                You&apos;re invited to {invitation.occasion.title}
            </h1>
            <p className="mt-2 text-sm text-gray-600">
                {invitation.occasion.type} · Invited as {invitation.responsibilities.length > 0 ? invitation.responsibilities.join(', ') : 'a committee member'}
            </p>

            {!invitation.is_pending && (
                <p className="mt-6 text-sm text-red-600">
                    This invitation is no longer valid. Ask the Host to send a new one.
                </p>
            )}

            {invitation.is_pending && !auth.user && (
                <div className="mt-6 space-y-3">
                    <p className="text-sm text-gray-600">Create an account or log in with {invitation.email} to accept.</p>
                    <div className="flex justify-center gap-3">
                        <Link
                            href={route('register', { invitation: invitation.token })}
                            className="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white"
                        >
                            Create account
                        </Link>
                        <Link
                            href={route('login', { invitation: invitation.token })}
                            className="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
                        >
                            Log in
                        </Link>
                    </div>
                </div>
            )}

            {invitation.is_pending && auth.user && !emailMatches && (
                <p className="mt-6 text-sm text-red-600">
                    This invitation was sent to {invitation.email}, but you&apos;re logged in as {auth.user.email}.
                </p>
            )}

            {invitation.is_pending && auth.user && emailMatches && (
                <div className="mt-6">
                    <button
                        onClick={accept}
                        disabled={processing}
                        className="rounded-md bg-gray-900 px-6 py-2 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {processing ? 'Joining…' : 'Accept invitation'}
                    </button>
                    {invitationError && <p className="mt-2 text-sm text-red-600">{invitationError}</p>}
                </div>
            )}
        </div>
    );
}
