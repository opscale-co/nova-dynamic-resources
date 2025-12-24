<?php

namespace Opscale\NovaDynamicResources\Services\Actions;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Opscale\Actions\Action;
use Override;

class RenderField extends Action
{
    #[Override]
    public function identifier(): string
    {
        return 'render-field';
    }

    #[Override]
    public function name(): string
    {
        return __('Render Field');
    }

    #[Override]
    public function description(): string
    {
        return __('Renders a Nova field from a field configuration');
    }

    #[Override]
    public function parameters(): array
    {
        return [
            [
                'name' => 'type',
                'description' => 'The field type identifier from configuration',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
            [
                'name' => 'label',
                'description' => 'The display label for the field',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
            [
                'name' => 'name',
                'description' => 'The field name/attribute',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
            [
                'name' => 'rules',
                'description' => 'Validation rules for the field',
                'type' => 'array',
                'rules' => ['nullable', 'array'],
            ],
            [
                'name' => 'config',
                'description' => 'Additional field configuration',
                'type' => 'array',
                'rules' => ['nullable', 'array'],
            ],
        ];
    }

    /**
     * Render a Nova field from a field configuration.
     *
     * @param  array{type?: string, label?: string, name?: string, rules?: array<mixed>, config?: array<string, mixed>}  $attributes
     *
     * @throws InvalidArgumentException
     */
    #[Override]
    public function handle(array $attributes = []): array
    {
        $this->fill($attributes);
        $validatedData = $this->validateAttributes();

        $type = $validatedData['type'];
        $label = $validatedData['label'];
        $name = $validatedData['name'];
        /** @var array<mixed> $rules */
        $rules = $validatedData['rules'] ?? [];
        /** @var array<string, mixed> $config */
        $config = $validatedData['config'] ?? [];

        /** @var array{field: class-string, rules?: array<mixed>, config?: array<mixed>, hooks?: array<string, class-string>}|null $component */
        $component = Config::get('nova-dynamic-resources.fields.' . $type, null);

        if ($component === null) {
            throw new InvalidArgumentException('Invalid field type: ' . $type);
        }

        /** @var array<mixed> $mergedRules */
        $mergedRules = array_merge($component['rules'] ?? [], $rules);
        /** @var array<string, mixed> $mergedConfig */
        $mergedConfig = array_merge($component['config'] ?? [], $config);
        /** @var array<string, class-string> $hooks */
        $hooks = $component['hooks'] ?? [];

        $fieldClass = $component['field'];
        $instance = $fieldClass::make(
            $label,
            'data->' . $name,
        )->rules($mergedRules);

        if (! empty($mergedConfig)) {
            foreach ($mergedConfig as $method => $parameters) {
                // Check if there's a hook for this method
                if (isset($hooks[$method]) &&
                    is_subclass_of($hooks[$method], \Opscale\Actions\Action::class)) {
                    /** @var class-string<\Opscale\Actions\Action> $hookClass */
                    $hookClass = $hooks[$method];
                    /** @var array{success: bool, value: mixed} $hookResult */
                    $hookResult = $hookClass::run(['catalog' => $parameters]);
                    $instance->{$method}($hookResult['value'] ?? $parameters);
                } elseif (is_string($method) && method_exists($instance, $method)) {
                    $instance = is_array($parameters) ?
                        $instance->{$method}(...$parameters) :
                        $instance->{$method}($parameters);
                }
            }
        }

        return [
            'success' => true,
            'instance' => $instance,
        ];
    }
}
