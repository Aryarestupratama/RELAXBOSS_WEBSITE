import '../css/app.css';
import type { ComponentType } from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

type PageModule = { default: ComponentType };

const pages = import.meta.glob<PageModule>('./Pages/**/*.tsx');

createInertiaApp({
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