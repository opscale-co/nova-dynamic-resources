<?php

namespace Opscale\NovaDynamicResources\Nova;

use Illuminate\Support\Facades\Config;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaDynamicResources\Models\Field as Model;
use Override;

/**
 * @extends Resource<Model>
 */
class Field extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Opscale\NovaDynamicResources\Models\Field>
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
        return __('fields');
    }

    /**
     * Get the displayable label of the resource.
     */
    #[Override]
    public static function label(): string
    {
        return __('Fields');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    #[Override]
    public static function singularLabel(): string
    {
        return __('Field');
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
            'label' => Text::make(__('Label'), 'label')
                ->rules($model->validationRules['label']),

            'name' => Slug::make(__('Name'), 'name')
                ->from('label')
                ->separator('_')
                ->creationRules('nullable')
                ->updateRules($model->validationRules['name'])
                ->hideFromIndex(),

            'type' => Select::make(__('Type'), 'type')
                ->options(static::getBusinessTypeOptions())
                ->displayUsingLabels()
                ->searchable()
                ->rules($model->validationRules['type']),

            'required' => Boolean::make(__('Required'), 'required')
                ->rules($model->validationRules['required']),

            'rules' => KeyValue::make(__('Validation Rules'), 'rules')
                ->keyLabel(__('Rule'))
                ->valueLabel(__('Value'))
                ->nullable()
                ->hideWhenCreating(),

            'config' => KeyValue::make(__('Config'), 'config')
                ->keyLabel(__('Method'))
                ->valueLabel(__('Parameters'))
                ->nullable()
                ->hideWhenCreating(),

            'hooks' => KeyValue::make(__('Hooks'), 'hooks')
                ->keyLabel(__('Hook'))
                ->valueLabel(__('Parameters'))
                ->nullable()
                ->hideWhenCreating(),

            'data' => KeyValue::make(__('Data'), 'data')
                ->keyLabel(__('Key'))
                ->valueLabel(__('Value'))
                ->onlyOnDetail(),
        ];
    }

    /**
     * Get the business type options for the select field.
     *
     * @return array<string, string>
     */
    protected static function getBusinessTypeOptions(): array
    {
        /** @var array<string, mixed> $configFields */
        $configFields = Config::get('nova-dynamic-resources.fields', []);

        $options = [];
        foreach (array_keys($configFields) as $key) {
            $options[$key] = $key;
        }

        return $options;
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

            ...array_values(static::defaultFields()),
        ];
    }
}
