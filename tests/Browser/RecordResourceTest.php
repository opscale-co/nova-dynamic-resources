<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Tests\Browser;

use Laravel\Dusk\Browser;
use Opscale\NovaDynamicResources\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

final class RecordResourceTest extends DuskTestCase
{
    #[Test]
    final public function can_navigate_to_seeded_dynamic_resource(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/events')
                ->waitForText('Events')
                ->assertSee('Events');
        });
    }

    #[Test]
    final public function can_view_seeded_dynamic_resource_create_form(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/events/new')
                ->waitForText('Name')
                ->assertSee('Name')
                ->assertSee('Description');
        });
    }
}
