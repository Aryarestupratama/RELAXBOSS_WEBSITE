import { useState, type FormEvent } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { Search, Users } from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import ConfirmLeaveDialog from '@/Components/shared/ConfirmLeaveDialog';
import EmptyState from '@/Components/shared/EmptyState';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';

type UserRow = {
  id: number;
  email: string;
  is_admin: boolean;
  is_active: boolean;
  is_verified: boolean;
  registered_on: string;
  is_self: boolean;
};

type IndexProps = {
  users: UserRow[];
  search: string;
  prev_page_url: string | null;
  next_page_url: string | null;
  status: string | null;
};

const badgeBase = 'inline-flex items-center rounded-md px-2 py-1 text-sm font-medium';

const STATUS_TEXT: Record<string, string> = {
  'user-activated': 'Akun diaktifkan kembali.',
  'user-deactivated': 'Akun dinonaktifkan. Sesinya langsung diakhiri.',
};

/** Tanggal WIB (Y-m-d) dari server menjadi "5 Okt 2026". Hanya tanggal, tanpa jam. */
function formatDate(value: string): string {
  const [year, month, day] = value.split('-').map(Number);
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  return `${day} ${months[month - 1] ?? ''} ${year}`;
}

/** SCR-021: daftar Pengguna terbatas (email, tanggal daftar, verifikasi, aktif) dan nonaktifkan akun. */
export default function Index({ users, search, prev_page_url, next_page_url, status }: IndexProps) {
  const { errors } = usePage().props as { errors: Partial<Record<string, string>> };
  const [query, setQuery] = useState(search);
  const [target, setTarget] = useState<UserRow | null>(null);
  const [busyId, setBusyId] = useState<number | null>(null);

  const submitSearch = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    router.get('/admin/pengguna', query.trim() === '' ? {} : { q: query.trim() }, { preserveScroll: true });
  };

  const setActive = (user: UserRow, isActive: boolean) => {
    setBusyId(user.id);
    router.patch(
      `/admin/pengguna/${user.id}/status`,
      { is_active: isActive },
      {
        preserveScroll: true,
        onFinish: () => {
          setBusyId(null);
          setTarget(null);
        },
      },
    );
  };

  const statusText = status ? STATUS_TEXT[status] : undefined;

  return (
    <AdminShell title="Pengguna">
      <h1>Pengguna</h1>
      <p className="mt-2 max-w-prose text-text-secondary">
        Daftar terbatas: email, tanggal daftar, status verifikasi, dan status aktif. Data pribadi lain tidak
        ditampilkan. Akun yang dinonaktifkan tidak bisa masuk dan sesinya langsung diakhiri; datanya tidak dihapus.
      </p>

      <div className="mt-6 space-y-4">
        {statusText ? (
          <Alert>
            <AlertDescription>{statusText}</AlertDescription>
          </Alert>
        ) : null}
        {errors.user_status ? (
          <Alert variant="destructive">
            <AlertDescription>{errors.user_status}</AlertDescription>
          </Alert>
        ) : null}

        <form onSubmit={submitSearch} role="search" className="flex flex-wrap items-end gap-3">
          <div className="min-w-0 flex-1 basis-64">
            <Label htmlFor="cari-email">Cari email</Label>
            <Input
              id="cari-email"
              type="search"
              name="q"
              maxLength={100}
              autoComplete="off"
              value={query}
              onChange={(event) => setQuery(event.target.value)}
              className="mt-1.5"
            />
          </div>
          <Button type="submit" variant="outline">
            <Search aria-hidden="true" />
            Cari
          </Button>
          {search !== '' ? (
            <Button asChild variant="ghost">
              <Link href="/admin/pengguna">Hapus pencarian</Link>
            </Button>
          ) : null}
        </form>

        {users.length === 0 ? (
          <EmptyState
            icon={Users}
            message={search !== '' ? 'Tidak ada akun dengan email itu.' : 'Belum ada akun terdaftar.'}
          />
        ) : (
          <div className="overflow-x-auto rounded-xl border border-border bg-card">
            <table className="w-full min-w-[40rem] text-left">
              <caption className="sr-only">Daftar akun pengguna</caption>
              <thead className="border-b border-border text-sm text-text-secondary">
                <tr>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Email
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Terdaftar
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Verifikasi
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Status
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    <span className="sr-only">Aksi</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                {users.map((user) => (
                  <tr key={user.id} className="border-b border-border last:border-b-0">
                    <th scope="row" className="px-4 py-3 font-normal">
                      <span className="block break-all font-medium">{user.email}</span>
                      <span className="flex flex-wrap gap-1">
                        {user.is_admin ? (
                          <span className={`${badgeBase} bg-neutral-soft text-brand-strong`}>Admin</span>
                        ) : null}
                        {user.is_self ? (
                          <span className={`${badgeBase} bg-muted text-text-secondary`}>Akun kamu</span>
                        ) : null}
                      </span>
                    </th>
                    <td className="px-4 py-3">{formatDate(user.registered_on)}</td>
                    <td className="px-4 py-3">{user.is_verified ? 'Terverifikasi' : 'Belum'}</td>
                    <td className="px-4 py-3">
                      <span
                        className={`${badgeBase} ${
                          user.is_active ? 'bg-neutral-soft text-brand-strong' : 'bg-muted text-text-secondary'
                        }`}
                      >
                        {user.is_active ? 'Aktif' : 'Nonaktif'}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-right">
                      {user.is_self ? (
                        <span className="text-sm text-text-secondary">Tidak bisa diubah</span>
                      ) : user.is_active ? (
                        <Button
                          variant="destructive"
                          disabled={busyId !== null}
                          onClick={() => setTarget(user)}
                          aria-label={`Nonaktifkan ${user.email}`}
                        >
                          Nonaktifkan
                        </Button>
                      ) : (
                        <Button
                          variant="outline"
                          disabled={busyId !== null}
                          onClick={() => setActive(user, true)}
                          aria-label={`Aktifkan ${user.email}`}
                        >
                          {busyId === user.id ? 'Memproses...' : 'Aktifkan'}
                        </Button>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {prev_page_url || next_page_url ? (
          <nav aria-label="Halaman pengguna" className="flex justify-between gap-3">
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

      <ConfirmLeaveDialog
        open={target !== null}
        title="Nonaktifkan akun ini?"
        description={`${target?.email ?? ''} tidak akan bisa masuk dan sesi yang sedang aktif langsung diakhiri. Datanya tidak dihapus dan akun bisa diaktifkan kembali.`}
        stayLabel="Batal"
        leaveLabel={busyId !== null ? 'Memproses...' : 'Nonaktifkan'}
        onStay={() => {
          if (busyId === null) {
            setTarget(null);
          }
        }}
        onLeave={() => {
          if (target && busyId === null) {
            setActive(target, false);
          }
        }}
      />
    </AdminShell>
  );
}
