<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Nova\Nova;
use Opscale\NovaDynamicResources\Models\Template;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait UsesTemplate
{
    use HasDynamicData;
    use HasDynamicRelationships;

    /**
     * Boot the UsesTemplate trait.
     */
    final public static function bootUsesTemplate(): void
    {
        static::retrieved(function (Model $model): void {
            if (method_exists($model, 'loadAppends')) {
                $model->loadAppends();
            }
        });
    }

    /**
     * Initialize the UsesTemplate trait.
     */
    final public function initializeUsesTemplate(): void
    {
        $this->with = array_merge($this->with, ['template.fields']);
    }

    /**
     * Get the template for this model.
     * Uses template_id if the field exists, otherwise falls back to class_name lookup.
     *
     * @return BelongsTo<Template, $this>|HasOne<Template, $this>
     */
    final public function template(): BelongsTo|HasOne
    {
        // Relation for inheritance scenarios
        if (in_array('template_id', $this->fillable, true)) {
            return $this->belongsTo(Template::class);
        }

        // Relation for composition scenarios
        return $this->hasOne(Template::class, 'related_class', 'class_name');
    }

    /**
     * Load dynamic appends from template fields.
     */
    final protected function loadAppends(): void
    {
        /** @var Template|null $template */
        $template = $this->getRelationValue('template');

        if ($template === null) {
            return;
        }

        /** @var list<string> $fieldNames */
        $fieldNames = $template->fields->pluck('name')->all();
        $this->appends = array_merge($this->appends, $fieldNames);
    }

    /**
     * Get the class name attribute (returns the Nova resource class for this model).
     *
     * @return Attribute<string|null, never>
     */
    final protected function className(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => Nova::resourceForModel($this),
        );
    }
}
