<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Nova\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\Actions\Decorators\NovaActionDecorator;
use Opscale\NovaDynamicResources\Models\Template;
use Opscale\NovaDynamicResources\Services\Actions\RenderAction;
use Opscale\NovaDynamicResources\Services\Actions\RenderField;
use Opscale\NovaDynamicResources\Services\Actions\RenderRelationship;
use Throwable;

/**
 * @mixin \Laravel\Nova\Resource<\Illuminate\Database\Eloquent\Model>
 */
trait UsesTemplate
{
    /**
     * Get the displayable singular label of the resource.
     */
    final public static function singularLabel(): string
    {
        if (isset(static::$template)) {
            return (string) static::$template->getAttribute('singular_label');
        }

        return parent::singularLabel();
    }

    /**
     * Get the displayable label of the resource.
     */
    final public static function label(): string
    {
        if (isset(static::$template)) {
            return (string) static::$template->getAttribute('label');
        }

        return parent::label();
    }

    /**
     * Get the URI key for the resource.
     */
    final public static function uriKey(): string
    {
        if (isset(static::$template)) {
            return (string) static::$template->getAttribute('uri_key');
        }

        return parent::uriKey();
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    final public static function indexQuery(NovaRequest $request, $query)
    {
        if (isset(static::$template)) {
            return $query->whereHas('template', function (Builder $relation): void {
                $relation->where('uri_key', static::uriKey());
            });
        }

        return $query;
    }

    /**
     * Render dynamic fields from the model's template.
     *
     * @return array<int, Field>
     */
    final protected function renderTemplateFields(): array
    {
        $fields = [];
        /** @var iterable<int, \Opscale\NovaDynamicResources\Models\Field> $templateFields */
        $templateFields = [];

        if (isset(static::$template)) {
            $templateFields = static::$template->fields;
            $fields[] = Hidden::make('Template', 'template_id')
                ->default(static::$template->id)
                ->onlyOnForms();
        } else {
            /** @var Template|null $template */
            $template = $this->resource?->getAttribute('template');
            $templateFields = $template?->fields ?? [];
        }

        foreach ($templateFields as $templateField) {
            if (isset(static::$template) && static::$template->hasData($templateField->name)) {
                $fields[] = Hidden::make($templateField->label, 'data->'.$templateField->name)
                    ->default(static::$template->getData($templateField->name));

                continue;
            }

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

        return $fields;
    }

    /**
     * Render dynamic actions from the model's template.
     *
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    final protected function renderTemplateActions(): array
    {
        $actions = [];

        if (isset(static::$template)) {
            $templateActions = static::$template->actions;
        } else {
            /** @var Template|null $template */
            $template = $this->resource?->getAttribute('template');
            $templateActions = $template?->actions ?? [];
        }

        foreach ($templateActions as $templateAction) {
            /** @var array{success: bool, instance: object} $result */
            $result = RenderAction::run([
                'class' => $templateAction->class,
                'config' => $templateAction->config ?? [],
            ]);

            $actions[] = App::make(NovaActionDecorator::class, [
                'action' => $result['instance'],
            ]);
        }

        return $actions;
    }

    /**
     * Render dynamic relationships from the model's template.
     *
     * @return array<int, Field>
     */
    final protected function renderTemplateRelationships(): array
    {
        $fields = [];

        if (isset(static::$template)) {
            $templateRelationships = static::$template->relationships;
        } else {
            /** @var Template|null $template */
            $template = $this->resource?->getAttribute('template');
            $templateRelationships = $template?->relationships ?? [];
        }

        foreach ($templateRelationships as $templateRelationship) {
            $relatedTemplate = $templateRelationship->relatedTemplate;

            if ($relatedTemplate === null) {
                continue;
            }

            try {
                /** @var array{success: bool, instance: Field} $result */
                $result = RenderRelationship::run([
                    'cardinality' => $templateRelationship->cardinality->value,
                    'name' => $templateRelationship->name,
                    'label' => $templateRelationship->label,
                    'related_uri_key' => (string) $relatedTemplate->uri_key,
                    'rules' => $templateRelationship->rules ?? [],
                    'config' => $templateRelationship->config ?? [],
                ]);

                $fields[] = $result['instance'];
            } catch (Throwable) {
                // Skip relationships whose related Resource binding is not yet registered.
                continue;
            }
        }

        return $fields;
    }
}
