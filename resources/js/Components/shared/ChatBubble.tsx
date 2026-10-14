import type { ReactNode } from 'react';
import TypewriterText from '@/Components/shared/TypewriterText';
import type { ChatMessage } from '@/lib/relaxmate';

type ChatBubbleProps = {
  message: Pick<ChatMessage, 'role' | 'content' | 'time' | 'pending'>;
  /** Masuk dengan gerak halus (balasan baru). Dimatikan otomatis bila pengguna meminta gerak dikurangi. */
  animate?: boolean;
  /** Teks tampil bertahap seperti diketik (balasan baru). */
  typewriter?: boolean;
  onTypingTick?: () => void;
  onTypingDone?: () => void;
  /** Saran tindakan (CMP-019) yang tampil tepat di bawah balasan. */
  children?: ReactNode;
};

/**
 * CMP-007 (bagian pesan). Pesan pengguna rata kanan (ungu muda, teks `--text`), balasan rata kiri (kartu berbingkai).
 * Teks biasa: React meng-escape isi, `whitespace-pre-wrap` menjaga baris baru; tanpa HTML atau Markdown (Architecture 6.3).
 */
export default function ChatBubble({
  message,
  animate = false,
  typewriter = false,
  onTypingTick,
  onTypingDone,
  children,
}: ChatBubbleProps) {
  const mine = message.role === 'user';

  return (
    <li
      className={`flex flex-col gap-2 ${mine ? 'items-end' : 'items-start'} ${
        animate ? 'motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 motion-safe:duration-300' : ''
      }`}
    >
      <div
        className={`max-w-[88%] rounded-2xl px-4 py-3 md:max-w-[75%] ${
          mine
            ? 'rounded-br-md bg-brand-soft text-text'
            : 'rounded-bl-md border border-border bg-card text-card-foreground shadow-card'
        } ${message.pending ? 'opacity-70' : ''}`}
      >
        <span className="sr-only">{mine ? 'Kamu' : 'RelaxMate'}: </span>
        <p className="whitespace-pre-wrap break-words">
          {typewriter ? (
            <TypewriterText text={message.content} onTick={onTypingTick} onDone={onTypingDone} />
          ) : (
            message.content
          )}
        </p>
        {message.time ? <p className={`mt-1 text-xs ${mine ? 'text-text' : 'text-text-muted'}`}>{message.time}</p> : null}
      </div>
      {children ? <div className="w-full max-w-[88%] md:max-w-[75%]">{children}</div> : null}
    </li>
  );
}
