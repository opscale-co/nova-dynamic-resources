<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Tests\Browser;

use Laravel\Dusk\Browser;
use Opscale\NovaDynamicResources\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

final class NovaLoginTest extends DuskTestCase
{
    #[Test]
    final public function admin_user_can_log_in_to_nova(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->assertPathIs('/nova/dashboards/main')
                ->assertSee('Get Started');
        });
    }
}
