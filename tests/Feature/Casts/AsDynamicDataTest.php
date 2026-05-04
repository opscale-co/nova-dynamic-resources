<?php

declare(strict_types=1);

use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Record;
use Opscale\NovaDynamicResources\Models\Template;

it('decodes JSON dynamic data on retrieval', function (): void {
    $template = Template::create([
        'label' => 'Articles',
        'singular_label' => 'Article',
        'uri_key' => 'articles',
        'title' => 'title',
        'type' => TemplateType::Dynamic,
    ]);

    $template->fields()->create([
        'type' => 'name',
        'label' => 'Title',
        'name' => 'title',
        'required' => true,
        'display_in_index' => true,
    ]);

    $record = Record::create([
        'template_id' => $template->id,
        'data' => ['title' => 'Hello world'],
    ]);

    $fresh = Record::find($record->id);

    expect($fresh)->not->toBeNull()
        ->and($fresh->data)->toBeArray()
        ->and($fresh->data['title'])->toBe('Hello world');
});

it('encodes data as JSON when persisting', function (): void {
    $template = Template::create([
        'label' => 'Notes',
        'singular_label' => 'Note',
        'uri_key' => 'notes',
        'title' => 'subject',
        'type' => TemplateType::Dynamic,
    ]);

    $record = Record::create([
        'template_id' => $template->id,
        'data' => ['subject' => 'Reminder', 'priority' => 'high'],
    ]);

    $raw = $record->getRawOriginal('data');

    expect($raw)->toBeString()
        ->and(json_decode($raw, true))->toBe(['subject' => 'Reminder', 'priority' => 'high']);
});
