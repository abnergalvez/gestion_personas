<?php

namespace App\Models\Concerns;

use App\Support\StoredUpload;

trait DeletesStoredFiles
{
    public static function bootDeletesStoredFiles(): void
    {
        static::deleting(function (self $model): void {
            foreach ($model->storedFiles() as $column) {
                StoredUpload::delete($model->getAttribute($column));
            }
        });
    }

    /**
     * @return list<string>
     */
    public function storedFiles(): array
    {
        return [];
    }
}
