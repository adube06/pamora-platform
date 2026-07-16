import { Link, useForm } from '@inertiajs/react';
import Alert from '@/Components/Alert';
import Button from '@/Components/Button';
import GuestLayout from '@/Layouts/GuestLayout';

export default function VerifyEmail() {
    const { post, processing, wasSuccessful } = useForm({});

    function resend() {
        post(route('verification.send'));
    }

    return (
        <GuestLayout title="Almost there" subtitle="Verify your email to keep your account secure.">
            <h1 className="text-xl font-semibold text-text-primary">Verify your email</h1>
            <p className="mt-2 text-sm text-text-secondary">
                We sent a verification link to your email address. Click the link to verify your account.
            </p>

            <div className="mt-6 space-y-3">
                <Button onClick={resend} loading={processing} className="w-full">
                    {processing ? 'Sending…' : 'Resend verification email'}
                </Button>

                {wasSuccessful && <Alert variant="success">Verification link sent.</Alert>}

                <Link href={route('logout')} method="post" as="button" className="block text-center text-sm text-text-secondary underline">
                    Log out
                </Link>
            </div>
        </GuestLayout>
    );
}
