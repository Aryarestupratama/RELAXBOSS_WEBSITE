/** Prop bersama `notifications` (HandleInertiaRequests): hanya hitungan untuk lonceng, tanpa isi (ADR-019). */
export type SharedNotificationProps = {
  notifications?: { unread: number } | null;
};

export type NotificationKind =
  | 'mood_reminder'
  | 'nudge_assessment'
  | 'nudge_relaxmate'
  | 'nudge_article'
  | 'announcement';

/** Pengingat mood dan nudge: satu kalimat dan satu tautan aksi. */
export type ActionNotification = {
  key: string;
  kind: Exclude<NotificationKind, 'announcement'>;
  text: string;
  action_label: string;
  href: string;
  /** Tujuan halaman Blade (`/artikel`): pakai `<a>` native, bukan Link Inertia. */
  native: boolean;
  dismissible: boolean;
};

/** Pengumuman pembaruan website; `html` sudah dirender dan disaring di server (`ArticleRenderer`). */
export type AnnouncementNotification = {
  key: string;
  kind: 'announcement';
  title: string;
  html: string;
  date_label: string | null;
  unread: boolean;
};

export type NotificationItem = ActionNotification | AnnouncementNotification;

export type NotificationPreferences = {
  mood_reminder_enabled: boolean;
  mood_reminder_hour: number;
  nudges_enabled: boolean;
  hour_min: number;
  hour_max: number;
};

/** Isi pengumuman di kartu: lebih kecil daripada ARTICLE_PROSE_CLASS (artikel). Hanya token (RULE-050). */
export const ANNOUNCEMENT_PROSE_CLASS =
  'text-sm leading-relaxed text-text [&>*:first-child]:mt-0 [&_h2]:mt-4 [&_h2]:text-base [&_h2]:font-semibold [&_h3]:mt-3 [&_h3]:text-sm [&_h3]:font-semibold [&_p]:mt-3 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:mt-3 [&_ol]:list-decimal [&_ol]:pl-5 [&_li]:mt-1 [&_strong]:font-semibold [&_a]:font-medium [&_a]:text-primary [&_a]:underline [&_a]:underline-offset-4 [&_blockquote]:mt-3 [&_blockquote]:rounded-lg [&_blockquote]:border-l-4 [&_blockquote]:border-primary [&_blockquote]:bg-surface-alt [&_blockquote]:px-4 [&_blockquote]:py-2 [&_hr]:my-4 [&_hr]:border-border';
