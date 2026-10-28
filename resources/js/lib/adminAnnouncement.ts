export type AnnouncementFormValues = {
  title: string;
  body_markdown: string;
  status: string;
  published_at: string;
};

export type AnnouncementFormData = {
  mode: 'create' | 'edit';
  id: number | null;
  version: string;
  values: AnnouncementFormValues;
  statuses: { value: string; label: string }[];
  body_max_chars: number;
};
