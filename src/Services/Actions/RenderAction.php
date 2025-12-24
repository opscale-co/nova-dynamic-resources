<?php

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

        if (! empty($config)) {
            foreach ($config as $method => $parameters) {
                if (is_string($method) && method_exists($instance, $method)) {
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
