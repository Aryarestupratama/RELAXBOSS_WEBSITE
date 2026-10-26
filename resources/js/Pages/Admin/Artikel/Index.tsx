import { Link } from '@inertiajs/react';
import { CheckCircle, FileText, Plus } from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import EmptyState from '@/Components/shared/EmptyState';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { STATE_LABEL, STATUS_TEXT } from '@/lib/adminArticle';

type ArticleRow = {
  id: number;
  slug: string;
  title: string;
  category_label: string;
  state: 'draft' | 'scheduled' | 'live';
  is_demo: boolean;
  published_label: string | null;
  updated_label: string;
  public_url: string | null;
};

type IndexProps = {
  articles: ArticleRow[];
  prev_page_url: string | null;
  next_page_url: string | null;
  status: string | null;
};

const badgeBase = 'inline-flex items-center rounded-md px-2 py-1 text-sm font-medium';

/** SCR-026: daftar artikel. Konten publik; tidak ada data pengguna. */
export default function Index({ articles, prev_page_url, next_page_url, status }: IndexProps) {
  return (
    <AdminShell title="Artikel">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1>Artikel</h1>
          <p className="mt-2 max-w-prose text-text-secondary">
            Tulis dan terbitkan artikel edukasi untuk halaman Publik. Artikel tidak dihapus; kembalikan ke draf bila
            tidak ingin tampil. Sebelum terbit, pastikan tidak ada klaim diagnosis, kolaborasi UMB, atau validasi
            psikolog yang belum terkonfirmasi.
          </p>
        </div>
        <Button asChild>
          <Link href="/admin/artikel/baru">
            <Plus aria-hidden="true" />
            Artikel baru
          </Link>
        </Button>
      </div>

      {status && STATUS_TEXT[status] ? (
        <Alert className="mt-4">
          <CheckCircle className="text-success!" aria-hidden="true" />
          <AlertDescription>{STATUS_TEXT[status]}</AlertDescription>
        </Alert>
      ) : null}

      <div className="mt-6 space-y-4">
        {articles.length === 0 ? (
          <EmptyState
            icon={FileText}
            message="Belum ada artikel. Tulis yang pertama, ya."
            action={
              <Button asChild>
                <Link href="/admin/artikel/baru">Artikel baru</Link>
              </Button>
            }
          />
        ) : (
          <div className="overflow-x-auto rounded-xl bg-card shadow-card">
            <table className="w-full min-w-[44rem] text-left">
              <caption className="sr-only">Daftar artikel</caption>
              <thead className="bg-neutral-soft text-sm text-text-secondary">
                <tr>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Artikel
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Status
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Terbit (WIB)
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Diubah (WIB)
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    <span className="sr-only">Aksi</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                {articles.map((article) => (
                  <tr key={article.id} className="border-b border-border last:border-b-0">
                    <th scope="row" className="px-4 py-3 font-normal">
                      <span className="block font-medium">{article.title}</span>
                      <span className="block text-sm text-text-secondary">
                        {article.category_label} · {article.slug}
                      </span>
                    </th>
                    <td className="px-4 py-3">
                      <span className="flex flex-wrap gap-1">
                        <span
                          className={`${badgeBase} ${
                            article.state === 'live' ? 'bg-neutral-soft text-brand-strong' : 'bg-neutral-soft text-text-secondary'
                          }`}
                        >
                          {STATE_LABEL[article.state]}
                        </span>
                        {article.is_demo ? <span className={`${badgeBase} bg-neutral-soft text-text-secondary`}>Contoh</span> : null}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-text-secondary">{article.published_label ?? '-'}</td>
                    <td className="px-4 py-3 text-text-secondary">{article.updated_label}</td>
                    <td className="px-4 py-3 text-right">
                      <span className="flex flex-wrap justify-end gap-2">
                        {article.public_url ? (
                          <Button asChild variant="ghost">
                            <a
                              href={article.public_url}
                              target="_blank"
                              rel="noopener"
                              aria-label={`Lihat ${article.title} di halaman Publik (tab baru)`}
                            >
                              Lihat
                            </a>
                          </Button>
                        ) : null}
                        <Button asChild variant="outline">
                          <Link href={`/admin/artikel/${article.id}/ubah`} aria-label={`Ubah ${article.title}`}>
                            Ubah
                          </Link>
                        </Button>
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {prev_page_url || next_page_url ? (
          <nav aria-label="Halaman artikel" className="flex justify-between gap-3">
            {prev_page_url ? (
              <Button asChild variant="outline">
                <Link href={prev_page_url}>Sebelumnya</Link>
              </Button>
            ) : (
              <span />
            )}
            {next_page_url ? (
              <Button asChild variant="outline">
                <Link href={next_page_url}>Berikutnya</Link>
              </Button>
            ) : null}
          </nav>
        ) : null}
      </div>
    </AdminShell>
  );
}
