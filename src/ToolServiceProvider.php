<?php

namespace Opscale\NovaDynamicResources;

use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Opscale\NovaDynamicResources\Models\DynamicResource as Template;
use Opscale\NovaDynamicResources\Nova\DynamicAction;
use Opscale\NovaDynamicResources\Nova\DynamicField;
use Opscale\NovaDynamicResources\Nova\DynamicRecord;
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
            ->hasResources([
                DynamicResource::class,
                DynamicField::class,
                DynamicAction::class,
                DynamicRecord::class,
            ])
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
            $baseClass = null;
            if ($resource->base_class && class_exists($resource->base_class)) {
                $baseClass = $resource->base_class;
            } else {
                $baseClass = DynamicRecord::class;
            }

            $templateClass = Template::class;
            $class = get_class(eval("
                return new class extends {$baseClass} {
                    public static {$templateClass} \$template;
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
