<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminStatsService;
use Inertia\Inertia;
use Inertia\Response;

/** SCR-019: angka agregat tanpa identitas (FR-024, FR-025). */
final class DashboardController extends Controller
{
    public function __invoke(AdminStatsService $stats): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats->dashboard(),
            'min_group_size' => (int) config('relaxboss.admin.min_group_size'),
        ]);
    }
}
