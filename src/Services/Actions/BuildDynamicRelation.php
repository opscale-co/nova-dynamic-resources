<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Services\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;
use Opscale\Actions\Action;
use Opscale\NovaDynamicResources\Eloquent\Relations\JsonBelongsTo;
use Opscale\NovaDynamicResources\Eloquent\Relations\JsonHasMany;
use Opscale\NovaDynamicResources\Eloquent\Relations\JsonHasOne;
use Opscale\NovaDynamicResources\Models\Enums\RelationshipCardinality;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Record;
use Opscale\NovaDynamicResources\Models\Template;
use Override;

class BuildDynamicRelation extends Action
{
    #[Override]
    public function identifier(): string
    {
        return 'build-dynamic-relation';
    }

    #[Override]
    public function name(): string
    {
        return __('Build Dynamic Relation');
    }

    #[Override]
    public function description(): string
    {
        return __('Build an Eloquent relation instance from a relationship definition.');
    }

    /**
     * Inputs are runtime objects (model, template, enum) supplied by the
     * caller, not user-facing values. They are declared with passthrough
     * rules so the SIPOC pipeline keeps them in the validated bag instead of
     * stripping undeclared keys before handle(); handle() enforces its own
     * type guards.
     *
     * @return array<int, array{name: string, description: string, type: string, rules: array<int, string>}>
     */
    #[Override]
    public function parameters(): array
    {
        return [
            [
                'name' => 'parent',
                'description' => 'Parent Eloquent model',
                'type' => 'object',
                'rules' => [],
            ],
            [
                'name' => 'target_template',
                'description' => 'Target template (nullable)',
                'type' => 'object',
                'rules' => [],
            ],
            [
                'name' => 'cardinality',
                'description' => 'Relationship cardinality enum',
                'type' => 'object',
                'rules' => [],
            ],
            [
                'name' => 'foreign_key',
                'description' => 'Foreign key column',
                'type' => 'string',
                'rules' => [],
            ],
        ];
    }

    /**
     * @param  array{parent?: Model, target_template?: ?Template, cardinality?: RelationshipCardinality, foreign_key?: string}  $attributes
     * @return array{success: bool, instance: Relation<Model, Model, mixed>}
     */
    #[Override]
    public function handle(array $attributes = []): array
    {
        $parent = $attributes['parent'] ?? null;
        $targetTemplate = $attributes['target_template'] ?? null;
        $cardinality = $attributes['cardinality'] ?? null;
        $foreignKey = $attributes['foreign_key'] ?? null;

        if (! $parent instanceof Model) {
            throw new InvalidArgumentException('parent must be an Eloquent model');
        }

        if (! $cardinality instanceof RelationshipCardinality) {
            throw new InvalidArgumentException('cardinality must be a RelationshipCardinality');
        }

        if (! is_string($foreignKey) || $foreignKey === '') {
            throw new InvalidArgumentException('foreign_key must be a non-empty string');
        }

        $relatedClass = $this->resolveTargetClass($targetTemplate);
        /** @var Model $relatedInstance */
        $relatedInstance = new $relatedClass;
        $query = $relatedInstance->newQuery();

        if ($targetTemplate?->type === TemplateType::Dynamic) {
            $query->where('template_id', $targetTemplate->id);
        }

        $instance = match ($cardinality) {
            RelationshipCardinality::BelongsTo => new JsonBelongsTo($query, $parent, $foreignKey),
            RelationshipCardinality::HasOne => new JsonHasOne($query, $parent, $foreignKey),
            RelationshipCardinality::HasMany => new JsonHasMany($query, $parent, $foreignKey),
        };

        return [
            'success' => true,
            'instance' => $instance,
        ];
    }

    /**
     * @return array<int, array{name: string, type: string, rules: array<int, string>}>
     */
    #[Override]
    public function outputs(): array
    {
        return [];
    }

    /**
     * @return class-string<Model>
     */
    final protected function resolveTargetClass(?Template $template): string
    {
        if ($template === null) {
            return Record::class;
        }

        if ($template->type === TemplateType::Dynamic) {
            return Record::class;
        }

        $related = $template->related_class;

        if (! is_string($related) || ! class_exists($related)) {
            return Record::class;
        }

        $instance = new $related;

        if (! $instance instanceof Model) {
            return Record::class;
        }

        /** @var class-string<Model> $related */
        return $related;
    }
}
