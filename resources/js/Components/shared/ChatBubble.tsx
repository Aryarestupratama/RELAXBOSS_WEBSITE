import type { ReactNode } from 'react';
import type { ChatMessage } from '@/lib/relaxmate';

type ChatBubbleProps = {
  message: Pick<ChatMessage, 'role' | 'content' | 'time' | 'pending'>;
  /** Saran tindakan (CMP-019) yang tampil tepat di bawah balasan. */
  children?: ReactNode;
};

/**
 * CMP-007 (bagian pesan). Pesan pengguna rata kanan, balasan rata kiri. Teks biasa: React meng-escape isi,
 * `whitespace-pre-wrap` menjaga baris baru; tanpa HTML atau Markdown (Architecture 6.3).
 */
export default function ChatBubble({ message, children }: ChatBubbleProps) {
  const mine = message.role === 'user';

  return (
    <li className={`flex flex-col gap-2 ${mine ? 'items-end' : 'items-start'}`}>
      <div
        className={`max-w-[85%] rounded-2xl px-4 py-3 ${
          mine ? 'rounded-br-md bg-primary text-primary-foreground' : 'rounded-bl-md border border-border bg-card text-card-foreground'
        } ${message.pending ? 'opacity-70' : ''}`}
      >
        <span className="sr-only">{mine ? 'Kamu' : 'RelaxMate'}: </span>
        <p className="whitespace-pre-wrap break-words">{message.content}</p>
        {message.time ? (
          <p className={`mt-1 text-xs ${mine ? 'text-primary-foreground' : 'text-text-muted'}`}>{message.time}</p>
        ) : null}
      </div>
      {children ? <div className="w-full max-w-[85%]">{children}</div> : null}
    </li>
  );
}
