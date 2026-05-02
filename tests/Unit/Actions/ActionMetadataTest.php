<?php

declare(strict_types=1);

use Opscale\NovaDynamicResources\Services\Actions\CreateRecord;
use Opscale\NovaDynamicResources\Services\Actions\RenderAction;
use Opscale\NovaDynamicResources\Services\Actions\RenderField;
use Opscale\NovaDynamicResources\Services\Actions\SelectOptions;
use Opscale\NovaDynamicResources\Services\Actions\ViewRecord;

it('exposes a unique slug identifier on every Action', function (string $class, string $expected): void {
    /** @var \Opscale\Actions\Action $action */
    $action = new $class;

    expect($action->identifier())->toBe($expected);
})->with([
    [CreateRecord::class, 'create-record'],
    [RenderAction::class, 'render-action'],
    [RenderField::class, 'render-field'],
    [SelectOptions::class, 'select-options'],
    [ViewRecord::class, 'view-record'],
]);

it('exposes a non-empty human-readable name on every Action', function (string $class): void {
    /** @var \Opscale\Actions\Action $action */
    $action = new $class;

    expect($action->name())->toBeString()->not->toBeEmpty();
})->with([
    CreateRecord::class,
    RenderAction::class,
    RenderField::class,
    SelectOptions::class,
    ViewRecord::class,
]);

it('exposes a description on every Action', function (string $class): void {
    /** @var \Opscale\Actions\Action $action */
    $action = new $class;

    expect($action->description())->toBeString()->not->toBeEmpty();
})->with([
    CreateRecord::class,
    RenderAction::class,
    RenderField::class,
    SelectOptions::class,
    ViewRecord::class,
]);

it('exposes a parameters schema on every Action', function (string $class): void {
    /** @var \Opscale\Actions\Action $action */
    $action = new $class;

    expect($action->parameters())->toBeArray();
})->with([
    CreateRecord::class,
    RenderAction::class,
    RenderField::class,
    SelectOptions::class,
    ViewRecord::class,
]);
