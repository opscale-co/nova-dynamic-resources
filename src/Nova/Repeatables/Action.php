<?php

namespace Opscale\NovaDynamicResources\Nova\Repeatables;

use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NovaDynamicResources\Models\Action as Model;
use Opscale\NovaDynamicResources\Nova\Action as Resource;
use Override;

class Action extends Repeatable
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
     * @return array<mixed>
     */
    #[Override]
    final public function fields(NovaRequest $request): array
    {
        $fields = Resource::defaultFields();
        unset($fields['config'], $fields['data']);

        return array_values($fields);
    }
}
