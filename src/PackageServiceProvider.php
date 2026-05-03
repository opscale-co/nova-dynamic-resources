<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Opscale\NovaDynamicResources\Models\Template as TemplateModel;
use Opscale\NovaDynamicResources\Nova\Action;
use Opscale\NovaDynamicResources\Nova\Field;
use Opscale\NovaDynamicResources\Nova\Record;
use Opscale\NovaDynamicResources\Nova\Relationship;
use Opscale\NovaDynamicResources\Nova\Template;
use Opscale\NovaPackageTools\NovaPackage;
use Opscale\NovaPackageTools\NovaPackageServiceProvider;
use Override;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package as SpatiePackage;

class PackageServiceProvider extends NovaPackageServiceProvider
{
    /**
     * @var list<class-string<\Laravel\Nova\Resource<Model>>>
     */
    private array $resourceClasses = [
        Template::class,
        Field::class,
        Action::class,
        Relationship::class,
        Record::class,
    ];

    #[Override]
    public function configurePackage(SpatiePackage $package): void
    {
        /** @var NovaPackage $package */
        $package
            ->name('nova-dynamic-resources')
            ->hasConfigFile('nova-dynamic-resources')
            ->hasTranslations()
            ->discoversMigrations()
            ->runsMigrations()
            ->hasResources($this->resourceClasses)
            ->hasInstallCommand(function (InstallCommand $installCommand): void {
                $installCommand
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('opscale-co/nova-dynamic-resources');
            });
    }

    #[Override]
    public function packageBooted(): void
    {
        parent::packageBooted();
        Nova::serving(function (ServingNova $servingNova): void {
            Nova::resources($this->generateResources());
        });
    }

    /**
     * @return list<class-string<\Laravel\Nova\Resource<Model>>>
     */
    private function generateResources(): array
    {
        $templates = TemplateModel::instantiables()->get();
        $classes = [];
        foreach ($templates as $template) {
            $baseClass = $template->related_class ?? Record::class;

            $templateClass = TemplateModel::class;
            $anonymous = eval("
                return new class extends {$baseClass} {
                    public static {$templateClass} \$template;
                };");

            if (! is_object($anonymous)) {
                continue;
            }

            $class = get_class($anonymous);
            /** @var class-string<\Laravel\Nova\Resource<Model>> $class */
            $class::$template = $template;
            $binding = 'dynamic-'.$template->uri_key;
            $this->app->bindIf($binding, fn (): object => new $class);

            $classes[] = $class;
        }

        return $classes;
    }
}
