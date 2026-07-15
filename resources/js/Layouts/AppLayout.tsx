import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import Alert from '@/Components/Alert';

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
        <div className="min-h-screen bg-background">
            <header className="border-b border-border bg-surface">
                <div className="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
                    <Link href={route('occasions.index')} className="font-semibold text-text-primary">
                        Pamora
                    </Link>

                    {auth.user && (
                        <nav className="flex items-center gap-4 text-sm text-text-secondary">
                            <span>{auth.user.name}</span>
                            <Link href={route('logout')} method="post" as="button" className="hover:text-text-primary">
                                Log out
                            </Link>
                        </nav>
                    )}
                </div>
            </header>

            {(flash.success || flash.error) && (
                <div className="mx-auto max-w-5xl space-y-2 px-4 pt-4">
                    {flash.success && <Alert variant="success">{flash.success}</Alert>}
                    {flash.error && <Alert variant="error">{flash.error}</Alert>}
                </div>
            )}

            <main className="mx-auto max-w-5xl px-4 py-6">{children}</main>
        </div>
    );
}
