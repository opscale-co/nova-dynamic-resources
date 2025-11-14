<?php

namespace Opscale\NovaDynamicResources\Services\Actions;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Lorisleiva\Actions\Action;
use Opscale\NovaDynamicResources\Models\DynamicResource;

class RenderField extends Action
{
    /**
     * Render a Nova field from a field configuration.
     *
     * @param  DynamicResource  $resource
     * @param  array<mixed>  $rules
     * @param  array<string, mixed>  $config
     *
     * @throws InvalidArgumentException
     */
    public function handle(
        string $type,
        string $label,
        string $name,
        array $rules = [],
        array $config = []
    ): mixed {
        /** @var array{field: class-string, rules?: array<mixed>, config?: array<mixed>}|null $component */
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
                    is_subclass_of($hooks[$method], Action::class)) {
                    $parameters = $hooks[$method]::run($parameters);
                    $instance->{$method}($parameters);
                } elseif (is_string($method) && method_exists($instance, $method)) {
                    $instance = is_array($parameters) ?
                        $instance->{$method}(...$parameters) :
                        $instance->{$method}($parameters);
                }
            }
        }

        return $instance;
    }
}
