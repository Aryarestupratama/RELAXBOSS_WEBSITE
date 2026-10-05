import { usePage } from '@inertiajs/react';
import { LifeBuoy, Phone } from 'lucide-react';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import {
  CRISIS_DUMMY_LABEL,
  CRISIS_NO_CONTACTS,
  CRISIS_RESPONSE_TEXT,
  isSafeUrl,
  telHref,
  type CrisisContact,
} from '@/lib/crisis';

type SharedProps = {
  crisisContacts?: CrisisContact[];
};

/**
 * CMP-008. Banner bantuan: teks Crisis Response dan kontak dari prop bersama `crisisContacts`
 * (config `crisis_contacts`, tidak pernah ditulis di sini). Dipakai di RelaxMate (SCR-017) dan
 * hasil Asesmen (SCR-014). Tidak bisa ditutup.
 */
export default function CrisisBanner() {
  const { crisisContacts = [] } = usePage<SharedProps>().props;

  return (
    <Alert role="alert" className="border-crisis-text/30 bg-crisis-bg text-crisis-text">
      <LifeBuoy aria-hidden="true" />
      <AlertTitle>Butuh bantuan sekarang?</AlertTitle>
      <AlertDescription className="text-crisis-text">
        <p>{CRISIS_RESPONSE_TEXT}</p>

        {crisisContacts.length === 0 ? (
          <p>{CRISIS_NO_CONTACTS}</p>
        ) : (
          <ul className="space-y-3">
            {crisisContacts.map((contact) => (
              <li key={`${contact.name}-${contact.phone ?? ''}-${contact.url ?? ''}`}>
                <p className="flex items-center gap-2 font-medium">
                  <Phone className="size-4" aria-hidden="true" />
                  {contact.name}
                </p>
                {contact.phone ? (
                  <p>
                    <a href={telHref(contact.phone)} className="inline-flex min-h-11 items-center font-medium">
                      {contact.phone}
                    </a>
                  </p>
                ) : null}
                {isSafeUrl(contact.url) ? (
                  <p>
                    <a
                      href={contact.url}
                      rel="noopener noreferrer"
                      className="inline-flex min-h-11 items-center font-medium"
                    >
                      {contact.url}
                    </a>
                  </p>
                ) : null}
                {contact.note && !contact.dummy ? <p>{contact.note}</p> : null}
                {contact.dummy ? <p>{CRISIS_DUMMY_LABEL}</p> : null}
              </li>
            ))}
          </ul>
        )}

        {/* <a> biasa: Konsultasi adalah halaman Blade */}
        <p>
          <a href="/konsultasi" className="inline-flex min-h-11 items-center font-medium">
            Lihat halaman Konsultasi Profesional
          </a>
        </p>
      </AlertDescription>
    </Alert>
  );
}
