<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Account\ManageUserAvatar;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\UploadAvatarRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/** API-022: foto profil. Hanya milik pengguna yang masuk; tidak ada route untuk foto orang lain (RULE-042). */
final class AvatarController extends Controller
{
    public function show(Request $request, ManageUserAvatar $avatar): Response
    {
        $user = $request->user();

        abort_unless($avatar->exists($user), 404);

        return Storage::disk('local')->response($avatar->path($user), null, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    public function store(UploadAvatarRequest $request, ManageUserAvatar $avatar): RedirectResponse
    {
        $avatar->store($request->user(), $request->file('avatar'));

        return back()->with('status', 'avatar-updated');
    }

    public function destroy(Request $request, ManageUserAvatar $avatar): RedirectResponse
    {
        $avatar->remove($request->user());

        return back()->with('status', 'avatar-removed');
    }
}
