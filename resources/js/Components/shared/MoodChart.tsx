import { useEffect, useRef, useState } from 'react';
import { formatAverage, MOOD_LABELS, type MoodDay, type MoodEntryItem, type MoodValue } from '@/lib/mood';

const HEIGHT = 220;
const MARGIN = { top: 12, right: 14, bottom: 28, left: 28 };
const Y_VALUES = [1, 2, 3, 4, 5];

/** Lebar wadah, agar teks SVG tidak ikut mengecil atau membesar saat layar berubah. */
function useWidth(): [React.RefObject<HTMLDivElement | null>, number] {
  const ref = useRef<HTMLDivElement | null>(null);
  const [width, setWidth] = useState(320);

  useEffect(() => {
    const node = ref.current;
    if (!node) {
      return;
    }
    const update = () => setWidth(Math.max(240, Math.round(node.clientWidth)));
    update();
    const observer = new ResizeObserver(update);
    observer.observe(node);
    return () => observer.disconnect();
  }, []);

  return [ref, width];
}

function yScale(value: number): number {
  const inner = HEIGHT - MARGIN.top - MARGIN.bottom;
  return MARGIN.top + (inner * (5 - value)) / 4;
}

function Grid({ width }: { width: number }) {
  return (
    <>
      {Y_VALUES.map((value) => (
        <g key={value}>
          <line
            x1={MARGIN.left}
            x2={width - MARGIN.right}
            y1={yScale(value)}
            y2={yScale(value)}
            className="stroke-border"
            strokeWidth={1}
          />
          <text x={MARGIN.left - 8} y={yScale(value) + 4} textAnchor="end" fontSize={11} className="fill-text-secondary">
            {value}
          </text>
        </g>
      ))}
    </>
  );
}

type RangeChartProps = {
  days: MoodDay[];
  range: 7 | 30;
  selectedDate: string | null;
  onSelectDay: (date: string) => void;
};

/** Mode 7 atau 30 hari: satu titik per hari (rata-rata). Hari tanpa entri menjadi celah. */
export function MoodRangeChart({ days, range, selectedDate, onSelectDay }: RangeChartProps) {
  const [ref, width] = useWidth();
  const visible = days.slice(-range);
  const innerWidth = width - MARGIN.left - MARGIN.right;
  const xScale = (index: number) => MARGIN.left + (innerWidth * index) / Math.max(1, visible.length - 1);

  // Garis hanya menyambung hari yang berurutan dan sama-sama punya entri.
  const segments: string[] = [];
  let current = '';
  visible.forEach((day, index) => {
    if (day.average === null) {
      if (current) {
        segments.push(current);
        current = '';
      }
      return;
    }
    const point = `${xScale(index).toFixed(1)},${yScale(day.average).toFixed(1)}`;
    current = current ? `${current} L ${point}` : `M ${point}`;
  });
  if (current) {
    segments.push(current);
  }

  const labelEvery = range === 7 ? 1 : 5;

  return (
    <figure className="m-0">
      <div ref={ref} className="w-full">
        <svg width={width} height={HEIGHT} viewBox={`0 0 ${width} ${HEIGHT}`} aria-hidden="true" focusable="false">
          <Grid width={width} />
          {segments.map((path) => (
            <path key={path} d={path} fill="none" className="stroke-brand-strong" strokeWidth={2} strokeLinejoin="round" />
          ))}
          {visible.map((day, index) => {
            const showLabel = index % labelEvery === 0 || index === visible.length - 1;
            return (
              <g key={day.date}>
                {showLabel ? (
                  <text
                    x={xScale(index)}
                    y={HEIGHT - 8}
                    textAnchor={index === 0 ? 'start' : index === visible.length - 1 ? 'end' : 'middle'}
                    fontSize={11}
                    className="fill-text-secondary"
                  >
                    {day.label}
                  </text>
                ) : null}
                {day.average !== null ? (
                  <g className="cursor-pointer" onClick={() => onSelectDay(day.date)}>
                    <circle cx={xScale(index)} cy={yScale(day.average)} r={14} fill="transparent" />
                    <circle
                      cx={xScale(index)}
                      cy={yScale(day.average)}
                      r={selectedDate === day.date ? 6 : 4.5}
                      className={`fill-brand-strong ${selectedDate === day.date ? 'stroke-text' : 'stroke-card'}`}
                      strokeWidth={2}
                    />
                  </g>
                ) : null}
              </g>
            );
          })}
        </svg>
      </div>
      <figcaption className="mt-1 text-sm text-text-secondary">
        Skala 1 ({MOOD_LABELS[1]}) sampai 5 ({MOOD_LABELS[5]}). Satu titik per hari adalah rata-rata catatanmu.
      </figcaption>
      <table className="sr-only">
        <caption>Rata-rata mood per hari, {range} hari terakhir</caption>
        <thead>
          <tr>
            <th scope="col">Tanggal</th>
            <th scope="col">Rata-rata mood (1 sampai 5)</th>
            <th scope="col">Jumlah catatan</th>
          </tr>
        </thead>
        <tbody>
          {visible.map((day) => (
            <tr key={day.date}>
              <th scope="row">{day.label}</th>
              <td>{day.average === null ? 'Tidak ada catatan' : formatAverage(day.average)}</td>
              <td>{day.entries.length}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </figure>
  );
}

type DayChartProps = {
  label: string;
  entries: MoodEntryItem[];
};

const DAY_TICKS = [0, 6, 12, 18, 24];

/** Mode hari: semua entri hari itu di sumbu jam WIB. */
export function MoodDayChart({ label, entries }: DayChartProps) {
  const [ref, width] = useWidth();
  const innerWidth = width - MARGIN.left - MARGIN.right;
  const xScale = (minutes: number) => MARGIN.left + (innerWidth * minutes) / 1440;

  const path = entries
    .map((entry, index) => `${index === 0 ? 'M' : 'L'} ${xScale(entry.minutes).toFixed(1)},${yScale(entry.mood).toFixed(1)}`)
    .join(' ');

  return (
    <figure className="m-0">
      <div ref={ref} className="w-full">
        <svg width={width} height={HEIGHT} viewBox={`0 0 ${width} ${HEIGHT}`} aria-hidden="true" focusable="false">
          <Grid width={width} />
          {DAY_TICKS.map((hour) => (
            <text
              key={hour}
              x={xScale(hour * 60)}
              y={HEIGHT - 8}
              textAnchor={hour === 0 ? 'start' : hour === 24 ? 'end' : 'middle'}
              fontSize={11}
              className="fill-text-secondary"
            >
              {`${String(hour === 24 ? 0 : hour).padStart(2, '0')}.00`}
            </text>
          ))}
          {entries.length > 1 ? (
            <path d={path} fill="none" className="stroke-brand-strong" strokeWidth={2} strokeLinejoin="round" />
          ) : null}
          {entries.map((entry) => (
            <circle
              key={entry.id}
              cx={xScale(entry.minutes)}
              cy={yScale(entry.mood)}
              r={5}
              className="fill-brand-strong stroke-card"
              strokeWidth={2}
            />
          ))}
        </svg>
      </div>
      <figcaption className="mt-1 text-sm text-text-secondary">
        Semua catatan {label} sesuai jam (WIB). Skala 1 sampai 5.
      </figcaption>
      <table className="sr-only">
        <caption>Catatan mood {label}</caption>
        <thead>
          <tr>
            <th scope="col">Jam</th>
            <th scope="col">Mood</th>
          </tr>
        </thead>
        <tbody>
          {entries.map((entry) => (
            <tr key={entry.id}>
              <th scope="row">{entry.time}</th>
              <td>{`${entry.mood_label} (${entry.mood})`}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </figure>
  );
}
