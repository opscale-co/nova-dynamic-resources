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
use Opscale\NovaDynamicResources\Models\Record as Model;
use Opscale\NovaDynamicResources\Models\Template as TemplateModel;
use Opscale\NovaDynamicResources\Services\Actions\RenderAction;
use Opscale\NovaDynamicResources\Services\Actions\RenderField;
use Opscale\NovaDynamicResources\Services\Actions\ViewRecord;
use Override;

/**
 * @extends Resource<Model>
 *
 * @property-read TemplateModel $template
 */
class Record extends Resource
{
    public static TemplateModel $template;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Opscale\NovaDynamicResources\Models\Record>
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
            $singular_label = __('Record');
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
            $label = __('Records');
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
            $uri_key = __('records');
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
            return $query->where('template_id', static::$template->id);
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
        if (isset($this->model()->template->title)) {
            $title = $this->model()->data[
                $this->model()->template->title
            ];
        } else {
            $title = $this->model()->id;
        }

        return $title;
    }

    /**
     * Get the fields displayed by the resource.
     */
    /**
     * @return array<mixed>
     */
    public function fieldsForIndex(NovaRequest $request): array
    {
        if (! isset(static::$template)) {
            return [
                'template' => BelongsTo::make(__('Template'), 'template', Template::class)
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

        $template = static::$template;
        $fields = [
            Hidden::make('Template', 'template_id')
                ->default($template->id)
                ->rules('required'),
        ];

        $templateFields = $template->fields;

        foreach ($templateFields as $templateField) {
            $result = RenderField::run([
                'type' => $templateField->type,
                'label' => $templateField->label,
                'name' => $templateField->name,
                'required' => $templateField->required,
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
        // Add inline action when viewing all records
        if (! isset(static::$template)) {
            return [
                ViewRecord::make()
                    ->showOnIndex()
                    ->showInline()
                    ->withoutConfirmation(),
            ];
        }

        $template = $this->model()->template;
        if ($template === null) {
            return [];
        }

        $actions = [];

        $templateActions = $template->actions;
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
