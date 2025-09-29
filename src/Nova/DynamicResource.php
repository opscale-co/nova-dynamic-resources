<?php

namespace Opscale\NovaDynamicResources\Nova;

use Illuminate\Support\Str;
use Laravel\Nova\Fields\Repeater;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaDynamicResources\Models\DynamicResource as Model;
use Opscale\NovaDynamicResources\Nova\Repeatables\Action;
use Opscale\NovaDynamicResources\Nova\Repeatables\Field;

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
    public static $title = 'label';

    /**
     * The columns that should be searched.
     *
     * @var array<int, string>
     */
    public static $search = [
        'label', 'singular_label', 'uri_key',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Dynamic Resources';

    /**
     * Get the URI key for the resource.
     */
    final public static function uriKey(): string
    {
        return 'dynamic-resources';
    }

    /**
     * Get the displayable label of the resource.
     */
    final public static function label(): string
    {
        return 'Dynamic Resources';
    }

    /**
     * Get the displayable singular label of the resource.
     */
    final public static function singularLabel(): string
    {
        return 'Dynamic Resource';
    }

    /**
     * Get the fields displayed by the resource.
     */
    /**
     * @return array<mixed>
     */
    final public function fields(NovaRequest $request): array
    {
        return [
            Text::make(_('Label'), 'label')
                ->rules(fn (): array => $this->model()?->validationRules()['label'] ?? ['required', 'string']),

            Text::make(_('Singular Label'), 'singular_label')
                ->dependsOn(['label'],
                    function (Text $text, NovaRequest $novaRequest, $formData): void {
                        if (! empty($formData['label']) &&
                            is_string($formData['label'])) {
                            $text->value = Str::singular($formData['label']);
                        }
                    })
                ->rules(fn (): array => $this->model()?->validationRules()['singular_label'] ?? ['required', 'string']),

            Slug::make(_('URI Key'), 'uri_key')
                ->from('label')
                ->rules(fn (): array => $this->model()?->validationRules()['uri_key'] ?? ['required', 'string']),

            Repeater::make(_('Fields'), 'fields')
                ->repeatables([
                    Field::make(),
                ])
                ->asJson(),

            Repeater::make(_('Actions'), 'actions')
                ->repeatables([
                    Action::make(),
                ])
                ->asJson(),
        ];
    }
}
