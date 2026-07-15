import { Form, Link } from '@inertiajs/react';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import FormField from '@/Components/FormField';
import Input from '@/Components/Input';

interface Props {
    invitation?: string | null;
}

export default function Login({ invitation }: Props) {
    return (
        <div className="mx-auto mt-16 max-w-sm">
            <h1 className="text-xl font-semibold text-text-primary">Log in to Pamora</h1>

            <Card className="mt-6">
                <Form action={route('login')} method="post" resetOnError={['password']} className="space-y-4">
                    {({ errors, processing }) => (
                        <>
                            {invitation && <input type="hidden" name="invitation" value={invitation} />}

                            <FormField label="Email" htmlFor="email" required error={errors.email}>
                                <Input id="email" name="email" type="email" required invalid={!!errors.email} />
                            </FormField>

                            <FormField label="Password" htmlFor="password" required error={errors.password}>
                                <Input id="password" name="password" type="password" required invalid={!!errors.password} />
                            </FormField>

                            <Button type="submit" loading={processing} className="w-full">
                                {processing ? 'Logging in…' : 'Log in'}
                            </Button>
                        </>
                    )}
                </Form>
            </Card>

            <p className="mt-4 text-sm text-text-secondary">
                Don&apos;t have an account?{' '}
                <Link href={route('register', invitation ? { invitation } : {})} className="font-medium text-primary underline">
                    Register
                </Link>
            </p>
        </div>
    );
}
