<?php

namespace Opscale\NovaDynamicResources\Nova\Repeatables;

use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Override;

class Action extends Repeatable
{
    /**
     * Get the fields for this repeatable.
     */
    /**
     * @return array<mixed>
     */
    #[Override]
    final public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Label')
                ->rules('required', 'string', 'min:1', 'max:255'),

            Text::make('Class Name', 'classname')
                ->rules('required', 'string', 'min:1', 'regex:/^[a-zA-Z_\\\\][a-zA-Z0-9_\\\\]*$/'),
        ];
    }
}
