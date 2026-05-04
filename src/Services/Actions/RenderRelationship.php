<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Services\Actions;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Laravel\Nova\Fields\Field;
use Opscale\Actions\Action;
use Opscale\NovaDynamicResources\Models\Enums\RelationshipCardinality;
use Override;

class RenderRelationship extends Action
{
    #[Override]
    public function identifier(): string
    {
        return 'render-relationship';
    }

    #[Override]
    public function name(): string
    {
        return __('Render Relationship');
    }

    #[Override]
    public function description(): string
    {
        return __('Renders a Nova relation field from a relationship configuration');
    }

    /**
     * @return array<int, array{name: string, description: string, type: string, rules: array<int, string>}>
     */
    #[Override]
    public function parameters(): array
    {
        return [
            [
                'name' => 'cardinality',
                'description' => 'The relationship cardinality (BelongsTo / HasOne / HasMany)',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
            [
                'name' => 'name',
                'description' => 'The relation method name',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
            [
                'name' => 'label',
                'description' => 'The display label',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
            [
                'name' => 'related_uri_key',
                'description' => 'The uri_key of the related Template (used to resolve the dynamic Resource binding)',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
            [
                'name' => 'rules',
                'description' => 'Validation rules for the relation',
                'type' => 'array',
                'rules' => ['nullable', 'array'],
            ],
            [
                'name' => 'config',
                'description' => 'Additional Nova field configuration',
                'type' => 'array',
                'rules' => ['nullable', 'array'],
            ],
        ];
    }

    /**
     * @param  array{cardinality?: string, name?: string, label?: string, related_uri_key?: string, rules?: array<mixed>, config?: array<string, mixed>}  $attributes
     * @return array{success: bool, instance: Field}
     *
     * @throws InvalidArgumentException
     */
    #[Override]
    public function handle(array $attributes = []): array
    {
        $this->fill($attributes);
        $validatedData = $this->validateAttributes();

        $cardinality = RelationshipCardinality::from($validatedData['cardinality']);
        $name = $validatedData['name'];
        $label = $validatedData['label'];
        $relatedUriKey = $validatedData['related_uri_key'];
        /** @var array<mixed> $rules */
        $rules = $validatedData['rules'] ?? [];
        /** @var array<string, mixed> $config */
        $config = $validatedData['config'] ?? [];

        /** @var array{field: class-string<Field>, rules?: array<mixed>, config?: array<mixed>}|null $component */
        $component = Config::get('nova-dynamic-resources.relationships.'.$this->configKey($cardinality), null);

        if ($component === null) {
            throw new InvalidArgumentException('Invalid relationship cardinality: '.$cardinality->value);
        }

        $resourceClass = $this->resolveResourceClass($relatedUriKey);

        /** @var array<mixed> $mergedRules */
        $mergedRules = array_merge($component['rules'] ?? [], $rules);
        /** @var array<string, mixed> $mergedConfig */
        $mergedConfig = array_merge($component['config'] ?? [], $config);

        /** @var class-string<Field> $fieldClass */
        $fieldClass = $component['field'];
        /** @var Field $instance */
        $instance = $fieldClass::make($label, $name, $resourceClass);
        /** @var array<int, mixed> $mergedRules */
        $instance->rules($mergedRules);

        foreach ($mergedConfig as $method => $parameters) {
            if (method_exists($instance, $method)) {
                $instance = is_array($parameters)
                    ? $instance->{$method}(...$parameters)
                    : $instance->{$method}($parameters);
            }
        }

        return [
            'success' => true,
            'instance' => $instance,
        ];
    }

    /**
     * @return class-string
     */
    final protected function resolveResourceClass(string $uriKey): string
    {
        $binding = 'dynamic-'.$uriKey;
        $container = Container::getInstance();

        if (! $container->bound($binding)) {
            throw new InvalidArgumentException('Dynamic resource binding not found: '.$binding);
        }

        $instance = $container->make($binding);

        if (! is_object($instance)) {
            throw new InvalidArgumentException('Dynamic resource binding did not resolve to an object: '.$binding);
        }

        return get_class($instance);
    }

    final protected function configKey(RelationshipCardinality $cardinality): string
    {
        return match ($cardinality) {
            RelationshipCardinality::BelongsTo => 'belongs_to',
            RelationshipCardinality::HasOne => 'has_one',
            RelationshipCardinality::HasMany => 'has_many',
        };
    }
}
