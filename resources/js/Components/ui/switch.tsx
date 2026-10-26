import type { ComponentProps } from 'react';
import { Switch as SwitchPrimitive } from 'radix-ui';
import { cn } from '@/lib/utils';

/**
 * Switch shadcn (Radix). Tidak menyuntik <style>, jadi aman untuk CSP bernonce. Warna hanya dari token
 * (RULE-050): jalur mati `--input-border` (3:1 di atas `--card`), hidup `--primary`, ibu jari `--card`.
 * Area sentuh diperluas lewat `after:` sampai 44px tanpa membesarkan tampilan. Wajib punya label
 * (pakai `SwitchField`).
 */
function Switch({ className, ...props }: ComponentProps<typeof SwitchPrimitive.Root>) {
  return (
    <SwitchPrimitive.Root
      data-slot="switch"
      className={cn(
        "peer relative inline-flex h-7 w-12 shrink-0 cursor-pointer items-center rounded-full bg-input-border transition-colors duration-200 outline-none after:absolute after:-inset-2 after:content-[''] data-[state=checked]:bg-primary disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:ring-2 aria-invalid:ring-destructive",
        className,
      )}
      {...props}
    >
      <SwitchPrimitive.Thumb
        data-slot="switch-thumb"
        className="pointer-events-none block size-6 translate-x-0.5 rounded-full bg-card shadow-card transition-transform duration-200 data-[state=checked]:translate-x-[1.375rem] motion-reduce:transition-none"
      />
    </SwitchPrimitive.Root>
  );
}

export { Switch };
