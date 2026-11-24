<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class StoredUpload
{
    public const DISK = 'local';

    public static function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, self::DISK);
    }

    public static function replace(?UploadedFile $file, string $directory, ?string $current = null): ?string
    {
        if ($file === null) {
            return $current;
        }

        $path = self::store($file, $directory);
        self::delete($current);

        return $path;
    }

    public static function delete(?string $path): void
    {
        if ($path !== null && $path !== '' && Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function apply(Request $request, array $data, string $input, string $column, string $directory, ?string $current = null): array
    {
        if ($request->hasFile($input)) {
            $data[$column] = self::replace($request->file($input), $directory, $current);
        } elseif ($request->boolean('remove_'.$input)) {
            self::delete($current);
            $data[$column] = null;
        }

        return $data;
    }
}
