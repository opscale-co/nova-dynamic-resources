<?php

declare(strict_types=1);

use Opscale\NovaCatalogs\Models\Catalog;
use Opscale\NovaDynamicResources\Services\Actions\SelectOptions;

it('returns options for a given catalog', function (): void {
    $catalog = Catalog::create([
        'key' => 'currencies',
        'name' => 'Currencies',
        'description' => 'Test catalog',
    ]);

    $catalog->items()->create(['key' => 'usd', 'name' => 'US Dollar']);
    $catalog->items()->create(['key' => 'eur', 'name' => 'Euro']);

    $result = SelectOptions::run(['catalog' => 'currencies']);

    expect($result)->toBeArray()
        ->toHaveKeys(['success', 'value'])
        ->and($result['success'])->toBeTrue()
        ->and($result['value'])->toBeArray();
});

it('returns an empty list when catalog has no items', function (): void {
    Catalog::create([
        'key' => 'empty',
        'name' => 'Empty',
        'description' => 'No items',
    ]);

    $result = SelectOptions::run(['catalog' => 'empty']);

    expect($result['success'])->toBeTrue()
        ->and($result['value'])->toBeArray()->toBeEmpty();
});
