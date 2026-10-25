/**
 * Kelas bersama halaman tamu (Masuk, Daftar, Lupa Sandi, Reset Sandi, Verifikasi Email).
 * Hanya token dari app.css (RULE-050): tanpa heksadesimal.
 */

/** Input ringkas 40px; fokus memberi lingkar lavender lembut di samping cincin fokus global. */
export const AUTH_INPUT =
  'min-h-10 px-3 py-1.5 text-sm transition-[border-color,box-shadow] duration-200 hover:border-primary focus-visible:border-brand-strong focus-visible:shadow-[0_0_0_4px_var(--soft-lavender)]';

/** Tautan teks: garis bawah lembut yang menebal dan berubah ke --nav-hover saat hover. */
export const AUTH_LINK =
  'font-medium text-brand-strong underline decoration-brand-strong/40 underline-offset-4 transition-colors duration-200 hover:text-nav-hover hover:decoration-nav-hover';
