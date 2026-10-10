import { LifeBuoy } from 'lucide-react';

/** Jalan pintas ke bantuan (RULE krisis: selalu terlihat di area pengguna). <a> biasa: Konsultasi adalah halaman Blade. */
export default function HelpPill({ className = '' }: { className?: string }) {
  return (
    <a
      href="/konsultasi"
      className={`inline-flex min-h-11 items-center gap-2 rounded-full bg-crisis-bg px-5 text-sm font-medium text-crisis-text transition-colors hover:bg-crisis-bg/70 ${className}`}
    >
      <LifeBuoy className="size-5" aria-hidden="true" />
      Butuh bantuan sekarang?
    </a>
  );
}
