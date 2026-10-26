<?php

declare(strict_types=1);

namespace App\Actions\Account;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * API-022 (FR-033): foto profil. Gambar diproses ulang dengan GD menjadi JPEG persegi kecil sehingga
 * metadata (EXIF, lokasi) dan muatan tersembunyi tidak ikut tersimpan. Disimpan di disk privat
 * `avatars/{id}.jpg`; hanya pemilik yang bisa mengambilnya (RULE-042).
 */
final class ManageUserAvatar
{
    public function path(User $user): string
    {
        return 'avatars/'.$user->getKey().'.jpg';
    }

    public function exists(User $user): bool
    {
        return $user->avatar_updated_at !== null && Storage::disk('local')->exists($this->path($user));
    }

    /**
     * @throws ValidationException
     */
    public function store(User $user, UploadedFile $file): void
    {
        $bytes = $file->get();
        $source = is_string($bytes) ? @imagecreatefromstring($bytes) : false;

        if ($source === false) {
            throw ValidationException::withMessages(['avatar' => 'Gambar tidak bisa dibaca. Coba berkas lain.']);
        }

        $source = $this->applyOrientation($source, (string) $bytes);

        $width = imagesx($source);
        $height = imagesy($source);
        $side = min($width, $height);
        $size = (int) config('relaxboss.avatar.size');

        $target = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($target, 255, 255, 255);
        imagefill($target, 0, 0, $white); // PNG transparan menjadi latar putih
        imagecopyresampled($target, $source, 0, 0, intdiv($width - $side, 2), intdiv($height - $side, 2), $size, $size, $side, $side);

        ob_start();
        imagejpeg($target, null, (int) config('relaxboss.avatar.quality'));
        $jpeg = (string) ob_get_clean();

        if ($jpeg === '' || ! Storage::disk('local')->put($this->path($user), $jpeg)) {
            throw ValidationException::withMessages(['avatar' => 'Foto belum bisa disimpan. Coba lagi.']);
        }

        $user->forceFill(['avatar_updated_at' => now()])->save();
    }

    public function remove(User $user): void
    {
        Storage::disk('local')->delete($this->path($user));

        if ($user->avatar_updated_at !== null) {
            $user->forceFill(['avatar_updated_at' => null])->save();
        }
    }

    /** Hapus berkas saja (dipakai saat akun dihapus: barisnya sudah hilang). */
    public function purge(int $userId): void
    {
        Storage::disk('local')->delete('avatars/'.$userId.'.jpg');
    }

    private function applyOrientation(\GdImage $image, string $bytes): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data('data://image/jpeg;base64,'.base64_encode($bytes));
        $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;
        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        return $rotated === false ? $image : $rotated;
    }
}
