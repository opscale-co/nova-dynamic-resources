<?php

namespace Opscale\NovaDynamicResources;

use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Opscale\NovaDynamicResources\Models\Template as TemplateModel;
use Opscale\NovaDynamicResources\Nova\Action;
use Opscale\NovaDynamicResources\Nova\Field;
use Opscale\NovaDynamicResources\Nova\Record;
use Opscale\NovaDynamicResources\Nova\Template;
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
            ->hasTranslations()
            ->discoversMigrations()
            ->runsMigrations()
            ->hasResources([
                Template::class,
                Field::class,
                Action::class,
                Record::class,
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
        $templates = TemplateModel::instantiables()->get();
        $classes = [];
        foreach ($templates as $template) {
            $baseClass = null;
            if ($template->related_class) {
                $baseClass = $template->related_class;
            } else {
                $baseClass = Record::class;
            }

            $templateClass = TemplateModel::class;
            $class = get_class(eval("
                return new class extends {$baseClass} {
                    public static {$templateClass} \$template;
                };"));
            $class::$template = $template;
            $binding = 'dynamic-' . $template->uri_key;
            $this->app->bindIf($binding, function ($app) use ($class): object {
                return new $class;
            });

            $classes[] = $class;
        }

        return $classes;
    }
}
