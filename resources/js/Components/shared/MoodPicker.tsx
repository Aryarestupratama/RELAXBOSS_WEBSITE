import { BatteryLow, CloudRain, Frown, Laugh, Meh, Smile, Zap, type LucideIcon } from 'lucide-react';
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group';
import { Button } from '@/Components/ui/button';
import { AROUSAL_LABELS, MOOD_LABELS, type ArousalValue, type MoodValue } from '@/lib/mood';

const MOOD_ICONS: Record<MoodValue, LucideIcon> = {
  1: CloudRain,
  2: Frown,
  3: Meh,
  4: Smile,
  5: Laugh,
};

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

/**
 * CMP-005. Lima pilihan mood (ikon + teks) dan pilihan tenaga opsional.
 * Keyboard lewat tombol panah (radio group). Satu warna aksen (`--brand-strong`), tanpa warna mood.
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
    <div className="space-y-6">
      <fieldset className="space-y-2" disabled={disabled}>
        <legend className="text-base font-medium">Bagaimana perasaanmu sekarang?</legend>
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
            const Icon = MOOD_ICONS[value];
            const id = `mood-${value}`;
            const selected = mood === value;
            return (
              <label
                key={value}
                htmlFor={id}
                className={`flex min-h-24 cursor-pointer flex-col items-center justify-center gap-1.5 rounded-lg border-[1.5px] px-1 py-2 text-center text-xs transition-colors sm:text-sm ${
                  selected ? 'border-primary bg-neutral-soft' : 'border-border bg-card hover:bg-neutral-soft'
                }`}
              >
                <RadioGroupItem id={id} value={String(value)} />
                <Icon className="size-6 text-brand-strong" aria-hidden="true" />
                <span>{MOOD_LABELS[value]}</span>
              </label>
            );
          })}
        </RadioGroup>
      </fieldset>

      <fieldset className="space-y-2" disabled={disabled}>
        <legend className="text-base font-medium">
          Tenagamu <span className="font-normal text-text-secondary">(boleh dilewati)</span>
        </legend>
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
                className={`flex min-h-12 cursor-pointer items-center gap-3 rounded-lg border-[1.5px] px-4 py-2 transition-colors ${
                  selected ? 'border-primary bg-neutral-soft' : 'border-border bg-card hover:bg-neutral-soft'
                }`}
              >
                <RadioGroupItem id={id} value={value} />
                <Icon className="size-5 text-brand-strong" aria-hidden="true" />
                <span>{AROUSAL_LABELS[value]}</span>
              </label>
            );
          })}
        </RadioGroup>
        {arousal !== null ? (
          <Button type="button" variant="ghost" onClick={() => onArousalChange(null)} disabled={disabled}>
            Hapus pilihan tenaga
          </Button>
        ) : null}
      </fieldset>
    </div>
  );
}
