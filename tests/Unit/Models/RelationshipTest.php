<?php

declare(strict_types=1);

use Illuminate\Validation\ValidationException;
use Opscale\NovaDynamicResources\Models\Enums\RelationshipCardinality;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Relationship;
use Opscale\NovaDynamicResources\Models\Template;

beforeEach(function (): void {
    $this->source = Template::create([
        'label' => 'Sources',
        'singular_label' => 'Source',
        'uri_key' => 'sources',
        'title' => 'name',
        'type' => TemplateType::Dynamic,
    ]);

    $this->target = Template::create([
        'label' => 'Targets',
        'singular_label' => 'Target',
        'uri_key' => 'targets',
        'title' => 'name',
        'type' => TemplateType::Dynamic,
    ]);
});

it('persists a relationship with required attributes', function (): void {
    $rel = $this->source->relationships()->create([
        'name' => 'target',
        'label' => 'Target',
        'cardinality' => RelationshipCardinality::BelongsTo,
        'related_template_id' => $this->target->id,
        'foreign_key' => 'target_id',
        'required' => false,
    ]);

    $reloaded = Relationship::findOrFail($rel->id);

    expect($reloaded->cardinality)->toBe(RelationshipCardinality::BelongsTo);
    expect($reloaded->template_id)->toBe($this->source->id);
    expect($reloaded->relatedTemplate->id)->toBe($this->target->id);
});

it('auto-derives name and foreign_key when blank', function (): void {
    $rel = $this->source->relationships()->create([
        'label' => 'Owner',
        'cardinality' => RelationshipCardinality::BelongsTo,
        'related_template_id' => $this->target->id,
        'required' => false,
    ]);

    expect($rel->name)->toBe('owner');
    expect($rel->foreign_key)->toBe('owner_id');
});

it('rejects relationships missing related_template_id', function (): void {
    $rel = $this->source->relationships()->make([
        'name' => 'broken',
        'label' => 'Broken',
        'cardinality' => RelationshipCardinality::BelongsTo,
        'foreign_key' => 'broken_id',
        'required' => false,
    ]);

    $rel->validate();
})->throws(ValidationException::class);

it('rejects relationships with invalid foreign_key syntax', function (): void {
    $rel = $this->source->relationships()->make([
        'name' => 'bad',
        'label' => 'Bad',
        'cardinality' => RelationshipCardinality::BelongsTo,
        'related_template_id' => $this->target->id,
        'foreign_key' => 'Bad-Key',
        'required' => false,
    ]);

    $rel->validate();
})->throws(ValidationException::class);

it('allows self-referential relationships', function (): void {
    $rel = $this->source->relationships()->create([
        'name' => 'parent',
        'label' => 'Parent',
        'cardinality' => RelationshipCardinality::BelongsTo,
        'related_template_id' => $this->source->id,
        'foreign_key' => 'parent_id',
        'inverse_name' => 'children',
        'required' => false,
    ]);

    expect($rel->template_id)->toBe($rel->related_template_id);
    expect($rel->inverse_name)->toBe('children');
});
