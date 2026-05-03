<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Nova\Repeatables;

use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NovaDynamicResources\Models\Relationship as Model;
use Opscale\NovaDynamicResources\Nova\Relationship as Resource;
use Override;

class Relationship extends Repeatable
{
    /**
     * @var class-string
     */
    public static $model = Model::class;

    /**
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    #[Override]
    final public function fields(NovaRequest $request): array
    {
        $fields = Resource::defaultFields();
        unset($fields['name'], $fields['rules'], $fields['config']);

        /** @var array<int, \Laravel\Nova\Fields\Field> $merged */
        $merged = [
            ...parent::fields($request),
            ...array_values($fields),
        ];

        return $merged;
    }
}
