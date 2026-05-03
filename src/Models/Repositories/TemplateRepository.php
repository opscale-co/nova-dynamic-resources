<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Models\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Template;

trait TemplateRepository
{
    /**
     * Boot the TemplateRepository trait.
     */
    final public static function bootTemplateRepository(): void
    {
        static::creating(function (Template $model): void {
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
     * Look up a template by its primary key.
     */
    final public static function findById(string $id): ?Template
    {
        return Template::query()->find($id);
    }

    /**
     * Scope a query to only include instantiable templates (Dynamic or Inherited).
     *
     * @param  Builder<Template>  $query
     * @return Builder<Template>
     */
    final public function scopeInstantiables(Builder $query): Builder
    {
        return $query->whereIn('type', [
            TemplateType::Dynamic,
            TemplateType::Inherited,
        ]);
    }
}
