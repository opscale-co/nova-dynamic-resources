<?php

namespace Opscale\NovaDynamicResources\Nova\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NovaDynamicResources\Services\Actions\RenderAction;
use Opscale\NovaDynamicResources\Services\Actions\RenderField;

/**
 * @mixin \Laravel\Nova\Resource
 */
trait UsesTemplate
{
    /**
     * Get the displayable singular label of the resource.
     */
    #[Override]
    public static function singularLabel(): string
    {
        if (isset(static::$template)) {
            return static::$template->getAttribute('singular_label');
        } else {
            return parent::singularLabel();
        }
    }

    /**
     * Get the displayable label of the resource.
     */
    #[Override]
    public static function label(): string
    {
        if (isset(static::$template)) {
            return static::$template->getAttribute('label');
        } else {
            return parent::label();
        }
    }

    /**
     * Get the URI key for the resource.
     */
    #[Override]
    public static function uriKey(): string
    {
        if (isset(static::$template)) {
            return static::$template->getAttribute('uri_key');
        } else {
            return parent::uriKey();
        }
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
            return $query->whereHas('template', function (Builder $q) {
                $q->where('uri_key', static::uriKey());
            });
        }

        return $query;
    }

    /**
     * Render dynamic fields from the model's template.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    protected function renderTemplateFields(): array
    {
        $fields = [];
        $templateFields = [];

        if (isset(static::$template)) {
            // Relation for inheritance scenarios
            $templateFields = static::$template->fields;
            $fields[] = Hidden::make('Template', 'template_id')
                ->default(static::$template->id)
                ->onlyOnForms();
        } else {
            // Relation for composition scenarios
            $templateFields = $this->resource->template?->fields ?? [];
        }

        foreach ($templateFields as $templateField) {
            if (isset(static::$template) && static::$template->hasData($templateField->name)) {
                $fields[] = Hidden::make($templateField->label, 'data->' . $templateField->name)
                    ->default(static::$template->getData($templateField->name));

                continue;
            }

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

        return $fields;
    }

    /**
     * Render dynamic actions from the model's template.
     *
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    protected function renderTemplateActions(): array
    {
        $actions = [];

        if (isset(static::$template)) {
            $templateActions = static::$template->actions;
        } else {
            $templateActions = $this->resource->template?->actions ?? [];
        }

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
