<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\Relation;
use Opscale\NovaDynamicResources\Models\Enums\RelationshipCardinality;
use Opscale\NovaDynamicResources\Models\Relationship;
use Opscale\NovaDynamicResources\Models\Repositories\RelationshipResolver;
use Opscale\NovaDynamicResources\Models\Template;
use Opscale\NovaDynamicResources\Services\Actions\BuildDynamicRelation;
use Override;

/**
 * Resolves dynamic relationship method calls based on the model's Template.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasDynamicRelationships
{
    /**
     * @var array<string, array{relationship: Relationship, inverse: bool}|null>
     */
    protected array $dynamicRelationshipCache = [];

    /**
     * @param  string  $key
     */
    #[Override]
    public function isRelation($key): bool
    {
        if (parent::isRelation($key)) {
            return true;
        }

        return $this->resolveDynamicRelationship($key) !== null;
    }

    /**
     * @return array{relationship: Relationship, inverse: bool}|null
     */
    final protected function resolveDynamicRelationship(string $name): ?array
    {
        if (array_key_exists($name, $this->dynamicRelationshipCache)) {
            return $this->dynamicRelationshipCache[$name];
        }

        $template = $this->getCurrentTemplate();

        if ($template === null) {
            return $this->dynamicRelationshipCache[$name] = null;
        }

        $forward = $template->relationships->firstWhere('name', $name);

        if ($forward instanceof Relationship) {
            return $this->dynamicRelationshipCache[$name] = [
                'relationship' => $forward,
                'inverse' => false,
            ];
        }

        $inverse = RelationshipResolver::findInverse($template->id, $name);

        if ($inverse instanceof Relationship) {
            return $this->dynamicRelationshipCache[$name] = [
                'relationship' => $inverse,
                'inverse' => true,
            ];
        }

        return $this->dynamicRelationshipCache[$name] = null;
    }

    /**
     * @return Relation<\Illuminate\Database\Eloquent\Model, \Illuminate\Database\Eloquent\Model, mixed>
     */
    final protected function buildDynamicRelation(Relationship $relationship, bool $inverse): Relation
    {
        $cardinality = $this->effectiveCardinality($relationship, $inverse);
        $targetTemplate = $inverse ? $relationship->template : $relationship->relatedTemplate;

        /** @var array{success: bool, instance: Relation<\Illuminate\Database\Eloquent\Model, \Illuminate\Database\Eloquent\Model, mixed>} $result */
        $result = BuildDynamicRelation::run([
            'parent' => $this,
            'target_template' => $targetTemplate,
            'cardinality' => $cardinality,
            'foreign_key' => $relationship->foreign_key,
        ]);

        return $result['instance'];
    }

    final protected function effectiveCardinality(Relationship $relationship, bool $inverse): RelationshipCardinality
    {
        if (! $inverse) {
            return $relationship->cardinality;
        }

        return match ($relationship->cardinality) {
            RelationshipCardinality::BelongsTo => RelationshipCardinality::HasMany,
            RelationshipCardinality::HasMany => RelationshipCardinality::BelongsTo,
            RelationshipCardinality::HasOne => RelationshipCardinality::HasOne,
        };
    }

    final protected function getCurrentTemplate(): ?Template
    {
        if ($this->relationLoaded('template')) {
            $template = $this->getRelation('template');

            return $template instanceof Template ? $template : null;
        }

        $attributes = $this->getAttributes();
        $templateId = $attributes['template_id'] ?? null;

        if (is_string($templateId) && $templateId !== '') {
            return Template::findById($templateId);
        }

        return null;
    }

    /**
     * @param  string  $method
     * @param  array<int, mixed>  $parameters
     */
    #[Override]
    public function __call($method, $parameters)
    {
        $resolved = $this->resolveDynamicRelationship($method);

        if ($resolved !== null) {
            return $this->buildDynamicRelation($resolved['relationship'], $resolved['inverse']);
        }

        return parent::__call($method, $parameters);
    }
}
