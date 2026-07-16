import { Form, Link } from '@inertiajs/react';
import Alert from '@/Components/Alert';
import Button from '@/Components/Button';
import FormField from '@/Components/FormField';
import Input from '@/Components/Input';
import GuestLayout from '@/Layouts/GuestLayout';

export default function ForgotPassword() {
    return (
        <GuestLayout title="Forgot your password?" subtitle="We'll email you a link to reset it.">
            <h1 className="text-xl font-semibold text-text-primary">Reset your password</h1>
            <p className="mt-2 text-sm text-text-secondary">Enter your email and we'll send you a password reset link.</p>

            <Form action={route('password.email')} method="post" className="mt-6 space-y-4">
                {({ errors, processing, wasSuccessful }) => (
                    <>
                        <FormField label="Email" htmlFor="email" required error={errors.email}>
                            <Input id="email" name="email" type="email" required invalid={!!errors.email} />
                        </FormField>

                        <Button type="submit" loading={processing} className="w-full">
                            {processing ? 'Sending…' : 'Send reset link'}
                        </Button>

                        {wasSuccessful && <Alert variant="success">Check your email for a reset link.</Alert>}
                    </>
                )}
            </Form>

            <p className="mt-4 text-sm text-text-secondary">
                <Link href={route('login')} className="font-medium text-primary underline">
                    Back to login
                </Link>
            </p>
        </GuestLayout>
    );
}
