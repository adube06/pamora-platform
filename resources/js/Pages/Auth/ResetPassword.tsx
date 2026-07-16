import { Form } from '@inertiajs/react';
import Button from '@/Components/Button';
import FormField from '@/Components/FormField';
import Input from '@/Components/Input';
import GuestLayout from '@/Layouts/GuestLayout';

interface Props {
    token: string;
    email: string | null;
}

export default function ResetPassword({ token, email }: Props) {
    return (
        <GuestLayout title="Choose a new password" subtitle="Keep your Occasion secure with a strong password.">
            <h1 className="text-xl font-semibold text-text-primary">Reset your password</h1>

            <Form action={route('password.update')} method="post" resetOnError={['password', 'password_confirmation']} className="mt-6 space-y-4">
                {({ errors, processing }) => (
                    <>
                        <input type="hidden" name="token" value={token} />

                        <FormField label="Email" htmlFor="email" required error={errors.email}>
                            <Input id="email" name="email" type="email" required defaultValue={email ?? ''} invalid={!!errors.email} />
                        </FormField>

                        <FormField label="New password" htmlFor="password" required error={errors.password}>
                            <Input id="password" name="password" type="password" required invalid={!!errors.password} />
                        </FormField>

                        <FormField label="Confirm password" htmlFor="password_confirmation" required>
                            <Input id="password_confirmation" name="password_confirmation" type="password" required />
                        </FormField>

                        <Button type="submit" loading={processing} className="w-full">
                            {processing ? 'Resetting…' : 'Reset password'}
                        </Button>
                    </>
                )}
            </Form>
        </GuestLayout>
    );
}
