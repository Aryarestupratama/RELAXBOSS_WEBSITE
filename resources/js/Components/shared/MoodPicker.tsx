import { BatteryLow, Check, Zap, type LucideIcon } from 'lucide-react';
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group';
import { Button } from '@/Components/ui/button';
import { FieldLegend, FieldSet } from '@/Components/ui/field';
import { cn } from '@/lib/utils';
import { AROUSAL_LABELS, MOOD_LABELS, moodImage, type ArousalValue, type MoodValue } from '@/lib/mood';

const MOOD_VALUES: MoodValue[] = [1, 2, 3, 4, 5];

const AROUSAL_ICONS: Record<ArousalValue, LucideIcon> = {
  energized: Zap,
  tired: BatteryLow,
};

const AROUSAL_VALUES: ArousalValue[] = ['energized', 'tired'];

type MoodPickerProps = {
  mood: MoodValue | null;
  arousal: ArousalValue | null;
  onMoodChange: (value: MoodValue) => void;
  onArousalChange: (value: ArousalValue | null) => void;
  disabled?: boolean;
  /** Galat untuk pilihan mood (mis. belum memilih); dibaca pembaca layar lewat aria-describedby. */
  errorId?: string;
  invalid?: boolean;
};

/** Penanda terpilih berupa ikon centang (bukan hanya warna), di atas `--primary` sehingga teksnya putih (RULE-052). */
function SelectedMark({ className }: { className?: string }) {
  return (
    <span
      aria-hidden="true"
      className={cn('absolute grid size-5 place-items-center rounded-full bg-primary text-on-primary shadow-card', className)}
    >
      <Check className="size-3" strokeWidth={3} />
    </span>
  );
}

/**
 * CMP-005. Lima pilihan mood (blob berwajah + teks, tidak bergantung pada warna) dan pilihan tenaga opsional.
 * Gaya shadcn: `FieldSet` + `FieldLegend` di atas `RadioGroup`; kartu pilihan memakai label yang membungkus radio
 * (radio disembunyikan secara visual tetapi tetap fokus dan terbaca pembaca layar). Keyboard lewat tombol panah.
 * Terpilih = garis `--primary` 2px, latar `--soft-lavender`, teks tebal, dan ikon centang.
 */
export default function MoodPicker({
  mood,
  arousal,
  onMoodChange,
  onArousalChange,
  disabled = false,
  errorId,
  invalid = false,
}: MoodPickerProps) {
  return (
    <div className="space-y-5">
      <FieldSet disabled={disabled}>
        <FieldLegend>Bagaimana perasaanmu sekarang?</FieldLegend>
        <RadioGroup
          name="mood"
          aria-label="Perasaanmu sekarang"
          aria-invalid={invalid}
          aria-describedby={errorId}
          value={mood === null ? '' : String(mood)}
          onValueChange={(next) => onMoodChange(Number(next) as MoodValue)}
          className="grid-cols-5 gap-2"
          disabled={disabled}
        >
          {MOOD_VALUES.map((value) => {
            const id = `mood-${value}`;
            const selected = mood === value;
            return (
              <label
                key={value}
                htmlFor={id}
                className={cn(
                  'group relative flex min-h-24 cursor-pointer flex-col items-center justify-center gap-1 rounded-2xl px-1 py-2.5 text-center text-xs text-text transition-all duration-200 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-ring motion-safe:hover:-translate-y-0.5',
                  selected
                    ? 'border-2 border-primary bg-soft-lavender font-semibold shadow-card'
                    : cn(
                        'border-[1.5px] bg-card hover:bg-soft-lavender/50 hover:shadow-card',
                        invalid ? 'border-destructive' : 'border-border',
                      ),
                )}
              >
                <RadioGroupItem id={id} value={String(value)} className="sr-only" />
                {selected ? <SelectedMark className="top-1 right-1" /> : null}
                <img
                  src={moodImage(value)}
                  width={48}
                  height={48}
                  alt=""
                  className="size-9 object-contain transition-transform duration-200 motion-safe:group-hover:scale-110 sm:size-10"
                />
                <span>{MOOD_LABELS[value]}</span>
              </label>
            );
          })}
        </RadioGroup>
      </FieldSet>

      <FieldSet disabled={disabled}>
        <FieldLegend className="mb-0">
          Tenagamu <span className="font-normal text-text-secondary">(boleh dilewati)</span>
        </FieldLegend>
        <RadioGroup
          name="arousal_input"
          aria-label="Tenagamu"
          value={arousal ?? ''}
          onValueChange={(next) => onArousalChange(next as ArousalValue)}
          className="grid-cols-2 gap-2"
          disabled={disabled}
        >
          {AROUSAL_VALUES.map((value) => {
            const Icon = AROUSAL_ICONS[value];
            const id = `arousal-${value}`;
            const selected = arousal === value;
            return (
              <label
                key={value}
                htmlFor={id}
                className={cn(
                  'relative flex min-h-12 cursor-pointer items-center gap-2.5 rounded-2xl px-3.5 py-2 text-sm text-text transition-all duration-200 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-ring',
                  selected
                    ? 'border-2 border-primary bg-soft-lavender font-semibold'
                    : 'border-[1.5px] border-border bg-card hover:bg-soft-lavender/50',
                )}
              >
                <RadioGroupItem id={id} value={value} className="sr-only" />
                <Icon className="size-5 shrink-0 text-primary" aria-hidden="true" />
                <span>{AROUSAL_LABELS[value]}</span>
                {selected ? <SelectedMark className="top-1/2 right-3 -translate-y-1/2" /> : null}
              </label>
            );
          })}
        </RadioGroup>
        {arousal !== null ? (
          <Button
            type="button"
            variant="ghost"
            className="w-fit rounded-full! text-[0.8125rem]"
            onClick={() => onArousalChange(null)}
            disabled={disabled}
          >
            Hapus pilihan tenaga
          </Button>
        ) : null}
      </FieldSet>
    </div>
  );
}
