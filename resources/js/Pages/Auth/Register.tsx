import { Form, Link } from '@inertiajs/react';

interface Props {
    invitation?: string | null;
}

export default function Register({ invitation }: Props) {
    return (
        <div className="mx-auto mt-16 max-w-sm">
            <h1 className="text-xl font-semibold text-gray-900">Create your account</h1>

            <Form action={route('register')} method="post" resetOnError className="mt-6 space-y-4">
                {({ errors, processing }) => (
                    <>
                        {invitation && <input type="hidden" name="invitation" value={invitation} />}

                        <div>
                            <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                Name
                            </label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                required
                                className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                            />
                            {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                        </div>

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

                        <div>
                            <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">
                                Confirm password
                            </label>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                        >
                            {processing ? 'Creating account…' : 'Create account'}
                        </button>
                    </>
                )}
            </Form>

            <p className="mt-4 text-sm text-gray-600">
                Already have an account?{' '}
                <Link
                    href={route('login', invitation ? { invitation } : {})}
                    className="font-medium text-gray-900 underline"
                >
                    Log in
                </Link>
            </p>
        </div>
    );
}
