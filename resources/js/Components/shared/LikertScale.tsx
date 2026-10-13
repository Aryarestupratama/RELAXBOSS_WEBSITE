import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group';

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
 * Pilihan terpilih ditandai garis `--primary`, latar `--mood-yellow`, dan teks tebal (tidak hanya warna).
 */
export default function LikertScale({ name, legend, options, value, onChange, disabled = false }: LikertScaleProps) {
  return (
    <RadioGroup
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
            className={`flex min-h-14 cursor-pointer items-center gap-4 rounded-2xl border-[1.5px] px-5 py-3 text-base transition-colors ${
              selected
                ? 'border-primary bg-mood-yellow font-semibold'
                : 'border-border bg-card hover:bg-neutral-soft'
            } ${disabled ? 'cursor-not-allowed opacity-60' : ''}`}
          >
            <RadioGroupItem id={id} value={String(option.value)} />
            <span>{option.label}</span>
          </label>
        );
      })}
    </RadioGroup>
  );
}
