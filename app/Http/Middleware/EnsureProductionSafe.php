<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Preflight\ProductionReadiness;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pengaman produksi tanpa database (Architecture bagian 12, RULE-048): bila APP_ENV=production dan
 * APP_DEBUG aktif, MAIL_MAILER=log, atau kontak krisis kosong atau dummy, aplikasi menolak melayani (503).
 * Hanya membaca config; tidak mencatat apa pun. Di luar produksi tidak berpengaruh.
 */
final class EnsureProductionSafe
{
    public function __construct(private readonly ProductionReadiness $readiness) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (app()->isProduction() && ! $this->readiness->isSafeToServe()) {
            abort(503);
        }

        return $next($request);
    }
}
