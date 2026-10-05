<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** SCR-011: sapaan, tiga kartu fitur, mood terakhir, hasil Asesmen terakhir. */
final class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardPresenter $presenter): Response
    {
        return Inertia::render('Dashboard', $presenter->page($request->user()));
    }
}
