import GuestLayout from '@/Layouts/GuestLayout';

// SEMENTARA (TASK-002): untuk memeriksa GuestLayout. Dihapus di TASK-004.
export default function UjiTamu() {
  return (
    <GuestLayout title="Uji Tamu">
      <h1>Buat akunmu</h1>
      <p className="mt-2 text-text-secondary">
        Mulai dari satu langkah kecil. Setelah mendaftar, kami kirim tautan verifikasi ke emailmu.
      </p>
    </GuestLayout>
  );
}
