import GuestLayout from '@/Layouts/GuestLayout';
import LogoutButton from '@/Components/shared/LogoutButton';

// SEMENTARA (TASK-005): pengganti Dashboard untuk menguji login dan logout. Hapus di TASK-014.
export default function DashboardSementara() {
  return (
    <GuestLayout title="Dashboard">
      <h1>Dashboard (sementara)</h1>
      <p className="mt-2 text-text-secondary">Kamu sudah masuk dan emailmu terverifikasi.</p>
      <div className="mt-6 flex justify-center">
        <LogoutButton />
      </div>
    </GuestLayout>
  );
}
