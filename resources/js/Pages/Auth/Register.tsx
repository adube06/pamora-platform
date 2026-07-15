import { Form, Link } from '@inertiajs/react';
import Button from '@/Components/Button';
import FormField from '@/Components/FormField';
import Input from '@/Components/Input';
import GuestLayout from '@/Layouts/GuestLayout';

interface Props {
    invitation?: string | null;
}

export default function Register({ invitation }: Props) {
    return (
        <GuestLayout title="Get started" subtitle="Create an account to start planning.">
            <h1 className="text-xl font-semibold text-text-primary">Create your account</h1>

            <Form action={route('register')} method="post" resetOnError className="mt-6 space-y-4">
                {({ errors, processing }) => (
                    <>
                        {invitation && <input type="hidden" name="invitation" value={invitation} />}

                        <FormField label="Name" htmlFor="name" required error={errors.name}>
                            <Input id="name" name="name" type="text" required invalid={!!errors.name} />
                        </FormField>

                        <FormField label="Email" htmlFor="email" required error={errors.email}>
                            <Input id="email" name="email" type="email" required invalid={!!errors.email} />
                        </FormField>

                        <FormField label="Password" htmlFor="password" required error={errors.password}>
                            <Input id="password" name="password" type="password" required invalid={!!errors.password} />
                        </FormField>

                        <FormField label="Confirm password" htmlFor="password_confirmation" required>
                            <Input id="password_confirmation" name="password_confirmation" type="password" required />
                        </FormField>

                        <Button type="submit" loading={processing} className="w-full">
                            {processing ? 'Creating account…' : 'Create account'}
                        </Button>
                    </>
                )}
            </Form>

            <p className="mt-4 text-sm text-text-secondary">
                Already have an account?{' '}
                <Link href={route('login', invitation ? { invitation } : {})} className="font-medium text-primary underline">
                    Log in
                </Link>
            </p>
        </GuestLayout>
    );
}
