/** Hasil panggilan JSON ke server (API-015). Format galat server: `{ error: { code, message } }`. */
export type ApiResult<T> =
  | { ok: true; data: T }
  | { ok: false; status: number; code: string; message: string };

const GENERIC_ERROR = 'Ada yang tidak berjalan semestinya. Coba lagi, ya.';
const EXPIRED_ERROR = 'Halaman ini sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.';

function xsrfToken(): string {
  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : '';
}

/**
 * POST JSON dengan token CSRF dari cookie. `timeoutMs` melebihi batas server (rotasi key dan model
 * cadangan) agar klien tidak menyerah lebih dulu.
 */
export async function postJson<T>(url: string, body: Record<string, unknown>, timeoutMs = 70000): Promise<ApiResult<T>> {
  const controller = new AbortController();
  const timer = window.setTimeout(() => controller.abort(), timeoutMs);

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': xsrfToken(),
      },
      credentials: 'same-origin',
      body: JSON.stringify(body),
      signal: controller.signal,
    });

    let json: unknown = null;
    try {
      json = await response.json();
    } catch {
      json = null;
    }

    if (response.ok) {
      const data = (json as { data?: T } | null)?.data;
      return data === undefined
        ? { ok: false, status: response.status, code: 'invalid_response', message: GENERIC_ERROR }
        : { ok: true, data };
    }

    const error = (json as { error?: { code?: string; message?: string } } | null)?.error;
    const expired = response.status === 419 || response.status === 401;

    return {
      ok: false,
      status: response.status,
      code: error?.code ?? (expired ? 'expired' : 'error'),
      message: error?.message ?? (expired ? EXPIRED_ERROR : GENERIC_ERROR),
    };
  } catch {
    return { ok: false, status: 0, code: 'network', message: GENERIC_ERROR };
  } finally {
    window.clearTimeout(timer);
  }
}

/** Hasil unduhan berkas lewat POST (TASK-057). `fieldErrors` berisi galat validasi per kolom bila server menjawab 422. */
export type FileResult =
  | { ok: true; blob: Blob; filename: string; headers: Headers }
  | { ok: false; status: number; code: string; message: string; fieldErrors: Record<string, string> };

const FILENAME_PATTERN = /filename\*?=(?:UTF-8'')?"?([^";]+)"?/i;

/**
 * POST JSON yang dijawab berkas (mis. Excel). Galat server (`{ error }` atau galat validasi `{ message, errors }`)
 * dikembalikan sebagai teks yang aman ditampilkan. Berkas diterima sebagai `Blob`; pemanggil yang menyimpannya.
 */
export async function postForFile(url: string, body: Record<string, unknown>, timeoutMs = 190000): Promise<FileResult> {
  const controller = new AbortController();
  const timer = window.setTimeout(() => controller.abort(), timeoutMs);

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        Accept: 'application/json, application/octet-stream',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': xsrfToken(),
      },
      credentials: 'same-origin',
      body: JSON.stringify(body),
      signal: controller.signal,
    });

    const type = response.headers.get('Content-Type') ?? '';

    if (response.ok && !type.includes('application/json')) {
      const disposition = response.headers.get('Content-Disposition') ?? '';
      const filename = FILENAME_PATTERN.exec(disposition)?.[1] ?? 'relaxboss-ekspor.xlsx';

      return { ok: true, blob: await response.blob(), filename, headers: response.headers };
    }

    let json: unknown = null;
    try {
      json = await response.json();
    } catch {
      json = null;
    }

    const parsed = json as {
      message?: string;
      error?: { code?: string; message?: string };
      errors?: Record<string, string[]>;
    } | null;
    const expired = response.status === 419 || response.status === 401;
    const fieldErrors: Record<string, string> = {};
    for (const [field, messages] of Object.entries(parsed?.errors ?? {})) {
      if (messages[0]) {
        fieldErrors[field] = messages[0];
      }
    }

    return {
      ok: false,
      status: response.status,
      code: parsed?.error?.code ?? (expired ? 'expired' : 'error'),
      message: parsed?.error?.message ?? (expired ? EXPIRED_ERROR : GENERIC_ERROR),
      fieldErrors,
    };
  } catch {
    return { ok: false, status: 0, code: 'network', message: GENERIC_ERROR, fieldErrors: {} };
  } finally {
    window.clearTimeout(timer);
  }
}

/** Menyimpan Blob sebagai berkas unduhan di peramban. */
export function saveBlob(blob: Blob, filename: string): void {
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  link.rel = 'noopener';
  document.body.appendChild(link);
  link.click();
  link.remove();
  window.setTimeout(() => URL.revokeObjectURL(url), 10000);
}
