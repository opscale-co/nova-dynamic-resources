<?php

declare(strict_types=1);

use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Field;
use Opscale\NovaDynamicResources\Models\Template;

beforeEach(function (): void {
    $this->template = Template::create([
        'label' => 'Items',
        'singular_label' => 'Item',
        'uri_key' => 'items',
        'title' => 'name',
        'type' => TemplateType::Dynamic,
    ]);
});

it('persists a field with required attributes', function (): void {
    $field = Field::create([
        'template_id' => $this->template->id,
        'type' => 'name',
        'label' => 'Title',
        'name' => 'title',
        'required' => true,
        'display_in_index' => true,
    ]);

    expect($field->id)->toBeString()
        ->and($field->label)->toBe('Title')
        ->and($field->name)->toBe('title')
        ->and($field->type)->toBe('name')
        ->and($field->required)->toBeTrue();
});

it('auto-populates name from label when not set', function (): void {
    $field = Field::create([
        'template_id' => $this->template->id,
        'type' => 'description',
        'label' => 'Long Description',
        'required' => false,
        'display_in_index' => true,
    ]);

    expect($field->name)->toBe('long_description');
});

it('belongs to a template', function (): void {
    $field = Field::create([
        'template_id' => $this->template->id,
        'type' => 'name',
        'label' => 'Title',
        'name' => 'title',
        'required' => true,
        'display_in_index' => true,
    ]);

    expect($field->template)->toBeInstanceOf(Template::class)
        ->and($field->template->id)->toBe($this->template->id);
});

it('casts rules and config to arrays when read from the database', function (): void {
    $field = Field::create([
        'template_id' => $this->template->id,
        'type' => 'name',
        'label' => 'Title',
        'name' => 'title',
        'required' => true,
        'display_in_index' => true,
    ]);

    $field->update([
        'rules' => ['min:3', 'max:100'],
        'config' => ['placeholder' => 'Enter title'],
    ]);

    $reloaded = Field::find($field->id);

    expect($reloaded->rules)->toBe(['min:3', 'max:100'])
        ->and($reloaded->config)->toBe(['placeholder' => 'Enter title']);
});
