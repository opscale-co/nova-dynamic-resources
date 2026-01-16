<?php

namespace Opscale\NovaDynamicResources\Models\Repositories;

use Illuminate\Support\Str;

trait FieldRepository
{
    /**
     * Boot the FieldRepository trait.
     */
    public static function bootFieldRepository(): void
    {
        static::creating(function ($model): void {
            // Auto-populate name if not set
            if (empty($model->name) && ! empty($model->label)) {
                $model->name = Str::slug($model->label, '_');
            }
        });
    }
}
