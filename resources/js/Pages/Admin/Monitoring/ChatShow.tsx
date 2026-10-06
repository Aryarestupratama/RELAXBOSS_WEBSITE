import { Link } from '@inertiajs/react';
import AdminShell from '@/Layouts/AdminShell';
import MonitoringHeader from '@/Components/shared/MonitoringHeader';
import MonitoringItem from '@/Components/shared/MonitoringItem';
import { Button } from '@/Components/ui/button';
import { formatDate, type MonitoringMessage } from '@/lib/adminMonitoring';

type ChatShowProps = {
  conversation: {
    pseudo_id: string;
    date: string;
    total_turns: number;
    has_crisis: boolean;
    initial_intent: string | null;
  };
  messages: MonitoringMessage[];
};

const badgeBase = 'inline-flex items-center rounded-md px-2 py-1 text-sm font-medium';

/** SCR-022, detail Percakapan: isi tanpa identitas, tanpa jam. Membuka halaman ini sudah tercatat di server. */
export default function ChatShow({ conversation, messages }: ChatShowProps) {
  return (
    <AdminShell title={`Percakapan ${conversation.pseudo_id}`}>
      <MonitoringHeader current="chat" />

      <div className="mt-6 space-y-4">
        <Button asChild variant="ghost">
          <Link href="/admin/monitoring/chat">Kembali ke daftar</Link>
        </Button>

        <h2>
          Percakapan <span className="font-mono">{conversation.pseudo_id}</span>
        </h2>
        <dl className="grid gap-x-6 gap-y-1 text-text-secondary sm:grid-cols-2">
          <div className="flex gap-2">
            <dt className="font-medium">Tanggal:</dt>
            <dd>{formatDate(conversation.date)}</dd>
          </div>
          <div className="flex gap-2">
            <dt className="font-medium">Giliran:</dt>
            <dd>{conversation.total_turns}</dd>
          </div>
          <div className="flex gap-2">
            <dt className="font-medium">Intent awal:</dt>
            <dd>{conversation.initial_intent ?? '-'}</dd>
          </div>
          <div className="flex gap-2">
            <dt className="font-medium">Krisis:</dt>
            <dd>{conversation.has_crisis ? 'Ya' : 'Tidak'}</dd>
          </div>
        </dl>
        <p className="text-sm text-text-secondary">Pembukaan ini sudah dicatat.</p>

        <ol className="space-y-3">
          {messages.map((message) => (
            <MonitoringItem
              key={message.id}
              title={message.role === 'user' ? 'Pengguna' : 'RelaxMate'}
              badges={
                <>
                  {message.is_crisis ? (
                    <span className={`${badgeBase} bg-neutral-soft text-brand-strong`}>Krisis</span>
                  ) : null}
                  {message.intent ? (
                    <span className={`${badgeBase} bg-muted text-text-secondary`}>{message.intent}</span>
                  ) : null}
                  {message.confidence !== null ? (
                    <span className={`${badgeBase} bg-muted text-text-secondary`}>
                      Keyakinan {message.confidence}%{message.low_confidence ? ' (rendah)' : ''}
                    </span>
                  ) : null}
                  {message.suggestion ? (
                    <span className={`${badgeBase} bg-muted text-text-secondary`}>{message.suggestion}</span>
                  ) : null}
                </>
              }
            >
              {message.content}
            </MonitoringItem>
          ))}
        </ol>
      </div>
    </AdminShell>
  );
}
