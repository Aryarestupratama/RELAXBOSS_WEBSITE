import { Minus } from 'lucide-react';
import { levelFromAverage, MOOD_LABELS, moodImage, weekdayShort, type MoodDay } from '@/lib/mood';

type MoodWeekFacesProps = {
  days: MoodDay[];
  selectedDate: string | null;
};

/**
 * Ringkasan tujuh hari terakhir: satu blob per hari sesuai rata-rata mood hari itu. Hari tanpa
 * catatan berupa lingkaran putus-putus (celah, bukan nol). Hanya tampilan; data yang sama ada di
 * grafik dan tabelnya, jadi pembaca layar mendapat teks lewat daftar ini.
 */
export default function MoodWeekFaces({ days, selectedDate }: MoodWeekFacesProps) {
  const week = days.slice(-7);

  return (
    <ul
      aria-label="Ringkasan mood tujuh hari terakhir"
      className="grid grid-cols-7 gap-1 rounded-xl bg-neutral-soft p-2"
    >
      {week.map((day) => {
        const level = day.average === null ? null : levelFromAverage(day.average);
        const selected = selectedDate === day.date;
        return (
          <li
            key={day.date}
            className={`flex flex-col items-center gap-1 rounded-lg py-2 text-center transition-colors ${
              selected ? 'bg-card shadow-card' : ''
            }`}
          >
            {level === null ? (
              <span
                aria-hidden="true"
                className="grid size-9 place-items-center rounded-full border-2 border-dashed border-input-border text-text-muted sm:size-11"
              >
                <Minus className="size-4" />
              </span>
            ) : (
              <img src={moodImage(level)} width={44} height={44} alt="" className="size-9 object-contain sm:size-11" />
            )}
            <span className={`text-xs ${selected ? 'font-semibold text-text' : 'text-text-secondary'}`}>
              {weekdayShort(day.date)}
            </span>
            <span className="sr-only">
              {day.label}, {level === null ? 'tidak ada catatan' : `rata-rata ${MOOD_LABELS[level]}`}
            </span>
          </li>
        );
      })}
    </ul>
  );
}
