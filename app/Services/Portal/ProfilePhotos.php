<?php

namespace App\Services\Portal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Portal photos of students (StudentPhotos, PLAN.md §5.8) and teachers (TeacherPhotos); the owner keeps
 * only `photo_path` on the public disk (invariant 6). Every upload is decoded and re-encoded with GD, which drops
 * EXIF / GPS metadata and anything appended to the image (polyglot payloads). The longest
 * side is scaled to 512 px; WebP when GD supports it, else JPEG (else PNG).
 */
abstract class ProfilePhotos
{
    const MAX_SIDE = 512;
    /** Per-owner folder on the public disk, set by each subclass. */
    const DIRECTORY = '';

    public function store(Model $owner, UploadedFile $file)
    {
        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (! $source) {
            throw new RuntimeException('The image could not be decoded.');
        }

        $image = $this->scale($this->orient($source, $file));
        list($extension, $bytes) = $this->encode($image);
        imagedestroy($image);

        $path = static::DIRECTORY . '/' . $owner->id . '/' . Str::uuid() . '.' . $extension;
        Storage::disk('public')->put($path, $bytes);

        $previous = $owner->photo_path;
        $owner->forceFill(['photo_path' => $path])->save();
        if ($previous) {
            Storage::disk('public')->delete($previous);
        }

        return $path;
    }

    public function delete(Model $owner)
    {
        if ($owner->photo_path) {
            Storage::disk('public')->delete($owner->photo_path);
            $owner->forceFill(['photo_path' => null])->save();
        }
    }

    public static function url(Model $owner)
    {
        return $owner->photo_path ? Storage::disk('public')->url($owner->photo_path) : null;
    }

    /** Phone cameras store the rotation in EXIF instead of rotating the pixels. */
    protected function orient($image, UploadedFile $file)
    {
        if (! function_exists('exif_read_data') || $file->getMimeType() !== 'image/jpeg') {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $angles = [3 => 180, 6 => -90, 8 => 90];
        $orientation = is_array($exif) && isset($exif['Orientation']) ? (int) $exif['Orientation'] : 1;
        if (! isset($angles[$orientation])) {
            return $image;
        }

        $rotated = imagerotate($image, $angles[$orientation], 0);
        imagedestroy($image);

        return $rotated;
    }

    protected function scale($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $ratio = min(1, self::MAX_SIDE / max($width, $height));
        $target = imagecreatetruecolor(max(1, (int) round($width * $ratio)), max(1, (int) round($height * $ratio)));
        // White behind transparent PNGs (photos are shown on cards).
        imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255));
        imagecopyresampled($target, $image, 0, 0, 0, 0, imagesx($target), imagesy($target), $width, $height);
        imagedestroy($image);

        return $target;
    }

    /** @return array [extension, bytes] */
    protected function encode($image)
    {
        ob_start();
        if (function_exists('imagewebp') && (imagetypes() & IMG_WEBP)) {
            imagewebp($image, null, 80);
            $extension = 'webp';
        } elseif (imagetypes() & IMG_JPG) {
            imagejpeg($image, null, 82);
            $extension = 'jpg';
        } else {
            imagepng($image, null, 9);
            $extension = 'png';
        }

        return [$extension, ob_get_clean()];
    }
}
