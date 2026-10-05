import '../css/app.css';
import type { ComponentType } from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

type PageModule = { default: ComponentType };

const pages = import.meta.glob<PageModule>('./Pages/**/*.tsx');

// Nonce CSP dari server (meta di app.blade.php); dipakai Inertia untuk <style> yang disuntik lewat JS.
const nonce = document.querySelector<HTMLMetaElement>('meta[name="csp-nonce"]')?.content;

createInertiaApp({
  nonce,
  resolve: async (name) => {
    const loader = pages[`./Pages/${name}.tsx`];
    if (!loader) {
      throw new Error(`Halaman tidak ditemukan: ${name}`);
    }
    const module = await loader();
    return module.default;
  },
  setup({ el, App, props }) {
    createRoot(el).render(<App {...props} />);
  },
});