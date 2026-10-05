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
};

/** CMP-003. Satu butir per kartu; pilihan dari `assessments.options`; keyboard lewat tombol panah. */
export default function LikertScale({ name, legend, options, value, onChange }: LikertScaleProps) {
  return (
    <RadioGroup
      name={name}
      aria-label={legend}
      value={value === null ? '' : String(value)}
      onValueChange={(next) => onChange(Number(next))}
    >
      {options.map((option) => {
        const id = `${name}-${option.value}`;
        const selected = value === option.value;
        return (
          <label
            key={option.value}
            htmlFor={id}
            className={`flex min-h-12 cursor-pointer items-center gap-3 rounded-lg border-[1.5px] px-4 py-3 transition-colors ${
              selected ? 'border-primary bg-neutral-soft' : 'border-border bg-card hover:bg-neutral-soft'
            }`}
          >
            <RadioGroupItem id={id} value={String(option.value)} />
            <span>{option.label}</span>
          </label>
        );
      })}
    </RadioGroup>
  );
}
