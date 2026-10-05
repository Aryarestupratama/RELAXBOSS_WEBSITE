import type { ReactNode } from 'react';
import { Head } from '@inertiajs/react';
import Logo from '@/Components/shared/Logo';

type GuestLayoutProps = {
  title: string;
  children: ReactNode;
};

export default function GuestLayout({ title, children }: GuestLayoutProps) {
  return (
    <>
      <Head title={`${title} | RelaxBoss`} />
      <div className="flex min-h-screen flex-col items-center px-4 py-8 sm:py-12">
        <Logo />
        <main
          id="konten"
          className="mt-8 w-full max-w-md rounded-xl border border-border bg-card p-6 shadow-card sm:p-8"
        >
          {children}
        </main>
      </div>
    </>
  );
}
