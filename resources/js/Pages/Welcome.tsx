import { CircleAlert, Info } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import { Button } from '@/Components/ui/button';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';

// SEMENTARA (TASK-002): untuk memeriksa AppShell dan token. Dihapus di TASK-007.
export default function Welcome() {
  return (
    <AppShell title="Uji Tampilan">
      <h1>Uji tampilan AppShell</h1>
      <h2 className="mt-6">Judul H2</h2>
      <h3 className="mt-4">Judul H3</h3>
      <p className="mt-4 max-w-prose text-text-secondary">
        Teks pendukung memakai <code>text-text-secondary</code>. Teks keterangan memakai{' '}
        <span className="text-text-muted">text-text-muted</span>.
      </p>

      <div className="mt-6 flex flex-wrap gap-3">
        <Button>Tombol utama</Button>
        <Button variant="outline">Tombol sekunder</Button>
        <Button variant="ghost">Ghost</Button>
        <Button variant="destructive">Destruktif</Button>
        <Button size="icon" aria-label="Info"><Info /></Button>
        <Button disabled>Memproses...</Button>
      </div>

      <div className="mt-6 grid max-w-xl gap-3">
        <Alert>
          <Info />
          <AlertTitle>Informasi</AlertTitle>
          <AlertDescription>Ini Alert status (role status).</AlertDescription>
        </Alert>
        <Alert variant="destructive">
          <CircleAlert />
          <AlertTitle>Ada yang tidak berjalan semestinya.</AlertTitle>
          <AlertDescription>Coba lagi, ya. Ini Alert galat (role alert).</AlertDescription>
        </Alert>
      </div>

      <div className="mt-8 flex flex-wrap gap-3 text-text">
        <span className="rounded-full bg-mood-blue px-4 py-2 text-sm">Sedih</span>
        <span className="rounded-full bg-mood-green px-4 py-2 text-sm">Lega</span>
        <span className="rounded-full bg-mood-red px-4 py-2 text-sm">Stres</span>
        <span className="rounded-full bg-mood-yellow px-4 py-2 text-sm">Cemas</span>
        <span className="rounded-full bg-brand px-4 py-2 text-sm font-semibold text-on-primary">Tenang</span>
      </div>
    </AppShell>
  );
}
