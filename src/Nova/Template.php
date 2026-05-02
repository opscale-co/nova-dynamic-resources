<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Nova;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Repeater;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Laravel\Nova\Tabs\Tab;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
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
     * Get the available base classes for dynamic resources.
     *
     * @return array<class-string, string>
     */
    final protected static function getRelatedResources(bool $checkRelation): array
    {
        $baseClasses = [];

        foreach (Nova::$resources as $resource) {
            if (is_subclass_of($resource, Record::class) === $checkRelation) {
                $baseClasses[$resource] = class_basename($resource);
            }
        }

        return $baseClasses;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Tabs\TabsGroup>
     */
    #[Override]
    public function fields(NovaRequest $request): array
    {
        return [
            Tab::group(fields: [
                Tab::make(__('Resource'), [
                    ...array_values($this->defaultFields($request)),
                ]),

                Tab::make(__('Fields'), [
                    'fields' => HasMany::make(__('Fields'), 'fields', Field::class),
                ]),

                Tab::make(__('Actions'), [
                    'actions' => HasMany::make(__('Actions'), 'actions', Action::class),
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
        /** @var array<int, \Laravel\Nova\Actions\Action> $actions */
        $actions = [
            ...parent::actions($request),
            CreateRecord::make(),
        ];

        return $actions;
    }

    /**
     * @return array<string, \Laravel\Nova\Fields\Field>
     */
    final protected function defaultFields(NovaRequest $request): array
    {
        return [
            'label' => Text::make(__('Label'), 'label')
                ->rules(fn (): array => $this->resolveRule('label'))
                ->help(__('Use a plural label for your resource.')),

            'singular_label' => Text::make(__('Singular Label'), 'singular_label')
                ->rules(fn (): array => $this->resolveRule('singular_label'))
                ->hideWhenCreating(),

            'uri_key' => Slug::make(__('URI Key'), 'uri_key')
                ->from('label')
                ->creationRules(fn (): array => $this->resolveRule('uri_key'))
                ->hideWhenCreating(),

            'type' => Select::make(__('Type'), 'type')
                ->options(Collection::make(TemplateType::cases())->mapWithKeys(fn (TemplateType $type): array => [
                    $type->value => $type->value,
                ])->all())
                ->displayUsingLabels()
                ->rules(fn (): array => $this->resolveRule('type')),

            'related_class' => Select::make(__('Related Class'), 'related_class')
                ->dependsOn('type', function (Select $field, NovaRequest $request, FormData $formData): void {
                    $type = $formData->resource('type');

                    if ($type === TemplateType::Inherited->value) {
                        $field->show()
                            ->options(static::getRelatedResources(true));
                    } elseif ($type === TemplateType::Composited->value) {
                        $field->show()
                            ->options(static::getRelatedResources(false));
                    } else {
                        $field->hide();
                    }
                })
                ->displayUsingLabels()
                ->searchable()
                ->rules(fn (): array => $this->resolveRule('related_class'))
                ->hideFromIndex()
                ->hide(),

            'fields' => Repeater::make(__('Fields'), 'fields')
                ->repeatables([
                    FieldRepeatable::make(),
                ])
                ->asHasMany(Field::class)
                ->required(),

            'title' => Text::make(__('Title'), 'title')
                ->rules(fn (): array => $this->resolveRule('title'))
                ->help(__('Define the property to be used as title.')),

            'actions' => Repeater::make(__('Actions'), 'actions')
                ->repeatables([
                    ActionRepeatable::make(),
                ])
                ->asHasMany(Action::class)
                ->hideWhenCreating(),
        ];
    }

    /**
     * @return array<int, ValidationRule|string>
     */
    final protected function resolveRule(string $key): array
    {
        return $this->model()?->validationRules[$key] ?? [];
    }
}
