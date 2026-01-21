<?php

namespace Opscale\NovaDynamicResources\Models\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;

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

    /**
     * Scope a query to only include instantiable templates (Dynamic or Inherited).
     *
     * @param  Builder<\Opscale\NovaDynamicResources\Models\Template>  $query
     * @return Builder<\Opscale\NovaDynamicResources\Models\Template>
     */
    public function scopeInstantiables(Builder $query): Builder
    {
        return $query->whereIn('type', [
            TemplateType::Dynamic,
            TemplateType::Inherited,
        ]);
    }
}
