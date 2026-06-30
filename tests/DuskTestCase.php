<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Tests;

use Illuminate\Foundation\Application;
use Inertia\ServiceProvider;
use Laravel\Dusk\Browser;
use Laravel\Dusk\DuskServiceProvider;
use Laravel\Fortify\FortifyServiceProvider;
use Laravel\Nova\NovaCoreServiceProvider;
use Laravel\Nova\NovaServiceProvider;
use Lorisleiva\Actions\ActionServiceProvider;
use Opscale\Actions\ToolServiceProvider;
use Opscale\NovaDynamicResources\PackageServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\Dusk\TestCase as BaseTestCase;
use Override;

abstract class DuskTestCase extends BaseTestCase
{
    use WithWorkbench;

    /**
     * @var int
     */
    protected static $baseServePort = 8089;

    /**
     * Login to Nova via the browser using the seeded admin user.
     */
    final protected function loginToNova(Browser $browser): Browser
    {
        $browser->visit('/nova');

        if ($browser->element('input[name="email"]') !== null) {
            $browser->type('email', 'admin@laravel.com')
                ->type('password', 'password')
                ->press('Log In')
                ->waitForText('Get Started');
        }

        return $browser;
    }

    /**
     * @param  Application  $app
     * @return list<class-string>
     */
    #[Override]
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            ServiceProvider::class,
            NovaCoreServiceProvider::class,
            NovaServiceProvider::class,
            FortifyServiceProvider::class,
            DuskServiceProvider::class,
            // Lorisleiva's ActionServiceProvider binds the ActionManager
            // singleton; Opscale's ToolServiceProvider registers the Nova
            // design pattern. Without them the Dusk serve process resolves
            // a bare ActionManager with no design patterns and template
            // actions stay undecorated, breaking Nova serialization.
            ActionServiceProvider::class,
            ToolServiceProvider::class,
            PackageServiceProvider::class,
        ]);
    }

    /**
     * @param  Application  $app
     */
    #[Override]
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('app.key', 'base64:90QZykleHM4k2ZXroXYsrcLVoh7o3+BBAsGGatE0yp4=');
        $app['config']->set('session.driver', 'file');
        $app['config']->set('cache.default', 'file');
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => realpath(__DIR__.'/../vendor/orchestra/testbench-dusk/laravel/database/database.sqlite'),
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }
}
