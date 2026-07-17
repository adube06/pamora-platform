import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import Alert from '@/Components/Alert';
import BrandMark from '@/Components/BrandMark';
import ThemeToggle from '@/Components/ThemeToggle';

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
    unreadNotificationsCount: number;
    [key: string]: unknown;
}

function NotificationBell({ count }: { count: number }) {
    return (
        <Link
            href={route('notifications.index')}
            aria-label={count > 0 ? `Notifications (${count} unread)` : 'Notifications'}
            className="relative inline-flex h-8 w-8 items-center justify-center rounded-lg text-text-secondary hover:bg-background hover:text-text-primary"
        >
            <svg viewBox="0 0 24 24" fill="none" className="h-4 w-4" aria-hidden="true">
                <path
                    stroke="currentColor"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"
                />
            </svg>
            {count > 0 && (
                <span className="absolute top-0.5 right-0.5 inline-flex h-2 w-2 rounded-full bg-error" />
            )}
        </Link>
    );
}

export default function AppLayout({ children }: PropsWithChildren) {
    const { auth, flash, unreadNotificationsCount } = usePage<PageProps>().props;

    return (
        <div className="min-h-screen bg-background">
            <header className="sticky top-0 z-10 border-b border-border bg-surface">
                <div className="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
                    <Link href={route('occasions.index')} className="flex items-center gap-2 font-semibold text-text-primary">
                        <BrandMark />
                        Pamora
                    </Link>

                    <div className="flex items-center gap-3">
                        {auth.user && <NotificationBell count={unreadNotificationsCount} />}
                        <ThemeToggle />
                        {auth.user && (
                            <nav className="flex items-center gap-4 text-sm text-text-secondary">
                                <Link href={route('vendor.index')} className="hover:text-text-primary">
                                    Marketplace
                                </Link>
                                <Link href={route('profile.show')} className="hover:text-text-primary">
                                    {auth.user.name}
                                </Link>
                                <Link href={route('logout')} method="post" as="button" className="hover:text-text-primary">
                                    Log out
                                </Link>
                            </nav>
                        )}
                    </div>
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
