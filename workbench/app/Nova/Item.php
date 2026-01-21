<?php

namespace Workbench\App\Nova;

use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaDynamicResources\Nova\Concerns\UsesTemplate;
use Workbench\App\Models\Item as Model;

class Item extends Resource
{
    use UsesTemplate;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static $model = Model::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array<int, string>
     */
    public static $search = [
        'id',
        'data',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<mixed>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make(__('Name'), 'name')
                ->sortable()
                ->rules('required', 'string', 'max:255'),

            Textarea::make(__('Description'), 'description')
                ->rules('nullable', 'string')
                ->hideFromIndex(),

            Currency::make(__('Price'), 'price')
                ->sortable()
                ->rules('required', 'numeric', 'min:0'),

            Number::make(__('Stock'), 'stock')
                ->sortable()
                ->rules('required', 'integer', 'min:0')
                ->default(0),

            ...$this->renderTemplateFields(),
        ];
    }
}
