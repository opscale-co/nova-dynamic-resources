<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaDynamicResources\Models\Enums\RelationshipCardinality;
use Opscale\NovaDynamicResources\Models\Relationship as Model;
use Override;

/**
 * @extends Resource<Model>
 */
class Relationship extends Resource
{
    /**
     * @var class-string<Model>
     */
    public static $model = Model::class;

    /**
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * @var string
     */
    public static $title = 'label';

    /**
     * @var array<int, string>
     */
    public static $search = [
        'label',
        'name',
    ];

    #[Override]
    public static function uriKey(): string
    {
        return __('relationships');
    }

    #[Override]
    public static function label(): string
    {
        return __('Relationships');
    }

    #[Override]
    public static function singularLabel(): string
    {
        return __('Relationship');
    }

    /**
     * @return array<string, \Laravel\Nova\Fields\Field>
     */
    final public static function defaultFields(): array
    {
        return [
            'label' => Text::make(__('Label'), 'label')
                ->rules(Model::$validationRules['label']),

            'name' => Slug::make(__('Name'), 'name')
                ->from('label')
                ->separator('_')
                ->creationRules('nullable')
                ->updateRules(Model::$validationRules['name'])
                ->hideFromIndex(),

            'cardinality' => Select::make(__('Cardinality'), 'cardinality')
                ->options(static::cardinalityOptions())
                ->displayUsingLabels()
                ->rules(Model::$validationRules['cardinality']),

            'related_template' => BelongsTo::make(__('Related Template'), 'relatedTemplate', Template::class)
                ->rules(Model::$validationRules['related_template_id']),

            'foreign_key' => Text::make(__('Foreign Key'), 'foreign_key')
                ->rules(Model::$validationRules['foreign_key']),

            'inverse_name' => Text::make(__('Inverse Name'), 'inverse_name')
                ->nullable()
                ->rules(Model::$validationRules['inverse_name']),

            'required' => Boolean::make(__('Required'), 'required')
                ->rules(Model::$validationRules['required']),

            'rules' => KeyValue::make(__('Validation Rules'), 'rules')
                ->keyLabel(__('Rule'))
                ->valueLabel(__('Value'))
                ->nullable()
                ->hideWhenCreating(),

            'config' => KeyValue::make(__('Config'), 'config')
                ->keyLabel(__('Key'))
                ->valueLabel(__('Value'))
                ->nullable()
                ->hideWhenCreating(),
        ];
    }

    /**
     * @return array<string, string>
     */
    final protected static function cardinalityOptions(): array
    {
        $options = [];

        foreach (RelationshipCardinality::cases() as $case) {
            $options[$case->value] = $case->value;
        }

        return $options;
    }

    /**
     * @return array<mixed>
     */
    #[Override]
    public function fields(NovaRequest $request): array
    {
        return [
            BelongsTo::make(__('Template'), 'template', Template::class)
                ->sortable()
                ->filterable(),

            ...static::defaultFields(),
        ];
    }
}
