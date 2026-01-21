<?php

namespace Opscale\NovaDynamicResources\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Nova\Nova;
use Opscale\NovaDynamicResources\Models\Field;
use Opscale\NovaDynamicResources\Models\Template;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait UsesTemplate
{
    use HasDynamicData;

    /**
     * Boot the UsesTemplate trait.
     */
    public static function bootUsesTemplate(): void
    {
        static::retrieved(function ($model) {
            $model->loadAppends();
        });
    }

    /**
     * Initialize the UsesTemplate trait.
     */
    public function initializeUsesTemplate(): void
    {
        $this->with = array_merge($this->with, ['template.fields']);
    }

    /**
     * Get the template for this model.
     * Uses template_id if the field exists, otherwise falls back to class_name lookup.
     */
    public function template(): BelongsTo|HasOne
    {
        // Relation for inheritance scenarios
        if (in_array('template_id', $this->fillable)) {
            return $this->belongsTo(Template::class);
        }

        // Relation for composition scenarios
        return $this->hasOne(Template::class, 'related_class', 'class_name');
    }

    /**
     * Load dynamic appends from template fields.
     */
    protected function loadAppends(): void
    {
        $fieldNames = $this->template?->fields?->pluck('name')->toArray() ?? [];
        $this->appends = array_merge($this->appends, $fieldNames);
    }

    /**
     * Get the class name attribute (returns the Nova resource class for this model).
     */
    protected function className(): Attribute
    {
        return Attribute::make(
            get: fn () => Nova::resourceForModel($this),
        );
    }
}
