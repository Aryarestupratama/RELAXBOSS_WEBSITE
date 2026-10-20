export type ArticleFormValues = {
  slug: string;
  title: string;
  excerpt: string;
  meta_title: string;
  meta_description: string;
  body_markdown: string;
  category: string;
  related_assessment_slug: string;
  author_name: string;
  status: string;
  published_at: string;
};

export type ArticleFormData = {
  mode: 'create' | 'edit';
  id: number | null;
  version: string;
  is_demo: boolean;
  public_url: string | null;
  values: ArticleFormValues;
  categories: { value: string; label: string }[];
  statuses: { value: string; label: string }[];
  assessments: { slug: string; name: string }[];
};

export type FormErrors = Partial<Record<string, string>>;

export const STATUS_TEXT: Record<string, string> = {
  'article-created': 'Artikel dibuat. Kamu bisa melanjutkan mengubahnya di sini.',
  'article-updated': 'Perubahan disimpan.',
};

export const STATE_LABEL: Record<string, string> = {
  draft: 'Draf',
  scheduled: 'Terjadwal',
  live: 'Terbit',
};

/** Gaya isi artikel (sama dengan komponen Blade `article-prose`), hanya token. */
export const ARTICLE_PROSE_CLASS =
  'text-lg leading-8 text-text [&>*:first-child]:mt-0 [&_h2]:mt-10 [&_h2]:mb-3 [&_h2]:text-2xl [&_h2]:leading-snug [&_h2]:font-semibold [&_h3]:mt-8 [&_h3]:mb-2 [&_h3]:text-xl [&_h3]:font-semibold [&_p]:mt-4 [&_ul]:mt-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:mt-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:mt-1.5 [&_strong]:font-semibold [&_a]:font-medium [&_a]:text-brand-strong [&_a]:underline [&_a]:underline-offset-4 [&_blockquote]:mt-6 [&_blockquote]:rounded-lg [&_blockquote]:border-l-4 [&_blockquote]:border-brand [&_blockquote]:bg-neutral-soft [&_blockquote]:px-5 [&_blockquote]:py-3 [&_hr]:my-8 [&_hr]:border-border [&_img]:h-auto [&_img]:max-w-full [&_table]:mt-4 [&_table]:block [&_table]:overflow-x-auto [&_th]:border [&_th]:border-border [&_th]:px-3 [&_th]:py-2 [&_th]:text-left [&_td]:border [&_td]:border-border [&_td]:px-3 [&_td]:py-2';
