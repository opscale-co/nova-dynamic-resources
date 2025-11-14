<?php

namespace Opscale\NovaDynamicResources\Services\Actions;

use Lorisleiva\Actions\Action;

class RenderAction extends Action
{
    /**
     * Render a Nova action from an action configuration.
     *
     * @param  class-string  $class
     * @param  array<string, mixed>  $config
     */
    public function handle(
        string $class,
        array $config = []
    ): mixed {
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

        return $instance;
    }
}
