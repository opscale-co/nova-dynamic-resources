<?php

namespace Opscale\NovaDynamicResources\Nova;

use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Repeater;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Laravel\Nova\Tabs\Tab;
use Opscale\NovaDynamicResources\Models\DynamicResource as Model;
use Opscale\NovaDynamicResources\Nova\Actions\CreateRecord;
use Opscale\NovaDynamicResources\Nova\Repeatables\Action;
use Opscale\NovaDynamicResources\Nova\Repeatables\Field;
use Override;
use ReflectionClass;
use ReflectionException;

/**
 * @extends Resource<Model>
 */
class DynamicResource extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Opscale\NovaDynamicResources\Models\DynamicResource>
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
        return __('dynamic-resources');
    }

    /**
     * Get the displayable label of the resource.
     */
    #[Override]
    public static function label(): string
    {
        return __('Dynamic Resources');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    #[Override]
    public static function singularLabel(): string
    {
        return __('Dynamic Resource');
    }

    /**
     * Get the available base classes for dynamic resources.
     *
     * @return array<string, string>
     */
    protected static function getAvailableBaseClasses(): array
    {
        $baseClasses = [];

        foreach (Nova::$resources as $resource) {
            // Skip if parent class is not DynamicRecord
            if (get_parent_class($resource) !== DynamicRecord::class) {
                continue;
            }

            // Skip anonymous classes
            try {
                $reflection = new ReflectionClass($resource);
                if ($reflection->isAnonymous()) {
                    continue;
                }
            } catch (ReflectionException $e) {
                continue;
            }

            $baseClasses[$resource] = class_basename($resource);
        }

        return $baseClasses;
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
        $baseClasses = static::getAvailableBaseClasses();

        return [
            Tab::group(fields: [
                Tab::make('Resource', [
                    Text::make(_('Label'), 'label')
                        ->rules(fn (): array => $this->model()?->validationRules['label'])
                        ->help('Use a plural label for your resource.'),

                    Text::make(_('Singular Label'), 'singular_label')
                        ->rules(fn (): array => $this->model()?->validationRules['singular_label'])
                        ->hideWhenCreating(),

                    Slug::make(_('URI Key'), 'uri_key')
                        ->from('label')
                        ->creationRules(fn (): array => $this->model()?->validationRules['uri_key'])
                        ->hideWhenCreating(),

                    Select::make(_('Base Class'), 'base_class')
                        ->options($baseClasses)
                        ->displayUsingLabels()
                        ->searchable()
                        ->rules(fn (): array => $this->model()?->validationRules['base_class'])
                        ->hideFromIndex()
                        ->canSee(function (NovaRequest $request) use ($baseClasses): bool {
                            return count($baseClasses) > 1;
                        }),

                    Repeater::make(_('Fields'), 'fields')
                        ->repeatables([
                            Field::make(),
                        ])
                        ->asHasMany(DynamicField::class),

                    Text::make(_('Title'), 'title')
                        ->rules(fn (): array => $this->model()?->validationRules['title'])
                        ->help('Define the property to be used as title.'),

                    Repeater::make(_('Actions'), 'actions')
                        ->repeatables([
                            Action::make(),
                        ])
                        ->asHasMany(DynamicAction::class)
                        ->hideWhenCreating(),
                ]),

                Tab::make('Fields', [
                    'fields' => HasMany::make(__('Fields'), 'fields', DynamicField::class),
                ]),

                Tab::make('Actions', [
                    'actions' => HasMany::make(__('Actions'), 'actions', DynamicAction::class),
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
}
