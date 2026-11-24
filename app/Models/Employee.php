<?php

namespace App\Models;

use App\Enums\EmploymentQuality;
use App\Enums\MaritalStatus;
use App\Models\Concerns\DeletesStoredFiles;
use App\Support\Rut;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Employee extends Model
{
    use DeletesStoredFiles;

    protected $fillable = [
        'rut',
        'first_name',
        'paternal_last_name',
        'maternal_last_name',
        'birth_date',
        'profession',
        'marital_status',
        'grade',
        'employment_quality',
        'department_id',
        'section_id',
        'photo_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'marital_status' => MaritalStatus::class,
            'employment_quality' => EmploymentQuality::class,
        ];
    }

    public function storedFiles(): array
    {
        return ['photo_path'];
    }

    protected static function booted(): void
    {
        static::deleting(function (Employee $employee): void {
            $employee->trainingCourses->each->delete();
            $employee->medicalLeaves->each->delete();
            $employee->familyDependents->each->delete();
            $employee->permits->each->delete();
            $employee->evaluations->each->delete();
            $employee->contracts->each(function (Contract $contract): void {
                $contract->delete();
            });
        });
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim(implode(' ', array_filter([
            $this->first_name,
            $this->paternal_last_name,
            $this->maternal_last_name,
        ]))));
    }

    protected function formattedRut(): Attribute
    {
        return Attribute::get(fn (): string => $this->rut ? Rut::format($this->rut) : '');
    }

    protected function initials(): Attribute
    {
        return Attribute::get(fn (): string => mb_strtoupper(
            mb_substr((string) $this->first_name, 0, 1).mb_substr((string) $this->paternal_last_name, 0, 1)
        ));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $parts = preg_split('/\s+/u', $term) ?: [];

        return $query->where(function (Builder $query) use ($parts, $term): void {
            $query->where(function (Builder $query) use ($parts): void {
                foreach ($parts as $part) {
                    $like = '%'.self::escapeLike($part).'%';
                    $query->where(function (Builder $query) use ($like): void {
                        $query->where('first_name', 'like', $like)
                            ->orWhere('paternal_last_name', 'like', $like)
                            ->orWhere('maternal_last_name', 'like', $like)
                            ->orWhere('profession', 'like', $like)
                            ->orWhere('rut', 'like', $like);
                    });
                }
            });

            $rut = str_replace('.', '', Rut::normalize($term));

            if ($rut !== '') {
                $query->orWhere('rut', 'like', '%'.self::escapeLike($rut).'%');
            }
        });
    }

    /**
     * @return Collection<int|string, string>
     */
    public static function options(): Collection
    {
        return static::query()
            ->orderBy('paternal_last_name')
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn (self $employee) => [$employee->id => $employee->full_name.' · '.$employee->formatted_rut]);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function headedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'head_employee_id');
    }

    public function deputyDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'deputy_employee_id');
    }

    public function analystDepartments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_analysts')->withTimestamps();
    }

    public function headedSections(): HasMany
    {
        return $this->hasMany(Section::class, 'head_employee_id');
    }

    public function deputySections(): HasMany
    {
        return $this->hasMany(Section::class, 'deputy_employee_id');
    }

    public function analystSections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'section_analysts')->withTimestamps();
    }

    public function trainingCourses(): HasMany
    {
        return $this->hasMany(TrainingCourse::class);
    }

    public function medicalLeaves(): HasMany
    {
        return $this->hasMany(MedicalLeave::class);
    }

    public function familyDependents(): HasMany
    {
        return $this->hasMany(FamilyDependent::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function permits(): HasMany
    {
        return $this->hasMany(Permit::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    private static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
