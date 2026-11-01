import type { ReactNode } from 'react';
import RelaxMateAvatar from '@/Components/shared/RelaxMateAvatar';
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
  /** Balasan RelaxMate: tampilkan avatar (balasan beruntun hanya yang pertama; lainnya memakai ruang kosong selebar avatar). */
  showAvatar?: boolean;
  /** Saran tindakan (CMP-019) yang tampil tepat di bawah balasan. */
  children?: ReactNode;
};

/**
 * CMP-007 (bagian pesan). Pesan pengguna rata kanan (ungu muda, teks `--text`), balasan rata kiri (kartu putih) dengan avatar RelaxMate.
 * Teks biasa: React meng-escape isi, `whitespace-pre-wrap` menjaga baris baru; tanpa HTML atau Markdown (Architecture 6.3).
 */
export default function ChatBubble({
  message,
  animate = false,
  typewriter = false,
  onTypingTick,
  onTypingDone,
  showAvatar = false,
  children,
}: ChatBubbleProps) {
  const mine = message.role === 'user';

  return (
    <li
      className={`flex flex-col gap-2 ${
        animate ? 'motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 motion-safe:duration-300' : ''
      }`}
    >
      <div className={`flex w-full items-start gap-2 ${mine ? 'justify-end' : ''}`}>
        {mine ? null : showAvatar ? <RelaxMateAvatar /> : <span aria-hidden="true" className="size-8 shrink-0" />}
        <div
          className={`min-w-0 max-w-[88%] rounded-2xl px-3.5 py-2.5 text-sm leading-relaxed md:max-w-[75%] md:text-[0.9375rem] ${
            mine
              ? 'rounded-br-md bg-soft-lavender text-text'
              : 'rounded-bl-md bg-card text-card-foreground shadow-card'
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
          {message.time ? <p className={`mt-0.5 text-[0.6875rem] ${mine ? 'text-text' : 'text-text-muted'}`}>{message.time}</p> : null}
        </div>
      </div>
      {children ? <div className="w-full max-w-[88%] pl-10 md:max-w-[75%]">{children}</div> : null}
    </li>
  );
}
