<?php

namespace Opscale\NovaDynamicResources\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaDynamicResources\Models\Action as Model;
use Override;

/**
 * @extends Resource<Model>
 */
class Action extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Opscale\NovaDynamicResources\Models\Action>
     */
    public static $model = Model::class;

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'label';

    /**
     * The columns that should be searched.
     *
     * @var array<int, string>
     */
    public static $search = [
        'label',
    ];

    /**
     * Get the URI key for the resource.
     */
    #[Override]
    public static function uriKey(): string
    {
        return __('actions');
    }

    /**
     * Get the displayable label of the resource.
     */
    #[Override]
    public static function label(): string
    {
        return __('Actions');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    #[Override]
    public static function singularLabel(): string
    {
        return __('Action');
    }

    /**
     * Get the default fields for this resource.
     *
     * @return array<mixed>
     */
    public static function defaultFields(): array
    {
        $model = new Model;

        return [
            'class' => Text::make(__('Class'), 'class')
                ->rules($model->validationRules['class']),

            'label' => Text::make(__('Label'), 'label')
                ->rules($model->validationRules['label']),

            'config' => KeyValue::make(__('Config'), 'config')
                ->keyLabel(__('Key'))
                ->valueLabel(__('Value'))
                ->nullable()
                ->hideWhenCreating(),

            'metadata' => KeyValue::make(__('Metadata'), 'metadata')
                ->keyLabel(__('Key'))
                ->valueLabel(__('Value'))
                ->nullable()
                ->hideWhenCreating(),
        ];
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<mixed>
     */
    #[Override]
    public function fields(NovaRequest $request): array
    {
        return [
            BelongsTo::make(__('Template'), 'template', Template::class)
                ->sortable()
                ->filterable(),

            ...static::defaultFields(),
        ];
    }
}
