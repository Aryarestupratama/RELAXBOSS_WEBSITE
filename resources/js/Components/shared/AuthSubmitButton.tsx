import type { ReactNode } from 'react';
import { LoaderCircle } from 'lucide-react';

type AuthSubmitButtonProps = {
  processing: boolean;
  disabled?: boolean;
  children: ReactNode;
};

/**
 * Tombol kirim halaman tamu: navy `--primary` setinggi 40px dengan sorot tipis di tepi atas.
 * Hover: naik 1px, beralih ke `--nav-hover`, bayangan mengangkat, dan kilau melintas sekali.
 * Ditekan: mengecil sedikit. Memproses: ikon berputar dan teks "Memproses...".
 */
export default function AuthSubmitButton({ processing, disabled = false, children }: AuthSubmitButtonProps) {
  const inactive = processing || disabled;

  return (
    <button
      type="submit"
      disabled={inactive}
      className="group/auth relative isolate inline-flex h-10 w-full items-center justify-center gap-2 overflow-hidden rounded-lg border-[1.5px] border-primary bg-primary text-sm font-medium text-on-primary inset-shadow-xs inset-shadow-on-primary/15 transition-all duration-300 ease-[cubic-bezier(0.2,0.7,0.2,1)] select-none before:pointer-events-none before:absolute before:inset-y-0 before:-left-1/2 before:-z-10 before:w-1/3 before:-skew-x-20 before:bg-linear-to-r before:from-transparent before:via-on-primary/25 before:to-transparent before:opacity-0 enabled:hover:-translate-y-px enabled:hover:border-nav-hover enabled:hover:bg-nav-hover enabled:hover:shadow-lift-hover enabled:hover:before:translate-x-[420%] enabled:hover:before:opacity-100 enabled:active:translate-y-0 enabled:active:scale-[0.985] enabled:active:shadow-lift disabled:cursor-not-allowed disabled:opacity-50 motion-safe:before:transition-all motion-safe:before:duration-700"
    >
      {processing ? <LoaderCircle className="size-4 motion-safe:animate-spin" aria-hidden="true" /> : null}
      {processing ? 'Memproses...' : children}
    </button>
  );
}
