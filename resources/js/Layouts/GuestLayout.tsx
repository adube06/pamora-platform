import type { PropsWithChildren } from 'react';

interface Props extends PropsWithChildren {
    title: string;
    subtitle?: string;
}

export default function GuestLayout({ title, subtitle, children }: Props) {
    return (
        <div className="flex min-h-screen">
            <div className="hidden flex-1 flex-col justify-between bg-primary p-12 text-white lg:flex">
                <span className="text-lg font-semibold">Pamora</span>
                <div>
                    <h2 className="text-3xl font-semibold">{title}</h2>
                    {subtitle && <p className="mt-3 text-white/80">{subtitle}</p>}
                </div>
                <span className="text-xs text-white/60">Pamora</span>
            </div>

            <div className="flex flex-1 items-center justify-center bg-background px-6 py-12">
                <div className="w-full max-w-sm">{children}</div>
            </div>
        </div>
    );
}
