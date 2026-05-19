<?php

declare(strict_types=1);

use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Template;
use Opscale\NovaDynamicResources\Nova\Fields\TemplatedRepeater;
use Opscale\NovaDynamicResources\Nova\Repeatables\Record as RecordRepeatable;
use Workbench\App\Nova\Item;

beforeEach(function (): void {
    Template::query()->delete();
});

it('auto-populates repeatables with Composited templates matching the Nova resource', function (): void {
    Template::create([
        'label' => 'Books',
        'singular_label' => 'Book',
        'uri_key' => 'books',
        'title' => 'title',
        'type' => TemplateType::Composited,
        'related_class' => Item::class,
    ]);

    Template::create([
        'label' => 'Widgets',
        'singular_label' => 'Widget',
        'uri_key' => 'widgets',
        'title' => 'title',
        'type' => TemplateType::Composited,
        'related_class' => 'App\\Nova\\OtherResource',
    ]);

    $repeater = TemplatedRepeater::make('Items', 'items')->forResource(Item::class);

    expect($repeater->repeatables)->toHaveCount(1);

    /** @var RecordRepeatable $first */
    $first = $repeater->repeatables->first();
    expect($first)->toBeInstanceOf(RecordRepeatable::class);
    expect($first::singularLabel())->toBe('Book');
});

it('applies the optional consumer filter on top of the related_class match', function (): void {
    Template::create([
        'label' => 'Books',
        'singular_label' => 'Book',
        'uri_key' => 'books',
        'title' => 'title',
        'type' => TemplateType::Composited,
        'related_class' => Item::class,
    ]);

    Template::create([
        'label' => 'Magazines',
        'singular_label' => 'Magazine',
        'uri_key' => 'magazines',
        'title' => 'title',
        'type' => TemplateType::Composited,
        'related_class' => Item::class,
    ]);

    $repeater = TemplatedRepeater::make('Items', 'items')
        ->forResource(Item::class, fn ($query): mixed => $query->where('uri_key', 'books'));

    expect($repeater->repeatables)->toHaveCount(1);

    /** @var RecordRepeatable $first */
    $first = $repeater->repeatables->first();
    expect($first::singularLabel())->toBe('Book');
});

it('returns an empty repeatables collection when no template matches', function (): void {
    Template::create([
        'label' => 'Widgets',
        'singular_label' => 'Widget',
        'uri_key' => 'widgets',
        'title' => 'title',
        'type' => TemplateType::Composited,
        'related_class' => 'App\\Nova\\OtherResource',
    ]);

    $repeater = TemplatedRepeater::make('Items', 'items')->forResource(Item::class);

    expect($repeater->repeatables)->toHaveCount(0);
});
