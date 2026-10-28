import { Link, usePage } from '@inertiajs/react';
import { Bell } from 'lucide-react';
import type { SharedNotificationProps } from '@/lib/notification';
import { cn } from '@/lib/utils';

type NotificationBellProps = {
  /** `rail`: item rail AppShell (>= 768px) dengan label. `bar`: tombol ikon di bar atas HP. */
  variant: 'rail' | 'bar';
  active: boolean;
  className?: string;
  iconClassName?: string;
};

/**
 * CMP-045. Lonceng menuju halaman Notifikasi (SCR-027). Penanda belum dibaca berupa angka (bukan warna saja, RULE-052),
 * dan `aria-label` memuat jumlahnya. Angka adalah hitungan dari prop bersama `notifications`, tanpa isi notifikasi.
 */
export default function NotificationBell({ variant, active, className, iconClassName }: NotificationBellProps) {
  const unread = usePage<SharedNotificationProps>().props.notifications?.unread ?? 0;
  const label = unread > 0 ? `Notifikasi, ${unread} belum dibaca` : 'Notifikasi';

  const badge =
    unread > 0 ? (
      <span
        aria-hidden="true"
        className="absolute -top-1.5 -right-2.5 grid min-w-4.5 place-items-center rounded-full bg-primary px-1 text-[0.6875rem] leading-[1.125rem] font-semibold text-on-primary ring-2 ring-card"
      >
        {unread > 9 ? '9+' : unread}
      </span>
    ) : null;

  const icon = (
    <span className="relative inline-flex">
      <Bell className={iconClassName ?? 'size-5'} aria-hidden="true" />
      {badge}
    </span>
  );

  if (variant === 'bar') {
    return (
      <Link
        href="/app/notifikasi"
        aria-label={label}
        aria-current={active ? 'page' : undefined}
        className={cn(
          'inline-flex size-11 items-center justify-center rounded-lg text-primary transition-colors hover:text-nav-hover',
          className,
        )}
      >
        {icon}
      </Link>
    );
  }

  return (
    <Link
      href="/app/notifikasi"
      data-press
      aria-label={label}
      aria-current={active ? 'page' : undefined}
      className={className}
    >
      {icon}
      Notifikasi
    </Link>
  );
}
