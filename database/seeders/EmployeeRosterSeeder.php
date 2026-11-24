<?php

namespace Database\Seeders;

use App\Enums\EmploymentQuality;
use App\Enums\StaffLevel;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Section;
use App\Support\Rut;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Carga la dotación inicial desde database/data/dotacion-2026-09-14.csv.
 * Une los dos nombres y asigna departamento o sección solo cuando ya existe una unidad con el mismo nombre.
 */
class EmployeeRosterSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/dotacion-2026-09-14.csv');

        if (! is_file($path)) {
            throw new RuntimeException('No está el archivo de dotación en database/data/dotacion-2026-09-14.csv.');
        }

        $rows = $this->rows($path);
        $departments = $this->units(Department::query()->pluck('name', 'id'));
        $sections = $this->units(Section::query()->pluck('name', 'id'));
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $unmatched = [];

        foreach ($rows as $line => $row) {
            $rut = Rut::normalize((string) ($row[9] ?? ''));

            if (! Rut::isValid($rut)) {
                $skipped++;
                $this->command?->warn("Fila {$line}: RUT no válido ({$rut}).");

                continue;
            }

            $firstName = $this->join((string) ($row[2] ?? ''), (string) ($row[3] ?? ''));
            $paternal = $this->clean((string) ($row[0] ?? ''));
            $maternal = $this->clean((string) ($row[1] ?? ''));
            $birthDate = $this->date((string) ($row[7] ?? ''));

            if ($firstName === '' || $paternal === '' || $maternal === '' || $birthDate === null) {
                $skipped++;
                $this->command?->warn("Fila {$line}: faltan nombres o la fecha de nacimiento.");

                continue;
            }

            $quality = $this->quality((string) ($row[10] ?? ''));

            if ($quality === null) {
                $skipped++;
                $this->command?->warn("Fila {$line}: calidad desconocida ({$this->clean((string) ($row[10] ?? ''))}).");

                continue;
            }

            [$departmentId, $sectionId, $unit] = $this->assignment((string) ($row[5] ?? ''), $departments, $sections);

            if ($unit !== '' && $departmentId === null && $sectionId === null) {
                $unmatched[$unit] = true;
            }

            $hiredRaw = (string) ($row[14] ?? '');
            $hiredOn = $this->date($hiredRaw);

            if ($this->clean($hiredRaw) !== '' && $hiredOn === null) {
                $this->command?->warn("Fila {$line}: fecha de ingreso no válida.");
            }

            $staffLevel = $this->staffLevel((string) ($row[12] ?? ''));

            if ($this->clean((string) ($row[12] ?? '')) !== '' && $staffLevel === null) {
                $this->command?->warn("Fila {$line}: estamento desconocido ({$this->clean((string) ($row[12] ?? ''))}).");
            }

            $employee = Employee::query()->firstOrNew(['rut' => $rut]);
            $email = $this->email((string) ($row[15] ?? ''), $employee);

            $employee->fill([
                'first_name' => $firstName,
                'paternal_last_name' => $paternal,
                'maternal_last_name' => $maternal,
                'birth_date' => $birthDate,
                'profession' => $this->profession((string) ($row[6] ?? '')),
                'staff_level' => $staffLevel,
                'grade' => $this->blank((string) ($row[13] ?? '')),
                'hired_on' => $hiredOn,
                'email' => $email,
                'employment_quality' => $quality,
                'department_id' => $departmentId,
                'section_id' => $sectionId,
            ]);

            $employee->exists ? $updated++ : $created++;
            $employee->save();
        }

        $this->command?->info("Dotación: {$created} nuevos, {$updated} actualizados, {$skipped} omitidos.");

        foreach (array_keys($unmatched) as $unit) {
            $this->command?->warn("Sin unidad con el nombre «{$unit}». Esas personas quedaron sin departamento ni sección.");
        }
    }

    /**
     * @return array<int, list<string|null>>
     */
    private function rows(string $path): array
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('No se pudo leer el archivo de dotación.');
        }

        if (! mb_check_encoding($contents, 'UTF-8')) {
            $contents = mb_convert_encoding($contents, 'UTF-8', 'Windows-1252');
        }

        $handle = fopen('php://memory', 'rb+');

        if ($handle === false) {
            throw new RuntimeException('No se pudo leer el archivo de dotación.');
        }

        fwrite($handle, $contents);
        rewind($handle);

        $header = fgetcsv($handle, 0, ';');
        $rows = [];
        $line = 1;

        if (is_array($header) && isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]) ?? $header[0];
        }

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $line++;

            if ($this->clean(implode('', $row)) === '') {
                continue;
            }

            $rows[$line] = $row;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @param  Collection<int|string, string>  $names
     * @return array<string, int>
     */
    private function units($names): array
    {
        $indexed = [];

        foreach ($names as $id => $name) {
            $indexed[$this->unitKey($name)] = (int) $id;
        }

        return $indexed;
    }

    /**
     * @param  array<string, int>  $departments
     * @param  array<string, int>  $sections
     * @return array{0: ?int, 1: ?int, 2: string}
     */
    private function assignment(string $unit, array $departments, array $sections): array
    {
        $label = $this->clean($unit);
        $key = $this->unitKey($label);

        if ($key === '') {
            return [null, null, ''];
        }

        if (str_starts_with($key, 'departamento')) {
            return [$departments[$key] ?? null, null, $label];
        }

        if (str_starts_with($key, 'seccion')) {
            return [null, $sections[$key] ?? null, $label];
        }

        if (isset($departments[$key])) {
            return [$departments[$key], null, $label];
        }

        return [null, $sections[$key] ?? null, $label];
    }

    private function quality(string $value): ?EmploymentQuality
    {
        return match ($this->plain($value)) {
            'contrata' => EmploymentQuality::Contract,
            'honorario', 'honorarios' => EmploymentQuality::Fee,
            'planta' => EmploymentQuality::Permanent,
            default => null,
        };
    }

    private function staffLevel(string $value): ?StaffLevel
    {
        return match ($this->plain($value)) {
            'profesional' => StaffLevel::Professional,
            'tecnico' => StaffLevel::Technical,
            'directivo' => StaffLevel::Directive,
            'administrativo' => StaffLevel::Administrative,
            'auxiliar' => StaffLevel::Auxiliary,
            default => null,
        };
    }

    private function profession(string $value): ?string
    {
        $value = $this->clean($value);

        if ($value === '' || $this->plain($value) === 'no tiene') {
            return null;
        }

        return $value;
    }

    private function email(string $value, Employee $employee): ?string
    {
        $email = mb_strtolower($this->clean($value));

        if ($email === '') {
            return null;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->command?->warn("Correo no válido para {$employee->rut}: {$email}.");

            return null;
        }

        $taken = Employee::query()
            ->where('email', $email)
            ->when($employee->exists, fn ($query) => $query->whereKeyNot($employee->id))
            ->exists();

        if ($taken) {
            $this->command?->warn("Correo repetido, se dejó vacío: {$email}.");

            return null;
        }

        return $email;
    }

    private function join(string $first, string $second): string
    {
        return trim($this->clean($first).' '.$this->clean($second));
    }

    private function date(string $value): ?Carbon
    {
        $value = $this->clean($value);

        if ($value === '') {
            return null;
        }

        $date = Carbon::createFromFormat('!d-m-Y', $value);

        if ($date === false || $date->format('d-m-Y') !== $value) {
            return null;
        }

        return $date;
    }

    private function unitKey(string $value): string
    {
        return str_replace('deparamento', 'departamento', $this->plain($value));
    }

    private function plain(string $value): string
    {
        $value = mb_strtolower($this->clean($value));
        $value = strtr($value, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);

        return $value;
    }

    private function clean(string $value): string
    {
        $value = trim($value);

        return preg_replace('/\s+/u', ' ', $value) ?? $value;
    }

    private function blank(string $value): ?string
    {
        $value = $this->clean($value);

        return $value === '' ? null : $value;
    }
}
