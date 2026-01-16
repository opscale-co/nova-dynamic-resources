<?php

namespace Opscale\NovaDynamicResources\Nova;

use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Repeater;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Laravel\Nova\Tabs\Tab;
use Opscale\NovaDynamicResources\Models\Template as Model;
use Opscale\NovaDynamicResources\Nova\Repeatables\Action as ActionRepeatable;
use Opscale\NovaDynamicResources\Nova\Repeatables\Field as FieldRepeatable;
use Opscale\NovaDynamicResources\Services\Actions\CreateRecord;
use Override;

/**
 * @extends Resource<Model>
 */
class Template extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Opscale\NovaDynamicResources\Models\Template>
     */
    public static $model = Model::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'singular_label';

    /**
     * The columns that should be searched.
     *
     * @var array<int, string>
     */
    public static $search = [
        'label', 'singular_label', 'uri_key',
    ];

    /**
     * Get the URI key for the resource.
     */
    #[Override]
    public static function uriKey(): string
    {
        return __('templates');
    }

    /**
     * Get the displayable label of the resource.
     */
    #[Override]
    public static function label(): string
    {
        return __('Templates');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    #[Override]
    public static function singularLabel(): string
    {
        return __('Template');
    }

    /**
     * Get the fields displayed by the resource.
     */
    /**
     * @return array<mixed>
     */
    #[Override]
    public function fields(NovaRequest $request): array
    {
        return [
            Tab::group(fields: [
                Tab::make(__('Template'), [
                    ...array_values($this->defaultFields($request)),
                ]),

                Tab::make(__('Fields'), [
                    HasMany::make(__('Fields'), 'fields', Field::class),
                ]),

                Tab::make(__('Actions'), [
                    HasMany::make(__('Actions'), 'actions', Action::class),
                ]),
            ]),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    #[Override]
    public function actions(NovaRequest $request): array
    {
        return [
            CreateRecord::make()->showInline(),
        ];
    }

    #[Override]
    protected function defaultFields(NovaRequest $request): array
    {
        $baseClasses = collect(Nova::$resources)
            ->mapWithKeys(function (string $resourceClass): array {
                return [$resourceClass::$model => $resourceClass::singularLabel()];
            });

        return [
            'base_class' => Select::make(__('Base Class'), 'base_class')
                ->options($baseClasses)
                ->displayUsingLabels()
                ->nullable()
                ->rules(fn (): array => $this->model()?->validationRules['base_class'])
                ->hideFromIndex(),

            'singular_label' => Text::make(__('Singular Label'), 'singular_label')
                ->rules(fn (): array => $this->model()?->validationRules['singular_label'])
                ->dependsOn('base_class', function (Text $field, NovaRequest $request, mixed $formData): void {
                    $resourceClass = Nova::resourceForModel($formData->base_class);
                    if ($resourceClass) {
                        $field->immutable()->setValue($resourceClass::singularLabel());
                    }
                }),

            'label' => Text::make(__('Label'), 'label')
                ->rules(fn (): array => $this->model()?->validationRules['label'])
                ->dependsOn('base_class', function (Text $field, NovaRequest $request, mixed $formData): void {
                    $resourceClass = Nova::resourceForModel($formData->base_class);
                    if ($resourceClass) {
                        $field->immutable()->setValue($resourceClass::label());
                    }
                }),

            'uri_key' => Text::make(__('URI Key'), 'uri_key')
                ->creationRules(fn (): array => $this->model()?->validationRules['uri_key'])
                ->dependsOn('base_class', function (Text $field, NovaRequest $request, mixed $formData): void {
                    $resourceClass = Nova::resourceForModel($formData->base_class);
                    if ($resourceClass) {
                        $field->immutable()->setValue($resourceClass::uriKey());
                    }
                }),

            'title' => Text::make(__('Title'), 'title')
                ->rules(fn (): array => $this->model()?->validationRules['title'])
                ->help(__('Define the property to be used as title.'))
                ->dependsOn('base_class', function (Text $field, NovaRequest $request, mixed $formData): void {
                    $resourceClass = Nova::resourceForModel($formData->base_class);
                    if ($resourceClass && property_exists($resourceClass, 'title')) {
                        $field->immutable()->setValue($resourceClass::$title);
                    }
                }),

            'fields' => Repeater::make(__('Fields'), 'fields')
                ->repeatables([
                    FieldRepeatable::make(),
                ])
                ->asHasMany(Field::class)
                ->required(),

            'actions' => Repeater::make(__('Actions'), 'actions')
                ->repeatables([
                    ActionRepeatable::make(),
                ])
                ->asHasMany(Action::class)
                ->hideWhenCreating(),
        ];
    }
}
