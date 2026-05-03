<?php

declare(strict_types=1);

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Opscale\NovaDynamicResources\Services\Actions\RenderRelationship;

beforeEach(function (): void {
    app()->bind('dynamic-targets', fn () => new \Workbench\App\Nova\Item);
});

it('renders a BelongsTo Nova field for the BelongsTo cardinality', function (): void {
    $result = RenderRelationship::run([
        'cardinality' => 'BelongsTo',
        'name' => 'target',
        'label' => 'Target',
        'related_uri_key' => 'targets',
        'rules' => [],
        'config' => [],
    ]);

    expect($result['instance'])->toBeInstanceOf(BelongsTo::class);
});

it('renders a HasOne Nova field for the HasOne cardinality', function (): void {
    $result = RenderRelationship::run([
        'cardinality' => 'HasOne',
        'name' => 'target',
        'label' => 'Target',
        'related_uri_key' => 'targets',
        'rules' => [],
        'config' => [],
    ]);

    expect($result['instance'])->toBeInstanceOf(HasOne::class);
});

it('renders a HasMany Nova field for the HasMany cardinality', function (): void {
    $result = RenderRelationship::run([
        'cardinality' => 'HasMany',
        'name' => 'targets',
        'label' => 'Targets',
        'related_uri_key' => 'targets',
        'rules' => [],
        'config' => [],
    ]);

    expect($result['instance'])->toBeInstanceOf(HasMany::class);
});

it('throws when the dynamic resource binding is missing', function (): void {
    RenderRelationship::run([
        'cardinality' => 'BelongsTo',
        'name' => 'missing',
        'label' => 'Missing',
        'related_uri_key' => 'missing-uri',
        'rules' => [],
        'config' => [],
    ]);
})->throws(InvalidArgumentException::class);
