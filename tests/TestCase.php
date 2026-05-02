<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Tests;

use Opscale\NovaDynamicResources\PackageServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Override;

abstract class TestCase extends BaseTestCase
{
    use WithWorkbench;

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return list<class-string>
     */
    #[Override]
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            \Inertia\ServiceProvider::class,
            \Laravel\Nova\NovaCoreServiceProvider::class,
            \Laravel\Nova\NovaServiceProvider::class,
            \Laravel\Fortify\FortifyServiceProvider::class,
            PackageServiceProvider::class,
        ]);
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    #[Override]
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('app.key', 'base64:90QZykleHM4k2ZXroXYsrcLVoh7o3+BBAsGGatE0yp4=');
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    #[Override]
    protected function defineDatabaseMigrations(): void
    {
        parent::defineDatabaseMigrations();
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/../vendor/opscale-co/nova-catalogs/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../workbench/database/migrations');
    }
}
