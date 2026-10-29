import { useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import {
  CircleAlert,
  CircleCheck,
  CircleOff,
  LoaderCircle,
  MailCheck,
  MailQuestion,
  ShieldCheck,
  UserCheck,
  UserPlus,
  UserX,
  Users,
} from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import CreateAdminDialog from '@/Components/shared/CreateAdminDialog';
import ConfirmLeaveDialog from '@/Components/shared/ConfirmLeaveDialog';
import DataTable, { type DataTableColumn } from '@/Components/shared/DataTable';
import DataTableCard from '@/Components/shared/DataTableCard';
import InlineNote from '@/Components/shared/InlineNote';
import PageHeader from '@/Components/shared/PageHeader';
import TableSearch from '@/Components/shared/TableSearch';
import TableToolbar from '@/Components/shared/TableToolbar';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { ROW_BUTTON } from '@/lib/adminTable';

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
        className="grid size-10 shrink-0 place-items-center rounded-full bg-soft-lavender text-sm font-bold text-text uppercase md:size-9"
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

  const submitSearch = () => {
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
          className={ROW_BUTTON}
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
        className={ROW_BUTTON}
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

  const columns: DataTableColumn<UserRow>[] = [
    { id: 'email', header: 'Email', rowHeader: true, cell: (user) => <EmailCell user={user} /> },
    {
      id: 'registered',
      header: 'Terdaftar',
      className: 'whitespace-nowrap text-text-secondary',
      cell: (user) => formatDate(user.registered_on),
    },
    { id: 'verified', header: 'Verifikasi', cell: (user) => <VerifiedBadge user={user} /> },
    { id: 'status', header: 'Status', cell: (user) => <ActiveBadge user={user} /> },
    { id: 'action', header: 'Aksi', hideHeader: true, align: 'right', cell: renderAction },
  ];

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

        <DataTableCard
          id="daftar-akun"
          title="Daftar akun"
          countLabel={users.length > 0 ? `${users.length} di halaman ini` : undefined}
          toolbar={
            <TableToolbar>
              <TableSearch
                id="cari-email"
                label="Cari email"
                value={query}
                onChange={setQuery}
                onSubmit={submitSearch}
                clearHref={search !== '' ? '/admin/pengguna' : undefined}
              />
            </TableToolbar>
          }
          isEmpty={users.length === 0}
          empty={{ icon: Users, message: search !== '' ? 'Tidak ada akun dengan email itu.' : 'Belum ada akun terdaftar.' }}
          pager={{ label: 'Halaman pengguna', prevUrl: prev_page_url, nextUrl: next_page_url }}
        >
          <DataTable
            caption="Daftar akun pengguna"
            columns={columns}
            rows={users}
            getRowKey={(user) => user.id}
            mobileCard={(user) => (
              <>
                <EmailCell user={user} />
                <span className="flex flex-wrap gap-1.5">
                  <VerifiedBadge user={user} />
                  <ActiveBadge user={user} />
                </span>
                <p className="text-sm text-text-secondary">Terdaftar: {formatDate(user.registered_on)}</p>
                <div>{renderAction(user)}</div>
              </>
            )}
          />
        </DataTableCard>
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
