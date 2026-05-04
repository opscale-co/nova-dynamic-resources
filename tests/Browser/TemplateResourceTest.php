<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Tests\Browser;

use Laravel\Dusk\Browser;
use Opscale\NovaDynamicResources\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

final class TemplateResourceTest extends DuskTestCase
{
    #[Test]
    final public function can_navigate_to_templates_index(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/templates')
                ->waitForText('Templates')
                ->assertSee('Templates');
        });
    }

    #[Test]
    final public function can_view_template_create_form(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/templates/new')
                ->waitForText('Create Template')
                ->assertSee('Label')
                ->assertSee('Type');
        });
    }

    #[Test]
    final public function template_create_form_validates_required_fields(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/templates/new')
                ->waitForText('Create Template')
                ->press('Create Template')
                ->waitForText('field is required')
                ->assertSee('field is required');
        });
    }
}
