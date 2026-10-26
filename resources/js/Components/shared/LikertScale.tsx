import { Check } from 'lucide-react';
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group';
import { cn } from '@/lib/utils';

export type LikertOption = {
  value: number;
  label: string;
};

type LikertScaleProps = {
  /** Dipakai untuk id unik tiap pilihan dan name grup. */
  name: string;
  /** Teks butir; menjadi nama grup untuk pembaca layar. */
  legend: string;
  options: LikertOption[];
  value: number | null;
  onChange: (value: number) => void;
  disabled?: boolean;
};

/**
 * CMP-003. Satu butir per layar; pilihan dari `assessments.options`; keyboard lewat tombol panah.
 * Kartu radio gaya shadcn: radio asli disembunyikan secara visual (tetap fokus dan terbaca pembaca layar),
 * diganti penanda bundar di kiri. Terpilih: garis `--primary`, latar `--soft-lavender`, teks tebal, dan
 * penanda terisi dengan centang (tidak hanya warna). Teks selalu `--text` (RULE-052).
 */
export default function LikertScale({ name, legend, options, value, onChange, disabled = false }: LikertScaleProps) {
  return (
    <RadioGroup
      className="gap-2!"
      name={name}
      aria-label={legend}
      value={value === null ? '' : String(value)}
      onValueChange={(next) => onChange(Number(next))}
      disabled={disabled}
    >
      {options.map((option) => {
        const id = `${name}-${option.value}`;
        const selected = value === option.value;
        return (
          <label
            key={option.value}
            htmlFor={id}
            className={cn(
              'relative flex min-h-12 cursor-pointer items-center gap-3 rounded-2xl border-[1.5px] px-4 py-2.5 text-sm text-text transition-all duration-200 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-ring motion-safe:hover:translate-x-0.5',
              selected
                ? 'border-primary bg-soft-lavender font-semibold shadow-card'
                : 'border-border bg-card hover:border-primary/40 hover:bg-soft-lavender/50',
              disabled && 'cursor-not-allowed opacity-60 motion-safe:hover:translate-x-0',
            )}
          >
            <RadioGroupItem id={id} value={String(option.value)} className="sr-only" />
            <span
              aria-hidden="true"
              className={cn(
                'grid size-5 shrink-0 place-items-center rounded-full border-[1.5px] transition-colors',
                selected ? 'border-primary bg-primary text-on-primary' : 'border-input-border bg-card',
              )}
            >
              {selected ? <Check className="size-3" strokeWidth={3} /> : null}
            </span>
            <span className="flex-1">{option.label}</span>
          </label>
        );
      })}
    </RadioGroup>
  );
}
