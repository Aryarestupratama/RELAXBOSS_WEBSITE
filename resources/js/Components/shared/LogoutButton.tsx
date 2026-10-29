import { router } from '@inertiajs/react';
import { LogOut } from 'lucide-react';
import { Button } from '@/Components/ui/button';

type LogoutButtonProps = {
  className?: string;
};

export default function LogoutButton({ className }: LogoutButtonProps) {
  return (
    <Button variant="ghost" className={className} onClick={() => router.post('/keluar')}>
      <LogOut aria-hidden="true" />
      Keluar
    </Button>
  );
}
