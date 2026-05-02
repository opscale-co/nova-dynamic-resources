<?php

declare(strict_types=1);

use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Record;
use Opscale\NovaDynamicResources\Models\Template;
use Workbench\App\Models\Item;
use Workbench\App\Models\User;

it('creates a Dynamic template that owns its Records and resolves dynamic data', function (): void {
    $template = Template::create([
        'label' => 'Events',
        'singular_label' => 'Event',
        'uri_key' => 'events-dyn',
        'title' => 'name',
        'type' => TemplateType::Dynamic,
    ]);

    $template->fields()->createMany([
        [
            'type' => 'name',
            'label' => 'Name',
            'name' => 'name',
            'required' => true,
            'display_in_index' => true,
        ],
        [
            'type' => 'date',
            'label' => 'Date',
            'name' => 'date',
            'required' => true,
            'display_in_index' => true,
        ],
    ]);

    $record = Record::create([
        'template_id' => $template->id,
        'data' => [
            'name' => 'Launch Day',
            'date' => '2026-12-01',
        ],
    ]);

    $reloaded = Record::findOrFail($record->id);
    $date = $reloaded->getData('date');

    expect($template->type)->toBe(TemplateType::Dynamic);
    expect($template->related_class)->toBeNull();
    expect($reloaded->template->id)->toBe($template->id);
    expect($reloaded->getData('name'))->toBe('Launch Day');
    expect($date)->toBeInstanceOf(DateTimeInterface::class);

    if ($date instanceof DateTimeInterface) {
        expect($date->format('Y-m-d'))->toBe('2026-12-01');
    }

    expect($reloaded->getData('name'))->toBe('Launch Day');
});

it('creates an Inherited template linked by template_id to a host model', function (): void {
    $template = Template::create([
        'label' => 'Products',
        'singular_label' => 'Product',
        'uri_key' => 'products-inh',
        'title' => 'name',
        'type' => TemplateType::Inherited,
        'related_class' => \Workbench\App\Nova\Item::class,
    ]);

    $template->fields()->create([
        'type' => 'quantity',
        'label' => 'Weight',
        'name' => 'weight',
        'required' => false,
        'display_in_index' => false,
    ]);

    $item = Item::create([
        'template_id' => $template->id,
        'name' => 'Widget',
        'description' => 'A widget',
        'price' => 9.99,
        'stock' => 5,
        'data' => ['weight' => 3],
    ]);

    $reloaded = Item::findOrFail($item->id);
    $data = $reloaded->data ?? [];

    expect($template->type)->toBe(TemplateType::Inherited);
    expect($template->related_class)->toBe(\Workbench\App\Nova\Item::class);
    expect($reloaded->template)->not->toBeNull();
    expect($reloaded->template->id)->toBe($template->id);
    expect($reloaded->name)->toBe('Widget');
    expect($data)->toHaveKey('weight');
    expect($data['weight'])->toBe(3);
});

it('creates a Composited template that resolves on existing host model via class_name', function (): void {
    $template = Template::create([
        'label' => 'Users',
        'singular_label' => 'User',
        'uri_key' => 'users-comp',
        'title' => 'name',
        'type' => TemplateType::Composited,
        'related_class' => \Workbench\App\Nova\User::class,
    ]);

    $template->fields()->create([
        'type' => 'phone',
        'label' => 'Phone',
        'name' => 'phone',
        'required' => false,
        'display_in_index' => true,
    ]);

    $user = User::factory()->create([
        'name' => 'Composited Tester',
        'email' => 'composited@example.com',
    ]);

    $user->forceFill(['data' => ['phone' => '+1-555-0199']])->save();

    expect($template->type)->toBe(TemplateType::Composited)
        ->and($template->related_class)->toBe(\Workbench\App\Nova\User::class)
        ->and($user->fresh()->data)->toBe(['phone' => '+1-555-0199']);
});

it('excludes Composited templates from instantiables scope', function (): void {
    Template::create([
        'label' => 'Dyn',
        'singular_label' => 'Dyn',
        'uri_key' => 'dyn-only',
        'title' => 'name',
        'type' => TemplateType::Dynamic,
    ]);
    Template::create([
        'label' => 'Inh',
        'singular_label' => 'Inh',
        'uri_key' => 'inh-only',
        'title' => 'name',
        'type' => TemplateType::Inherited,
        'related_class' => \Workbench\App\Nova\Item::class,
    ]);
    Template::create([
        'label' => 'Comp',
        'singular_label' => 'Comp',
        'uri_key' => 'comp-only',
        'title' => 'name',
        'type' => TemplateType::Composited,
        'related_class' => \Workbench\App\Nova\User::class,
    ]);

    $types = Template::instantiables()->get()->pluck('type')->all();

    expect($types)->toContain(TemplateType::Dynamic, TemplateType::Inherited)
        ->not->toContain(TemplateType::Composited);
});
