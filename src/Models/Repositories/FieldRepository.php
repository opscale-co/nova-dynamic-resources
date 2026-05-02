<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Models\Repositories;

use Illuminate\Support\Str;
use Opscale\NovaDynamicResources\Models\Field;

trait FieldRepository
{
    /**
     * Boot the FieldRepository trait.
     */
    final public static function bootFieldRepository(): void
    {
        static::creating(function (Field $model): void {
            // Auto-populate name if not set
            if (empty($model->name) && ! empty($model->label)) {
                $model->name = Str::slug($model->label, '_');
            }
        });
    }
}
