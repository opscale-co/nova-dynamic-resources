<?php

declare(strict_types=1);

use Opscale\NovaDynamicResources\Models\Enums\TemplateType;

it('has three cases', function (): void {
    expect(TemplateType::cases())->toHaveCount(3);
});

it('exposes Dynamic, Inherited, Composited values', function (): void {
    expect(TemplateType::Dynamic->value)->toBe('Dynamic')
        ->and(TemplateType::Inherited->value)->toBe('Inherited')
        ->and(TemplateType::Composited->value)->toBe('Composited');
});
