<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Services\Actions;

use Opscale\Actions\Action;
use Override;

class RenderAction extends Action
{
    #[Override]
    public function identifier(): string
    {
        return 'render-action';
    }

    #[Override]
    public function name(): string
    {
        return __('Render Action');
    }

    #[Override]
    public function description(): string
    {
        return __('Renders a Nova action from an action configuration');
    }

    /**
     * @return array<int, array{name: string, description: string, type: string, rules: array<int, string>}>
     */
    #[Override]
    public function parameters(): array
    {
        return [
            [
                'name' => 'class',
                'description' => 'The fully qualified class name of the action',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
            [
                'name' => 'config',
                'description' => 'Configuration array with method names as keys and parameters as values',
                'type' => 'array',
                'rules' => ['nullable', 'array'],
            ],
        ];
    }

    /**
     * Render a Nova action from an action configuration.
     *
     * @param  array{class?: class-string, config?: array<string, mixed>}  $attributes
     * @return array{success: bool, instance: object}
     */
    #[Override]
    public function handle(array $attributes = []): array
    {
        $this->fill($attributes);
        $validatedData = $this->validateAttributes();

        /** @var class-string $class */
        $class = $validatedData['class'];
        /** @var array<string, mixed> $config */
        $config = $validatedData['config'] ?? [];

        $instance = new $class;

        if ($config !== []) {
            foreach ($config as $method => $parameters) {
                if (method_exists($instance, $method)) {
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
