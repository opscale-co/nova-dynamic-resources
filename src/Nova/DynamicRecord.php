<?php

namespace Opscale\NovaDynamicResources\Nova;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaDynamicResources\Models\DynamicRecord as Model;
use Opscale\NovaDynamicResources\Models\DynamicResource as Template;
use Opscale\NovaDynamicResources\Nova\Actions\ViewRecord;
use Opscale\NovaDynamicResources\Services\Actions\RenderAction;
use Opscale\NovaDynamicResources\Services\Actions\RenderField;
use Override;

/**
 * @extends Resource<Model>
 *
 * @property-read Template $template
 */
class DynamicRecord extends Resource
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
        $singular_label = null;

        if (isset(static::$template)) {
            $singular_label = static::$template->getAttribute('singular_label');
        } else {
            $singular_label = __('Dynamic Record');
        }

        return $singular_label;
    }

    /**
     * Get the displayable label of the resource.
     */
    #[Override]
    public static function label(): string
    {
        /** @var string $label */
        $label = null;

        if (isset(static::$template)) {
            $label = static::$template->getAttribute('label');
        } else {
            $label = __('Dynamic Records');
        }

        return $label;
    }

    /**
     * Get the URI key for the resource.
     */
    #[Override]
    public static function uriKey(): string
    {
        /** @var string $uri_key */
        $uri_key = null;

        if (isset(static::$template)) {
            $uri_key = static::$template->getAttribute('uri_key');
        } else {
            $uri_key = __('dynamic-records');
        }

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
        if (isset(static::$template)) {
            return $query->where('resource_id', static::$template->id);
        } else {
            return $query;
        }
    }

    /**
     * Get the value that should be displayed to represent the resource.
     */
    final public function title(): string
    {
        /** @var string $label */
        $title = $this->model()->data[
            $this->model()->resource->title
        ];

        return $title;
    }

    /**
     * Get the fields displayed by the resource.
     */
    /**
     * @return array<mixed>
     */
    #[Override]
    public function fieldsForIndex(NovaRequest $request): array
    {
        if (static::uriKey() === __('dynamic-records')) {
            return [
                'resource' => BelongsTo::make(__('Resource'), 'resource', DynamicResource::class)
                    ->sortable()
                    ->filterable(),

                'title' => Text::make(__('Title'), function () {
                    return $this->title();
                }),

                'data' => KeyValue::make(__('Data'), 'data')
                    ->keyLabel(__('Field'))
                    ->valueLabel(__('Value')),

                'created_at' => DateTime::make(__('Created At'), 'created_at')
                    ->sortable()
                    ->filterable(),
            ];
        } else {
            return $this->fields($request);
        }
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
        if (! isset(static::$template)) {
            return [];
        }

        $resource = static::$template;
        $fields = [
            Hidden::make('Resource', 'resource_id')
                ->default($resource->id)
                ->rules('required'),
        ];

        $templateFields = $resource->fields;

        foreach ($templateFields as $templateField) {
            $result = RenderField::run([
                'type' => $templateField->type,
                'label' => $templateField->label,
                'name' => $templateField->name,
                'rules' => $templateField->rules ?? [],
                'config' => $templateField->config ?? [],
            ]);
            $fields[] = $result['instance'];
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
        // Add inline action when viewing all dynamic records
        if (static::uriKey() === __('dynamic-records')) {
            return [
                ViewRecord::make()
                    ->showOnIndex()
                    ->showInline()
                    ->withoutConfirmation(),
            ];
        }

        $resource = $this->model()->resource;
        if ($resource === null) {
            return [];
        }

        $actions = [];

        $templateActions = $resource->actions;
        foreach ($templateActions as $templateAction) {
            $result = RenderAction::run([
                'class' => $templateAction->class,
                'config' => $templateAction->config ?? [],
            ]);
            $actions[] = $result['instance'];
        }

        return $actions;
    }
}
