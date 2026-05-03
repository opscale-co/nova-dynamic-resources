<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Tests\Browser;

use Laravel\Dusk\Browser;
use Opscale\NovaDynamicResources\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

final class SeededTemplatesTest extends DuskTestCase
{
    #[Test]
    final public function dynamic_events_index_shows_first_seeded_record(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/events')
                ->waitForText('Events')
                ->assertSee('Events')
                ->waitForText('Annual Conference')
                ->assertSee('Annual Conference');
        });
    }

    #[Test]
    final public function dynamic_events_create_form_renders_template_fields(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/events/new')
                ->waitForText('Name')
                ->assertSee('Name')
                ->assertSee('Description')
                ->assertSee('Address')
                ->assertSee('Date');
        });
    }

    #[Test]
    final public function inherited_products_index_shows_first_seeded_record(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/products')
                ->waitForText('Products')
                ->assertSee('Products')
                ->waitForText('Sample Widget')
                ->assertSee('Sample Widget');
        });
    }

    #[Test]
    final public function inherited_products_create_form_renders_host_and_template_fields(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/products/new')
                ->waitForText('Name')
                ->assertSee('Name')
                ->assertSee('Price')
                ->assertSee('Stock')
                ->assertSee('Weight');
        });
    }

    #[Test]
    final public function composited_users_index_shows_first_seeded_record(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/users')
                ->waitForText('Users')
                ->assertSee('Users')
                ->waitForText('Admin User')
                ->assertSee('Admin User');
        });
    }

    #[Test]
    final public function composited_users_create_form_renders_host_and_template_fields(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/users/new')
                ->waitForText('Name')
                ->assertSee('Name')
                ->assertSee('Email')
                ->assertSee('Password')
                ->assertSee('Phone');
        });
    }

    #[Test]
    final public function showcase_create_form_renders_every_field_type_and_relationship(): void
    {
        $expectedLabels = [
            'Address', 'Color', 'Country', 'Date', 'Description', 'Document',
            'Email', 'Gender', 'Hash', 'Image', 'Ip', 'Language', 'Marital Status',
            'Moment', 'Money', 'Name', 'Options', 'Password', 'Phone',
            'Postal Code', 'Post', 'Quantity', 'Rating', 'Region', 'Slug', 'Snippet',
            'State', 'Title', 'Token', 'Ulid', 'Url', 'Username', 'Uuid', 'Yes No',
            'Audio', 'Video', 'File', 'Pdf',
            'Parent', // dynamic Relationship rendered alongside template fields
        ];

        $this->browse(function (Browser $browser) use ($expectedLabels): void {
            $browser = $this->loginToNova($browser)
                ->visit('/nova/resources/showcases/new')
                ->waitForText('Create Showcase');

            foreach ($expectedLabels as $label) {
                $browser->assertSee($label);
            }
        });
    }
}
