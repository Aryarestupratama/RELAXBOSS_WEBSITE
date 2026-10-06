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
