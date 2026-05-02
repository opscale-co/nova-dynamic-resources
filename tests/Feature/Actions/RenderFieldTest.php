<?php

declare(strict_types=1);

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Mockery;
use Opscale\NovaDynamicResources\Services\Actions\RenderField;

it('renders a Nova field for a configured type', function (): void {
    $result = RenderField::run([
        'type' => 'name',
        'label' => 'Title',
        'name' => 'title',
        'required' => true,
        'display_in_index' => true,
        'rules' => [],
        'config' => [],
    ]);

    expect($result)->toBeArray()
        ->toHaveKeys(['success', 'instance'])
        ->and($result['success'])->toBeTrue()
        ->and($result['instance'])->toBeInstanceOf(Field::class);
});

it('throws when field type is not in configuration', function (): void {
    RenderField::run([
        'type' => 'this_type_does_not_exist',
        'label' => 'Bogus',
        'name' => 'bogus',
        'required' => false,
        'display_in_index' => true,
        'rules' => [],
        'config' => [],
    ]);
})->throws(InvalidArgumentException::class, 'Invalid field type:');

it('hides from index when display_in_index is false', function (): void {
    $result = RenderField::run([
        'type' => 'description',
        'label' => 'Notes',
        'name' => 'notes',
        'required' => false,
        'display_in_index' => false,
        'rules' => [],
        'config' => [],
    ]);

    /** @var Field $field */
    $field = $result['instance'];

    expect($field->showOnIndex)->toBeFalse();
});

it('marks the field as required when required is true', function (): void {
    $result = RenderField::run([
        'type' => 'name',
        'label' => 'Title',
        'name' => 'title',
        'required' => true,
        'display_in_index' => true,
        'rules' => [],
        'config' => [],
    ]);

    /** @var Field $field */
    $field = $result['instance'];

    expect($field->getRules(Mockery::mock(NovaRequest::class)))
        ->toBeArray();
});
