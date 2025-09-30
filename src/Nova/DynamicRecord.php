<?php

namespace Opscale\NovaDynamicResources\Nova;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaDynamicResources\Models\DynamicRecord as Model;
use Opscale\NovaDynamicResources\Models\DynamicResource as Template;
use Override;

/**
 * @extends Resource<Model>
 *
 * @property-read Template $template
 */
abstract class DynamicRecord extends Resource
{
    public static Template $template;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Opscale\NovaDynamicResources\Models\DynamicRecord>
     */
    public static $model = Model::class;

    /**
     * Get the displayable singular label of the resource.
     */
    #[Override]
    public static function singularLabel(): string
    {
        /** @var string $singular_label */
        $singular_label = static::$template->getAttribute('singular_label');

        return $singular_label;
    }

    /**
     * Get the displayable label of the resource.
     */
    #[Override]
    public static function label(): string
    {
        /** @var string $label */
        $label = static::$template->getAttribute('label');

        return $label;
    }

    /**
     * Get the URI key for the resource.
     */
    #[Override]
    public static function uriKey(): string
    {
        /** @var string $uri_key */
        $uri_key = static::$template->getAttribute('uri_key');

        return $uri_key;
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    #[Override]
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('resource_id', static::$template->id);
    }

    /**
     * Get the value that should be displayed to represent the resource.
     */
    #[Override]
    public function title(): string
    {
        /** @var string $label */
        $label = static::$template->getAttribute('label');

        return $label;
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
        $fields = [
            Hidden::make('Resource', 'resource_id')
                ->default(static::$template->id)
                ->rules('required'),
        ];

        /** @var array<array{fields: array{type: string, label: string, name: string, rules?: array<mixed>, config?: array<mixed>}}> $templateFields */
        $templateFields = static::$template->getAttribute('fields');

        foreach ($templateFields as $templateField) {
            $fields[] = $this->parseField($templateField);
        }

        return $fields;
    }

    /**
     * Get the actions available for the resource.
     */
    /**
     * @return array<mixed>
     */
    #[Override]
    public function actions(NovaRequest $request): array
    {
        $actions = [];
        /** @var array<array{fields: array{class: class-string, config?: array<string, mixed>}}>|null $templateActions */
        $templateActions = static::$template->getAttribute('actions');

        if ($templateActions !== null) {
            foreach ($templateActions as $templateAction) {
                $actions[] = $this->parseAction($templateAction);
            }
        }

        return $actions;
    }

    /**
     * @param  array{fields: array{type: string, label: string, name: string, rules?: array<mixed>, config?: array<mixed>}}  $field
     */
    private function parseField(array $field): mixed
    {
        /** @var array{field: class-string, rules?: array<mixed>, config?: array<mixed>}|null $component */
        $component = Config::get('nova-dynamic-resources.fields.' . $field['fields']['type'], null);

        if ($component === null) {
            throw new InvalidArgumentException('Invalid field type: ' . $field['fields']['type']);
        }

        /** @var array<mixed> $rules */
        $rules = array_merge($component['rules'] ?? [], $field['fields']['rules'] ?? []);
        /** @var array<string, mixed> $config */
        $config = array_merge($component['config'] ?? [], $field['fields']['config'] ?? []);

        $fieldClass = $component['field'];
        $instance = $fieldClass::make(
            $field['fields']['label'],
            'data->' . $field['fields']['name'],
        )->rules($rules);

        if (! empty($config)) {
            foreach ($config as $method => $parameters) {
                if (is_string($method) && method_exists($instance, $method)) {
                    $instance = is_array($parameters) ? $instance->{$method}(...$parameters) : $instance->{$method}($parameters);
                }
            }
        }

        return $instance;
    }

    /**
     * @param  array{fields: array{class: class-string, config?: array<string, mixed>}}  $action
     */
    private function parseAction(array $action): mixed
    {
        $actionClass = $action['fields']['class'];
        $instance = new $actionClass;

        if (! empty($action['fields']['config']) && is_array($action['fields']['config'])) {
            foreach ($action['fields']['config'] as $method => $parameters) {
                if (is_string($method) && method_exists($instance, $method)) {
                    $instance = is_array($parameters) ? $instance->{$method}(...$parameters) : $instance->{$method}($parameters);
                }
            }
        }

        return $instance;
    }
}
