<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

final class RosterPositions
{
    /**
     * Asigna el cargo de la planilla a los funcionarios que ya existen, buscándolos por RUT.
     */
    public static function assignExisting(?string $path = null): int
    {
        $path ??= database_path('data/dotacion-2026-09-14.csv');

        if (! is_file($path)) {
            return 0;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return 0;
        }

        if (! mb_check_encoding($contents, 'UTF-8')) {
            $contents = mb_convert_encoding($contents, 'UTF-8', 'Windows-1252');
        }

        $handle = fopen('php://memory', 'rb+');

        if ($handle === false) {
            return 0;
        }

        fwrite($handle, $contents);
        rewind($handle);
        fgetcsv($handle, 0, ';');

        $updated = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rut = Rut::normalize((string) ($row[9] ?? ''));
            $position = self::clean((string) ($row[4] ?? ''));

            if (! Rut::isValid($rut) || $position === '') {
                continue;
            }

            $updated += DB::table('employees')->where(function ($query) use ($rut): void {
                $query->where('rut', $rut)->orWhere('rut', Rut::format($rut));
            })->update([
                'position' => $position,
                'updated_at' => now(),
            ]);
        }

        fclose($handle);

        return $updated;
    }

    private static function clean(string $value): string
    {
        $value = trim($value);

        return preg_replace('/\s+/u', ' ', $value) ?? $value;
    }
}
