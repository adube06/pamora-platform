import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';

interface AuthUser {
    id: number;
    name: string;
    email: string;
}

interface PageProps {
    auth: {
        user: AuthUser | null;
    };
    flash: {
        success?: string;
        error?: string;
    };
    [key: string]: unknown;
}

export default function AppLayout({ children }: PropsWithChildren) {
    const { auth, flash } = usePage<PageProps>().props;

    return (
        <div className="min-h-screen bg-gray-50">
            <header className="border-b border-gray-200 bg-white">
                <div className="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
                    <Link href={route('occasions.index')} className="font-semibold text-gray-900">
                        Pamora
                    </Link>

                    {auth.user && (
                        <nav className="flex items-center gap-4 text-sm text-gray-600">
                            <span>{auth.user.name}</span>
                            <Link href={route('logout')} method="post" as="button" className="hover:text-gray-900">
                                Log out
                            </Link>
                        </nav>
                    )}
                </div>
            </header>

            {(flash.success || flash.error) && (
                <div className="mx-auto max-w-5xl px-4 pt-4">
                    {flash.success && (
                        <div className="rounded-md bg-green-50 px-4 py-2 text-sm text-green-800">{flash.success}</div>
                    )}
                    {flash.error && (
                        <div className="rounded-md bg-red-50 px-4 py-2 text-sm text-red-800">{flash.error}</div>
                    )}
                </div>
            )}

            <main className="mx-auto max-w-5xl px-4 py-6">{children}</main>
        </div>
    );
}
