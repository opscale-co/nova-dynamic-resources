<?php

namespace Opscale\NovaDynamicResources\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaDynamicResources\Models\DynamicAction as Model;
use Override;

/**
 * @extends Resource<Model>
 */
class DynamicAction extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Opscale\NovaDynamicResources\Models\DynamicAction>
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
        return 'dynamic-actions';
    }

    /**
     * Get the displayable label of the resource.
     */
    #[Override]
    public static function label(): string
    {
        return _('Dynamic Actions');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    #[Override]
    public static function singularLabel(): string
    {
        return _('Dynamic Action');
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
            'class' => Text::make(_('Class'), 'class')
                ->rules($model->validationRules['class']),

            'label' => Text::make(_('Label'), 'label')
                ->rules($model->validationRules['label']),

            'config' => KeyValue::make(_('Config'), 'config')
                ->keyLabel(_('Key'))
                ->valueLabel(_('Value'))
                ->nullable()
                ->hideWhenCreating(),

            'metadata' => KeyValue::make(_('Metadata'), 'metadata')
                ->keyLabel(_('Key'))
                ->valueLabel(_('Value'))
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
            BelongsTo::make(_('Resource'), 'resource', DynamicResource::class)
                ->sortable()
                ->filterable(),

            ...static::defaultFields(),
        ];
    }
}
