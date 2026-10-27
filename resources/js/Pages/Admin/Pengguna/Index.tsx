import { useState, type FormEvent } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import {
  ChevronLeft,
  ChevronRight,
  CircleAlert,
  CircleCheck,
  CircleOff,
  LoaderCircle,
  MailCheck,
  MailQuestion,
  Search,
  ShieldCheck,
  UserCheck,
  UserPlus,
  UserX,
  Users,
  X,
} from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import CreateAdminDialog from '@/Components/shared/CreateAdminDialog';
import ConfirmLeaveDialog from '@/Components/shared/ConfirmLeaveDialog';
import InlineNote from '@/Components/shared/InlineNote';
import PageHeader from '@/Components/shared/PageHeader';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';

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
};

const ROW_HEAD = 'h-auto px-3 py-3 text-sm font-normal tracking-normal whitespace-normal text-text';
const PILL_BUTTON = 'rounded-full! px-5 text-sm';

/** Tanggal WIB (Y-m-d) dari server menjadi "5 Okt 2026". Hanya tanggal, tanpa jam. */
function formatDate(value: string): string {
  const [year, month, day] = value.split('-').map(Number);
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  return `${day} ${months[month - 1] ?? ''} ${year}`;
}

function EmailCell({ user }: { user: UserRow }) {
  return (
    <span className="flex items-center gap-3">
      <span
        className="grid size-10 shrink-0 place-items-center rounded-full bg-soft-lavender text-sm font-bold text-text uppercase"
        aria-hidden="true"
      >
        {user.email.charAt(0)}
      </span>
      <span className="min-w-0">
        <span className="block font-semibold break-all">{user.email}</span>
        {user.is_admin || user.is_self ? (
          <span className="mt-1 flex flex-wrap gap-1.5">
            {user.is_admin ? (
              <Badge variant="lavender">
                <ShieldCheck aria-hidden="true" />
                Admin
              </Badge>
            ) : null}
            {user.is_self ? <Badge variant="outline">Akun kamu</Badge> : null}
          </span>
        ) : null}
      </span>
    </span>
  );
}

function VerifiedBadge({ user }: { user: UserRow }) {
  return user.is_verified ? (
    <Badge variant="sky">
      <MailCheck aria-hidden="true" />
      Terverifikasi
    </Badge>
  ) : (
    <Badge variant="peach">
      <MailQuestion aria-hidden="true" />
      Belum
    </Badge>
  );
}

function ActiveBadge({ user }: { user: UserRow }) {
  return user.is_active ? (
    <Badge variant="mint">
      <CircleCheck aria-hidden="true" />
      Aktif
    </Badge>
  ) : (
    <Badge variant="secondary">
      <CircleOff aria-hidden="true" />
      Nonaktif
    </Badge>
  );
}

/** SCR-021: daftar Pengguna terbatas (email, tanggal daftar, verifikasi, aktif) dan nonaktifkan akun. */
export default function Index({ users, search, prev_page_url, next_page_url }: IndexProps) {
  const { errors } = usePage().props as { errors: Partial<Record<string, string>> };
  const [query, setQuery] = useState(search);
  const [target, setTarget] = useState<UserRow | null>(null);
  const [busyId, setBusyId] = useState<number | null>(null);
  const [createOpen, setCreateOpen] = useState(false);

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

  const renderAction = (user: UserRow) => {
    if (user.is_self) {
      return <span className="text-sm text-text-secondary">Tidak bisa diubah</span>;
    }

    if (user.is_active) {
      return (
        <Button
          variant="destructive"
          className={PILL_BUTTON}
          disabled={busyId !== null}
          onClick={() => setTarget(user)}
          aria-label={`Nonaktifkan ${user.email}`}
        >
          <UserX className="size-4!" aria-hidden="true" />
          Nonaktifkan
        </Button>
      );
    }

    return (
      <Button
        variant="outline"
        className={PILL_BUTTON}
        disabled={busyId !== null}
        onClick={() => setActive(user, true)}
        aria-label={`Aktifkan ${user.email}`}
      >
        {busyId === user.id ? (
          <LoaderCircle className="size-4! animate-spin motion-reduce:animate-none" aria-hidden="true" />
        ) : (
          <UserCheck className="size-4!" aria-hidden="true" />
        )}
        {busyId === user.id ? 'Memproses...' : 'Aktifkan'}
      </Button>
    );
  };

  return (
    <AdminShell title="Pengguna" staggered>
      <PageHeader
        title="Pengguna"
        description="Daftar terbatas: email, tanggal daftar, status verifikasi, dan status aktif."
        actions={
          <Button type="button" variant="outline" className={PILL_BUTTON} onClick={() => setCreateOpen(true)}>
            <UserPlus aria-hidden="true" />
            Tambah admin
          </Button>
        }
      />

      <div className="flex flex-col gap-6">
        {errors.user_status ? (
          <div data-load>
            <Alert
              variant="destructive"
              className="rounded-2xl border-transparent border-l-4 border-l-destructive px-4 py-3 shadow-card"
            >
              <CircleAlert aria-hidden="true" />
              <AlertDescription>{errors.user_status}</AlertDescription>
            </Alert>
          </div>
        ) : null}

        <div data-load>
          <InlineNote>Hanya data terbatas yang tampil, dan akun nonaktif tidak bisa masuk tetapi datanya tidak dihapus.</InlineNote>
        </div>

        <Card data-load className="p-4 md:p-5">
          <form onSubmit={submitSearch} role="search" className="flex flex-wrap items-end gap-3">
            <div className="min-w-0 flex-1 basis-64">
              <Label htmlFor="cari-email">Cari email</Label>
              <div className="relative mt-1.5">
                <Search
                  className="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-text-secondary"
                  aria-hidden="true"
                />
                <Input
                  id="cari-email"
                  type="search"
                  name="q"
                  maxLength={100}
                  autoComplete="off"
                  value={query}
                  onChange={(event) => setQuery(event.target.value)}
                  className="rounded-full pl-10"
                />
              </div>
            </div>
            <Button type="submit" className={PILL_BUTTON}>
              <Search aria-hidden="true" />
              Cari
            </Button>
            {search !== '' ? (
              <Button asChild variant="ghost" className={PILL_BUTTON}>
                <Link href="/admin/pengguna">
                  <X aria-hidden="true" />
                  Hapus pencarian
                </Link>
              </Button>
            ) : null}
          </form>
        </Card>

        {users.length === 0 ? (
          <Card tone="lavender" data-load className="items-center gap-3 px-6 py-10 text-center">
            <span className="grid size-12 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true">
              <Users className="size-6" />
            </span>
            <p className="max-w-sm text-sm text-text">
              {search !== '' ? 'Tidak ada akun dengan email itu.' : 'Belum ada akun terdaftar.'}
            </p>
          </Card>
        ) : (
          <section aria-labelledby="daftar-akun" data-load className="flex flex-col gap-3">
            <div className="flex items-center justify-between gap-3">
              <h2 id="daftar-akun" className="text-lg leading-snug font-bold tracking-tight">
                Daftar akun
              </h2>
              <Badge variant="outline">{users.length} di halaman ini</Badge>
            </div>

            {/* >= 768px: tabel */}
            <Card className="hidden gap-0 p-3 md:flex">
              <Table className="min-w-[40rem]">
                <TableCaption className="sr-only">Daftar akun pengguna</TableCaption>
                <TableHeader>
                  <TableRow className="border-0 bg-soft-lavender/40 hover:bg-soft-lavender/40 [&>th:first-child]:rounded-l-xl [&>th:last-child]:rounded-r-xl">
                    <TableHead>Email</TableHead>
                    <TableHead>Terdaftar</TableHead>
                    <TableHead>Verifikasi</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>
                      <span className="sr-only">Aksi</span>
                    </TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {users.map((user) => (
                    <TableRow key={user.id}>
                      <TableHead scope="row" className={ROW_HEAD}>
                        <EmailCell user={user} />
                      </TableHead>
                      <TableCell className="whitespace-nowrap text-text-secondary">
                        {formatDate(user.registered_on)}
                      </TableCell>
                      <TableCell>
                        <VerifiedBadge user={user} />
                      </TableCell>
                      <TableCell>
                        <ActiveBadge user={user} />
                      </TableCell>
                      <TableCell className="text-right">{renderAction(user)}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </Card>

            {/* < 768px: kartu */}
            <ul className="grid gap-3 md:hidden">
              {users.map((user) => (
                <li key={user.id}>
                  <Card className="gap-3 p-4">
                    <EmailCell user={user} />
                    <span className="flex flex-wrap gap-1.5">
                      <VerifiedBadge user={user} />
                      <ActiveBadge user={user} />
                    </span>
                    <p className="text-sm text-text-secondary">Terdaftar: {formatDate(user.registered_on)}</p>
                    <div>{renderAction(user)}</div>
                  </Card>
                </li>
              ))}
            </ul>
          </section>
        )}

        {prev_page_url || next_page_url ? (
          <nav aria-label="Halaman pengguna" data-load className="flex justify-between gap-3">
            {prev_page_url ? (
              <Button asChild variant="outline" className={PILL_BUTTON}>
                <Link href={prev_page_url}>
                  <ChevronLeft aria-hidden="true" />
                  Sebelumnya
                </Link>
              </Button>
            ) : (
              <span />
            )}
            {next_page_url ? (
              <Button asChild variant="outline" className={PILL_BUTTON}>
                <Link href={next_page_url}>
                  Berikutnya
                  <ChevronRight aria-hidden="true" />
                </Link>
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
      <CreateAdminDialog open={createOpen} onClose={() => setCreateOpen(false)} />
    </AdminShell>
  );
}
