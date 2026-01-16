<?php

namespace Opscale\NovaDynamicResources\Models\Repositories;

use Illuminate\Support\Str;

trait TemplateRepository
{
    /**
     * Boot the TemplateRepository trait.
     */
    public static function bootTemplateRepository(): void
    {
        static::creating(function ($model): void {
            // Auto-populate singular_label if not set
            if (empty($model->singular_label) && ! empty($model->label)) {
                $model->singular_label = Str::singular($model->label);
            }

            // Auto-populate label if not set
            if (empty($model->label) && ! empty($model->singular_label)) {
                $model->label = Str::plural($model->singular_label);
            }

            // Auto-populate uri_key if not set
            if (empty($model->uri_key) && ! empty($model->label)) {
                $model->uri_key = Str::slug($model->label);
            }
        });
    }
}
