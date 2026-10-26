import { CalendarDays, Lock, MessagesSquare, ShieldAlert, Sparkles, Tag, TriangleAlert } from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import MonitoringHeader from '@/Components/shared/MonitoringHeader';
import MonitoringItem from '@/Components/shared/MonitoringItem';
import MonitoringMeta from '@/Components/shared/MonitoringMeta';
import { Badge } from '@/Components/ui/badge';
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

/** SCR-022, detail Percakapan: isi tanpa identitas, tanpa jam. Membuka halaman ini sudah tercatat di server. */
export default function ChatShow({ conversation, messages }: ChatShowProps) {
  return (
    <AdminShell title={`Percakapan ${conversation.pseudo_id}`} staggered>
      <MonitoringHeader current="chat" back={{ href: '/admin/monitoring/chat', label: 'Kembali ke daftar' }} />

      <div className="flex flex-col gap-6">
        <MonitoringMeta
          title={
            <>
              Percakapan <span className="font-mono">{conversation.pseudo_id}</span>
            </>
          }
          note="Pembukaan ini sudah dicatat."
          items={[
            { icon: CalendarDays, label: 'Tanggal', value: formatDate(conversation.date) },
            { icon: MessagesSquare, label: 'Giliran', value: conversation.total_turns },
            { icon: Tag, label: 'Intent awal', value: conversation.initial_intent ?? '-' },
            { icon: ShieldAlert, label: 'Krisis', value: conversation.has_crisis ? 'Ya' : 'Tidak' },
          ]}
        />

        <section aria-labelledby="isi-percakapan" data-load className="flex flex-col gap-3">
          <div className="flex items-center justify-between gap-3">
            <h2 id="isi-percakapan" className="text-lg leading-snug font-bold tracking-tight">
              Isi Percakapan
            </h2>
            <Badge variant="outline">
              <Lock aria-hidden="true" />
              Hanya baca
            </Badge>
          </div>

          <ol className="flex flex-col gap-3">
            {messages.map((message) => (
              <MonitoringItem
                key={message.id}
                title={message.role === 'user' ? 'Pengguna' : 'RelaxMate'}
                kind={message.role === 'user' ? 'user' : 'assistant'}
                crisis={message.is_crisis}
                badges={
                  <>
                    {message.is_crisis ? (
                      <Badge variant="crisis">
                        <TriangleAlert aria-hidden="true" />
                        Krisis
                      </Badge>
                    ) : null}
                    {message.intent ? <Badge variant="outline">{message.intent}</Badge> : null}
                    {message.confidence !== null ? (
                      <Badge variant={message.low_confidence ? 'peach' : 'secondary'}>
                        Keyakinan {message.confidence}%{message.low_confidence ? ' (rendah)' : ''}
                      </Badge>
                    ) : null}
                    {message.suggestion ? (
                      <Badge variant="mint">
                        <Sparkles aria-hidden="true" />
                        {message.suggestion}
                      </Badge>
                    ) : null}
                  </>
                }
              >
                {message.content}
              </MonitoringItem>
            ))}
          </ol>
        </section>
      </div>
    </AdminShell>
  );
}
