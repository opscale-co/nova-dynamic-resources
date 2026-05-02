<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Tests;

use Laravel\Dusk\Browser;
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
            \Laravel\Dusk\DuskServiceProvider::class,
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
    }
}
