import { Link } from '@inertiajs/react';
import { HeartHandshake, LifeBuoy, Sparkles, X } from 'lucide-react';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { SUGGESTION_TEXT, type ChatSuggestion } from '@/lib/relaxmate';

type SuggestionCardProps = {
  suggestion: ChatSuggestion;
  onDismiss?: () => void;
};

const linkClass = 'inline-flex min-h-11 items-center font-medium text-brand-strong underline underline-offset-2';

/**
 * CMP-019. Saran tindakan di bawah balasan sesuai prioritas intent (FR-029).
 * Prioritas 1 (`escalate_crisis`) tidak bisa ditutup; lainnya punya tombol tutup.
 */
export default function SuggestionCard({ suggestion, onDismiss }: SuggestionCardProps) {
  const { type } = suggestion;
  const crisis = type === 'escalate_crisis';
  const Icon = crisis ? LifeBuoy : type === 'recommend_professional' ? HeartHandshake : Sparkles;

  return (
    <Alert
      role={crisis ? 'alert' : 'status'}
      className={crisis ? 'border-crisis-text/30 bg-crisis-bg text-crisis-text' : undefined}
    >
      <Icon aria-hidden="true" />
      <AlertDescription className={crisis ? 'text-crisis-text' : undefined}>
        <p>{SUGGESTION_TEXT[type]}</p>
        <div className="flex flex-wrap gap-x-4">
          {crisis ? (
            <>
              <a href="#bantuan" className={linkClass}>
                Lihat kontak bantuan
              </a>
              <a href="/konsultasi" className={linkClass}>
                Konsultasi Profesional
              </a>
            </>
          ) : null}
          {type === 'recommend_professional' ? (
            // <a> biasa: Konsultasi adalah halaman Blade.
            <a href="/konsultasi" className={linkClass}>
              Bicara dengan profesional
            </a>
          ) : null}
          {type === 'recommend_support' ? (
            <>
              <Link href="/app/mood" className={linkClass}>
                Buka Mood Tracker
              </Link>
              <Link href="/app/asesmen" className={linkClass}>
                Lihat asesmen
              </Link>
            </>
          ) : null}
        </div>
      </AlertDescription>
      {suggestion.dismissible && onDismiss ? (
        <Button variant="ghost" size="icon" onClick={onDismiss} aria-label="Tutup saran" className="absolute right-1 top-1">
          <X aria-hidden="true" />
        </Button>
      ) : null}
    </Alert>
  );
}
