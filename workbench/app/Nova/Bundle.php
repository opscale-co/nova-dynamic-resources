<?php

declare(strict_types=1);

namespace Workbench\App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaDynamicResources\Nova\Fields\TemplatedRepeater;
use Workbench\App\Models\Bundle as Model;
use Workbench\App\Nova\Item as ItemResource;

class Bundle extends Resource
{
    /**
     * @var class-string<Model>
     */
    public static $model = Model::class;

    public static $title = 'name';

    /**
     * @var array<int, string>
     */
    public static $search = ['id', 'name'];

    /**
     * @return array<mixed>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make(__('Name'), 'name')
                ->sortable()
                ->rules('required', 'string', 'max:255'),

            Textarea::make(__('Description'), 'description')
                ->rules('nullable', 'string')
                ->hideFromIndex(),

            TemplatedRepeater::make(__('Items'), 'items')
                ->forResource(ItemResource::class)
                ->asHasMany(ItemResource::class)
                ->uniqueField('uuid'),
        ];
    }
}
