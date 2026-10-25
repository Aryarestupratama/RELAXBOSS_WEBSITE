import { useState, type ComponentProps } from 'react';
import { Eye, EyeOff } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { AUTH_INPUT } from '@/lib/authStyles';

type PasswordInputProps = Omit<ComponentProps<'input'>, 'type'> & {
  /** Ukuran ringkas untuk halaman tamu (tinggi 40px). */
  compact?: boolean;
};

/** CMP-017: input sandi dengan tombol tampil/sembunyikan. */
export default function PasswordInput({ className = '', compact = false, ...props }: PasswordInputProps) {
  const [visible, setVisible] = useState(false);

  return (
    <div className="relative">
      <Input
        {...props}
        type={visible ? 'text' : 'password'}
        className={`${compact ? `${AUTH_INPUT} pr-11` : 'pr-14'} ${className}`}
      />
      <Button
        type="button"
        variant="ghost"
        size="icon"
        className={compact ? 'absolute top-0 right-0 size-10 rounded-lg text-text-secondary hover:bg-transparent hover:text-nav-hover' : 'absolute top-0 right-0'}
        aria-label={visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'}
        aria-pressed={visible}
        onClick={() => setVisible((current) => !current)}
      >
        {visible ? <EyeOff aria-hidden="true" /> : <Eye aria-hidden="true" />}
      </Button>
    </div>
  );
}
