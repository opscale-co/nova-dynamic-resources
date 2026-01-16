<?php

namespace Opscale\NovaDynamicResources\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Opscale\NovaDynamicResources\Models\Field;
use Opscale\NovaDynamicResources\Models\Template;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasDynamicTemplate
{
    use HasDynamicData;

    /**
     * Boot the HasDynamicTemplate trait.
     */
    public static function bootHasDynamicTemplate(): void
    {
        static::addGlobalScope('withTemplate', function (Builder $builder): void {
            $builder->with(['template.fields']);
        });
    }

    /**
     * Get the template for this model (class-level, shared by all instances).
     */
    public function template(): HasOne
    {
        return $this->hasOne(Template::class, 'base_class', 'class_name');
    }

    /**
     * Get the fields for this model through the template.
     */
    public function fields(): HasManyThrough
    {
        return $this->hasManyThrough(
            Field::class,
            Template::class,
            'base_class',   // Foreign key on templates table
            'template_id',  // Foreign key on fields table
            'class_name',   // Local key on this model (virtual attribute)
            'id'            // Local key on templates table
        );
    }

    /**
     * Get the class name attribute (returns the model's class name).
     */
    protected function className(): Attribute
    {
        return Attribute::make(
            get: fn () => static::class,
        );
    }
}
