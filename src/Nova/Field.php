<?php

namespace Opscale\NovaDynamicResources\Nova;

use Illuminate\Support\Facades\Config;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\MultiSelect;
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

             'rules' => MultiSelect::make(__('Validation Rules'), 'rules')
                ->options([
                    'required' => __('Required'),
                    'nullable' => __('Nullable'),
                    'sometimes' => __('Sometimes'),
                    'filled' => __('Filled (not empty if present)'),
                    'confirmed' => __('Confirmed (needs _confirmation field)'),
                    'gt:0' => __('Greater than zero'),
                    'gte:0' => __('Greater than or equal to zero'),
                    'lt:0' => __('Less than zero'),
                    'lte:0' => __('Less than or equal to zero'),
                    'min:1' => __('Minimum 1'),
                    'max:255' => __('Maximum 255'),
                    'max:65535' => __('Maximum 65535'),
                    'between:1,100' => __('Between 1 and 100'),
                    'between:0,1' => __('Between 0 and 1'),
                    'distinct' => __('Distinct (unique in array)'),
                    'lowercase' => __('Lowercase'),
                    'uppercase' => __('Uppercase'),
                    'max:2048' => __('Max file 2MB'),
                    'max:10240' => __('Max file 10MB'),
                    'mimes:pdf' => __('PDF only'),
                    'mimes:jpg,png,webp' => __('Images (jpg, png, webp)'),
                    'mimes:xlsx,xls,csv' => __('Excel/CSV'),
                    'after:today' => __('After today'),
                    'after_or_equal:today' => __('Today or after'),
                    'before:today' => __('Before today'),
                    'before_or_equal:today' => __('Today or before'),
                ]),

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
