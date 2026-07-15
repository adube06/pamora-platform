import { Form, Link } from '@inertiajs/react';

interface Props {
    invitation?: string | null;
}

export default function Login({ invitation }: Props) {
    return (
        <div className="mx-auto mt-16 max-w-sm">
            <h1 className="text-xl font-semibold text-gray-900">Log in to Pamora</h1>

            <Form action={route('login')} method="post" resetOnError={['password']} className="mt-6 space-y-4">
                {({ errors, processing }) => (
                    <>
                        {invitation && <input type="hidden" name="invitation" value={invitation} />}

                        <div>
                            <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                                Email
                            </label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                required
                                className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                            />
                            {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
                        </div>

                        <div>
                            <label htmlFor="password" className="block text-sm font-medium text-gray-700">
                                Password
                            </label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                            />
                            {errors.password && <p className="mt-1 text-sm text-red-600">{errors.password}</p>}
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                        >
                            {processing ? 'Logging in…' : 'Log in'}
                        </button>
                    </>
                )}
            </Form>

            <p className="mt-4 text-sm text-gray-600">
                Don&apos;t have an account?{' '}
                <Link
                    href={route('register', invitation ? { invitation } : {})}
                    className="font-medium text-gray-900 underline"
                >
                    Register
                </Link>
            </p>
        </div>
    );
}
