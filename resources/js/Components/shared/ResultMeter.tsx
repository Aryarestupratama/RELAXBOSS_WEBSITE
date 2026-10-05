import { severityOf, type SeverityLevel } from '@/lib/assessment';

type ResultMeterProps = {
  subScale: string;
  score: number;
  maxScore: number;
  interpretation: string;
  severity: string;
};

// Kelas ditulis utuh agar terdeteksi Tailwind. Tingkat tertinggi memakai --warning (Design SCR-014).
const FILL: Record<SeverityLevel, string> = {
  normal: 'fill-success',
  mild: 'fill-brand-strong',
  moderate: 'fill-brand-strong',
  severe: 'fill-warning',
};

/**
 * CMP-004. Satu meter per subskala. Warna bukan satu-satunya penanda: ada teks setara
 * (aria-label dan keterangan skor di bawah meter).
 */
export default function ResultMeter({ subScale, score, maxScore, interpretation, severity }: ResultMeterProps) {
  const ratio = maxScore > 0 ? Math.min(score / maxScore, 1) : 0;
  const percent = score > 0 ? Math.max(ratio * 100, 4) : 0;
  const label = `${subScale}: skor ${score} dari ${maxScore}, ${interpretation}`;

  return (
    <div>
      <svg role="img" aria-label={label} width="100%" height="12" className="block">
        <rect width="100%" height="12" rx="6" className="fill-neutral-soft" />
        {percent > 0 ? (
          <rect width={`${percent}%`} height="12" rx="6" className={FILL[severityOf(severity)]} />
        ) : null}
      </svg>
      <p className="mt-1.5 text-sm text-text-secondary" aria-hidden="true">
        Skor {score} dari {maxScore}
      </p>
    </div>
  );
}
