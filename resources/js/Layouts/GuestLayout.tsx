import type { PropsWithChildren } from 'react';
import BrandMark from '@/Components/BrandMark';
import ThemeToggle from '@/Components/ThemeToggle';

interface Props extends PropsWithChildren {
    title: string;
    subtitle?: string;
}

export default function GuestLayout({ title, subtitle, children }: Props) {
    return (
        <div className="flex min-h-screen">
            <div className="relative hidden flex-1 flex-col justify-between overflow-hidden bg-gradient-to-br from-primary to-secondary p-12 text-white lg:flex">
                <div className="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-white/10 blur-3xl" aria-hidden="true" />
                <div className="pointer-events-none absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-black/10 blur-3xl" aria-hidden="true" />
                <div className="pointer-events-none absolute top-1/3 left-1/2 h-56 w-56 rounded-full bg-white/5 blur-3xl" aria-hidden="true" />

                <div className="relative flex items-center gap-2">
                    <BrandMark variant="inverted" />
                    <span className="text-lg font-semibold">Pamora</span>
                </div>
                <div className="relative">
                    <h2 className="text-3xl font-semibold">{title}</h2>
                    {subtitle && <p className="mt-3 text-white/80">{subtitle}</p>}
                </div>
                <span className="relative text-xs text-white/60">Pamora</span>
            </div>

            <div className="flex flex-1 flex-col bg-background">
                <div className="flex justify-end px-6 py-4">
                    <ThemeToggle />
                </div>
                <div className="flex flex-1 items-center justify-center px-6 pb-12">
                    <div className="w-full max-w-sm">{children}</div>
                </div>
            </div>
        </div>
    );
}
