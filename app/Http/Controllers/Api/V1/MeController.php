<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MeResource;
use Illuminate\Http\Request;

/** API-M006. Hanya data milik pemilik token. */
final class MeController extends Controller
{
    public function __invoke(Request $request): MeResource
    {
        return new MeResource($request->user());
    }
}
