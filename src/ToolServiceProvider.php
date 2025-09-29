<?php

namespace Opscale\NovaDynamicResources;

use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Opscale\NovaDynamicResources\Models\DynamicResource as Template;
use Opscale\NovaDynamicResources\Nova\DynamicResource;
use Opscale\NovaPackageTools\NovaPackage;
use Opscale\NovaPackageTools\NovaPackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;

class ToolServiceProvider extends NovaPackageServiceProvider
{
    /**
     * @phpstan-ignore solid.ocp.conditionalOverride
     */
    public function configurePackage(Package $package): void
    {
        /** @var NovaPackage $package */
        $package
            ->name('nova-dynamic-resources')
            ->hasConfigFile('nova-dynamic-resources')
            ->discoversMigrations()
            ->runsMigrations()
            ->hasResources(DynamicResource::class)
            ->hasInstallCommand(function (InstallCommand $installCommand): void {
                $installCommand
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('opscale-co/nova-dynamic-resources');
            });
    }

    /**
     * @phpstan-ignore solid.ocp.conditionalOverride
     */
    public function packageBooted(): void
    {
        parent::packageBooted();
        Nova::serving(function (ServingNova $servingNova): void {
            Nova::resources($this->generateResources());
        });
    }

    private function generateResources(): array
    {
        $resources = Template::all();
        $classes = [];
        foreach ($resources as $resource) {
            $class = get_class(eval("
            return new class extends \Opscale\NovaDynamicResources\Nova\DynamicRecord{
                public static \Opscale\NovaDynamicResources\Models\DynamicResource \$template;
            };"));
            $class::$template = $resource;
            $binding = 'dynamic-' . $resource->uri_key;
            $this->app->bindIf($binding, function ($app) use ($class): object {
                return new $class;
            });

            $classes[] = $class;
        }

        return $classes;
    }
}
