import { router } from '@inertiajs/react';
import { LogOut } from 'lucide-react';
import { Button } from '@/Components/ui/button';

export default function LogoutButton() {
  return (
    <Button variant="ghost" onClick={() => router.post('/keluar')}>
      <LogOut aria-hidden="true" />
      Keluar
    </Button>
  );
}
