<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Nova\Repeatables;

use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NovaDynamicResources\Models\Field as Model;
use Opscale\NovaDynamicResources\Nova\Field as Resource;
use Override;

class Field extends Repeatable
{
    /**
     * The underlying model the repeatable represents.
     *
     * @var class-string
     */
    public static $model = Model::class;

    /**
     * Get the fields for this repeatable.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    #[Override]
    final public function fields(NovaRequest $request): array
    {
        $fields = Resource::defaultFields();
        unset($fields['name'], $fields['rules'], $fields['config'], $fields['hooks'], $fields['data']);

        /** @var array<int, \Laravel\Nova\Fields\Field> $merged */
        $merged = [
            ...parent::fields($request),
            ...array_values($fields),
        ];

        return $merged;
    }
}
