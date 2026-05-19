<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Tests\Browser;

use Laravel\Dusk\Browser;
use Opscale\NovaDynamicResources\Models\Record;
use Opscale\NovaDynamicResources\Models\Template;
use Opscale\NovaDynamicResources\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;
use Workbench\App\Models\Item;
use Workbench\App\Models\User;

/**
 * Smoke-tests for detail rendering. Each test only visits the first
 * seeded record's detail URL and waits for Nova to settle. The test
 * passes as long as no exception is thrown during navigation — any
 * server-side render failure (500 response, missing class, etc.)
 * surfaces as a JS/console error that aborts the wait.
 */
final class RecordDetailRenderTest extends DuskTestCase
{
    #[Test]
    final public function dynamic_events_detail_renders_without_exception(): void
    {
        $this->assertDetailRenders('events', $this->firstDynamicRecordId('events'));
    }

    #[Test]
    final public function composited_products_detail_renders_without_exception(): void
    {
        $id = (string) Item::query()->orderBy('id')->firstOrFail()->id;

        $this->assertDetailRenders('products', $id);
    }

    #[Test]
    final public function composited_users_detail_renders_without_exception(): void
    {
        $id = (string) User::query()->where('email', 'admin@laravel.com')->firstOrFail()->id;

        $this->assertDetailRenders('users', $id);
    }

    #[Test]
    final public function dynamic_authors_detail_renders_without_exception(): void
    {
        $this->assertDetailRenders('authors', $this->firstDynamicRecordId('authors'));
    }

    #[Test]
    final public function dynamic_books_detail_renders_without_exception(): void
    {
        $this->assertDetailRenders('books', $this->firstDynamicRecordId('books'));
    }

    #[Test]
    final public function dynamic_showcases_detail_renders_without_exception(): void
    {
        $this->assertDetailRenders('showcases', $this->firstDynamicRecordId('showcases'));
    }

    private function assertDetailRenders(string $uriKey, string $id): void
    {
        $this->browse(function (Browser $browser) use ($uriKey, $id): void {
            $this->loginToNova($browser)
                ->visit("/nova/resources/{$uriKey}/{$id}")
                ->pause(1500)
                ->assertPathIs("/nova/resources/{$uriKey}/{$id}");
        });
    }

    private function firstDynamicRecordId(string $uriKey): string
    {
        $template = Template::query()->where('uri_key', $uriKey)->firstOrFail();

        /** @var Record $record */
        $record = Record::query()
            ->where('template_id', $template->id)
            ->orderBy('id')
            ->firstOrFail();

        return $record->id;
    }
}
