import type { FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import FormField from '@/Components/shared/FormField';
import SwitchField from '@/Components/shared/SwitchField';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { NativeSelect } from '@/Components/ui/native-select';
import { Separator } from '@/Components/ui/separator';
import type { NotificationPreferences } from '@/lib/notification';

type NotificationSettingsCardProps = {
  preferences: NotificationPreferences;
};

/** Kartu Notifikasi di Akun (SCR-018, FR-036): dua Switch dan pilihan jam WIB. Keduanya mati sampai dipilih. */
export default function NotificationSettingsCard({ preferences }: NotificationSettingsCardProps) {
  const form = useForm({
    mood_reminder_enabled: preferences.mood_reminder_enabled,
    mood_reminder_hour: preferences.mood_reminder_hour,
    nudges_enabled: preferences.nudges_enabled,
  });
  const errors = form.errors as Partial<Record<string, string>>;

  const hours = Array.from(
    { length: Math.max(0, preferences.hour_max - preferences.hour_min + 1) },
    (_, index) => preferences.hour_min + index,
  );

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    form.patch('/app/akun/notifikasi', {
      preserveScroll: true,
      onSuccess: () => form.setDefaults(),
    });
  };

  return (
    <Card data-load role="region" aria-labelledby="judul-notifikasi" className="gap-4 p-5">
      <CardHeader>
        <CardTitle id="judul-notifikasi" role="heading" aria-level={2} className="text-base">
          Notifikasi
        </CardTitle>
        <CardDescription className="text-[0.8125rem] leading-relaxed">
          Muncul di ikon lonceng saat kamu membuka RelaxBoss, tanpa email. Isinya umum dan tidak menyebut hasil atau
          catatanmu.
        </CardDescription>
      </CardHeader>
      <Separator />

      <form onSubmit={submit} noValidate className="flex flex-col gap-4">
        <CardContent className="space-y-4">
          <SwitchField
            id="notif-mood"
            label="Pengingat catat mood"
            hint="Muncul setelah jam pilihanmu bila hari ini belum ada catatan."
            checked={form.data.mood_reminder_enabled}
            onChange={(checked) => form.setData('mood_reminder_enabled', checked)}
            error={errors.mood_reminder_enabled}
          />
          <FormField id="notif-jam" label="Jam pengingat (WIB)" error={errors.mood_reminder_hour}>
            {(control) => (
              <NativeSelect
                {...control}
                variant="pill"
                name="mood_reminder_hour"
                disabled={!form.data.mood_reminder_enabled}
                value={String(form.data.mood_reminder_hour)}
                onChange={(event) => form.setData('mood_reminder_hour', Number(event.target.value))}
              >
                {hours.map((hour) => (
                  <option key={hour} value={hour}>
                    {String(hour).padStart(2, '0')}.00
                  </option>
                ))}
              </NativeSelect>
            )}
          </FormField>
          <SwitchField
            id="notif-ajakan"
            label="Ajakan lembut"
            hint="Sesekali mengajak mencoba Asesmen atau RelaxMate, dan memberi tahu ada artikel baru. Bisa kamu tutup kapan saja."
            checked={form.data.nudges_enabled}
            onChange={(checked) => form.setData('nudges_enabled', checked)}
            error={errors.nudges_enabled}
          />
        </CardContent>
        <CardFooter className="justify-end">
          <Button type="submit" className="rounded-full! px-5 text-sm" disabled={form.processing || !form.isDirty}>
            {form.processing ? 'Menyimpan...' : 'Simpan'}
          </Button>
        </CardFooter>
      </form>
    </Card>
  );
}
