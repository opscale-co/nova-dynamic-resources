<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Nova;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaDynamicResources\Models\Record as Model;
use Opscale\NovaDynamicResources\Models\Template as TemplateModel;
use Opscale\NovaDynamicResources\Services\Actions\RenderAction;
use Opscale\NovaDynamicResources\Services\Actions\RenderField;
use Opscale\NovaDynamicResources\Services\Actions\RenderRelationship;
use Opscale\NovaDynamicResources\Services\Actions\ViewRecord;
use Override;
use Throwable;

/**
 * @extends Resource<Model>
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
        if (isset(static::$template)) {
            return static::$template->singular_label;
        }

        return __('Record');
    }

    /**
     * Get the displayable label of the resource.
     */
    #[Override]
    public static function label(): string
    {
        if (isset(static::$template)) {
            return static::$template->label;
        }

        return __('Records');
    }

    /**
     * Get the URI key for the resource.
     */
    #[Override]
    public static function uriKey(): string
    {
        if (isset(static::$template)) {
            return static::$template->uri_key;
        }

        return __('records');
    }

    /**
     * Pre-fill the model with the current template context so dynamic
     * relationships resolve on freshly instantiated (create-form) records.
     */
    #[Override]
    public static function newModel()
    {
        /** @var Model $model */
        $model = parent::newModel();

        if (isset(static::$template)) {
            if (in_array('template_id', $model->getFillable(), true)) {
                $model->setAttribute('template_id', static::$template->id);
            }
            $model->setRelation('template', static::$template);
        }

        return $model;
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
        }

        return $query;
    }

    /**
     * Get the value that should be displayed to represent the resource.
     */
    #[Override]
    public function title(): string
    {
        $model = $this->model();

        if ($model === null) {
            return (string) parent::title();
        }

        $template = $model->template;

        if ($template === null || $template->title === null) {
            return $model->id;
        }

        $title = $model->data[$template->title] ?? $model->id;

        return is_string($title) ? $title : (string) $model->id;
    }

    /**
     * Get the fields displayed by the resource for the index.
     *
     * @return array<int, Field>
     */
    final public function fieldsForIndex(NovaRequest $request): array
    {
        if (! isset(static::$template)) {
            return [
                BelongsTo::make(__('Template'), 'template', Template::class)
                    ->sortable()
                    ->filterable(),

                Text::make(__('Title'), fn (): string => $this->title()),

                KeyValue::make(__('Data'), 'data')
                    ->keyLabel(__('Field'))
                    ->valueLabel(__('Value')),

                DateTime::make(__('Created At'), 'created_at')
                    ->sortable()
                    ->filterable(),
            ];
        }

        return $this->fields($request);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, Field>
     */
    #[Override]
    public function fields(NovaRequest $request): array
    {
        if (! isset(static::$template)) {
            return [];
        }

        $template = static::$template;
        /** @var array<int, Field> $fields */
        $fields = [
            Hidden::make('Template', 'template_id')
                ->default($template->id)
                ->rules('required'),
        ];

        foreach ($template->fields as $templateField) {
            /** @var array{success: bool, instance: Field} $result */
            $result = RenderField::run([
                'type' => $templateField->type,
                'label' => $templateField->label,
                'name' => $templateField->name,
                'required' => $templateField->required,
                'display_in_index' => $templateField->display_in_index,
                'rules' => $templateField->rules ?? [],
                'config' => $templateField->config ?? [],
            ]);
            $fields[] = $result['instance'];
        }

        foreach ($template->relationships as $templateRelationship) {
            $relatedTemplate = $templateRelationship->relatedTemplate;

            if ($relatedTemplate === null) {
                continue;
            }

            try {
                /** @var array{success: bool, instance: Field} $relationResult */
                $relationResult = RenderRelationship::run([
                    'cardinality' => $templateRelationship->cardinality->value,
                    'name' => $templateRelationship->name,
                    'label' => $templateRelationship->label,
                    'related_uri_key' => (string) $relatedTemplate->uri_key,
                    'rules' => $templateRelationship->rules ?? [],
                    'config' => $templateRelationship->config ?? [],
                ]);
                $fields[] = $relationResult['instance'];
            } catch (Throwable) {
                continue;
            }
        }

        return $fields;
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    #[Override]
    public function actions(NovaRequest $request): array
    {
        $defaults = parent::actions($request);

        // Add inline action when viewing all records
        if (! isset(static::$template)) {
            return [
                ...$defaults,
                ViewRecord::make(),
            ];
        }

        $model = $this->model();
        $template = $model?->template;

        if ($template === null) {
            return $defaults;
        }

        /** @var array<int, \Laravel\Nova\Actions\Action> $actions */
        $actions = $defaults;

        foreach ($template->actions as $templateAction) {
            /** @var array{success: bool, instance: \Laravel\Nova\Actions\Action} $result */
            $result = RenderAction::run([
                'class' => $templateAction->class,
                'config' => $templateAction->config ?? [],
            ]);
            $actions[] = $result['instance'];
        }

        return $actions;
    }
}
