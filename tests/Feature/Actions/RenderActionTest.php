<?php

declare(strict_types=1);

use Opscale\NovaDynamicResources\Services\Actions\CreateRecord;
use Opscale\NovaDynamicResources\Services\Actions\RenderAction;

it('instantiates an action from its class name', function (): void {
    $result = RenderAction::run([
        'class' => CreateRecord::class,
        'config' => [],
    ]);

    expect($result)->toBeArray()
        ->toHaveKeys(['success', 'instance'])
        ->and($result['success'])->toBeTrue()
        ->and($result['instance'])->toBeInstanceOf(CreateRecord::class);
});
