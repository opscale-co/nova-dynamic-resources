<?php

declare(strict_types=1);

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Opscale\NovaCatalogs\Models\Catalog;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Record;
use Opscale\NovaDynamicResources\Models\Template;
use Workbench\App\Models\Item;
use Workbench\App\Models\User;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create catalog with key "options"
        $catalog = Catalog::create([
            'key' => 'options',
            'name' => 'Options',
            'description' => 'General options catalog for testing',
        ]);

        // Add 5 random items to the catalog
        $items = [
            ['key' => 'option-1', 'name' => 'Option One'],
            ['key' => 'option-2', 'name' => 'Option Two'],
            ['key' => 'option-3', 'name' => 'Option Three'],
            ['key' => 'option-4', 'name' => 'Option Four'],
            ['key' => 'option-5', 'name' => 'Option Five'],
        ];

        foreach ($items as $item) {
            $catalog->items()->create($item);
        }

        // Create Dynamic template for Events
        $eventsTemplate = Template::create([
            'label' => 'Events',
            'singular_label' => 'Event',
            'uri_key' => 'events',
            'title' => 'name',
            'type' => TemplateType::Dynamic,
            'related_class' => null,
        ]);

        $eventsTemplate->fields()->createMany([
            [
                'type' => 'name',
                'label' => 'Name',
                'name' => 'name',
                'required' => true,
                'display_in_index' => true,
            ],
            [
                'type' => 'description',
                'label' => 'Description',
                'name' => 'description',
                'required' => false,
                'display_in_index' => true,
            ],
            [
                'type' => 'address',
                'label' => 'Address',
                'name' => 'address',
                'required' => false,
                'display_in_index' => true,
            ],
            [
                'type' => 'date',
                'label' => 'Date',
                'name' => 'date',
                'required' => true,
                'display_in_index' => true,
            ],
        ]);

        // Create Inherited template for Products
        $productTemplate = Template::create([
            'label' => 'Products',
            'singular_label' => 'Product',
            'uri_key' => 'products',
            'title' => 'name',
            'type' => TemplateType::Inherited,
            'related_class' => \Workbench\App\Nova\Item::class,
        ]);

        $productTemplate->fields()->createMany([
            [
                'type' => 'quantity',
                'label' => 'Weight',
                'name' => 'weight',
                'required' => false,
                'display_in_index' => false,
            ],
            [
                'type' => 'quantity',
                'label' => 'Height',
                'name' => 'height',
                'required' => false,
                'display_in_index' => false,
            ],
            [
                'type' => 'quantity',
                'label' => 'Width',
                'name' => 'width',
                'required' => false,
                'display_in_index' => false,
            ],
        ]);

        // Create Composited template for Users
        $userTemplate = Template::create([
            'label' => 'Users',
            'singular_label' => 'User',
            'uri_key' => 'users',
            'title' => 'name',
            'type' => TemplateType::Composited,
            'related_class' => \Workbench\App\Nova\User::class,
        ]);

        $userTemplate->fields()->createMany([
            [
                'type' => 'phone',
                'label' => 'Phone',
                'name' => 'phone',
                'required' => false,
                'display_in_index' => true,
            ],
        ]);

        // Seed sample Event records (Dynamic template)
        Record::create([
            'template_id' => $eventsTemplate->id,
            'data' => [
                'name' => 'Annual Conference',
                'description' => 'Yearly meetup of contributors',
                'address' => '123 Main St, Springfield',
                'date' => '2026-09-15',
            ],
        ]);

        Record::create([
            'template_id' => $eventsTemplate->id,
            'data' => [
                'name' => 'Product Launch',
                'description' => 'Public unveiling of v2.0',
                'address' => '500 Market Ave',
                'date' => '2026-11-02',
            ],
        ]);

        // Seed sample Product records (Inherited template)
        Item::create([
            'template_id' => $productTemplate->id,
            'name' => 'Sample Widget',
            'description' => 'A demo widget for testing',
            'price' => 19.99,
            'stock' => 25,
            'data' => [
                'weight' => 1.5,
                'height' => 10,
                'width' => 5,
            ],
        ]);

        Item::create([
            'template_id' => $productTemplate->id,
            'name' => 'Sample Gadget',
            'description' => 'A demo gadget for testing',
            'price' => 49.50,
            'stock' => 10,
            'data' => [
                'weight' => 3.2,
                'height' => 20,
                'width' => 8,
            ],
        ]);

        // Attach Composited template data to existing users
        User::query()->take(2)->get()->each(function (User $user): void {
            $user->forceFill(['data' => ['phone' => '+1-555-0100']])->save();
        });

        // Create Dynamic template that exercises every renderable field type
        $showcaseTemplate = Template::create([
            'label' => 'Showcases',
            'singular_label' => 'Showcase',
            'uri_key' => 'showcases',
            'title' => 'name',
            'type' => TemplateType::Dynamic,
            'related_class' => null,
        ]);

        $showcaseFieldTypes = [
            'address', 'color', 'country', 'date', 'description', 'document',
            'email', 'gender', 'hash', 'image', 'ip', 'language', 'maritalStatus',
            'moment', 'money', 'name', 'options', 'password', 'phone',
            'postalCode', 'post', 'quantity', 'rating', 'region', 'slug', 'snippet',
            'state', 'title', 'token', 'ulid', 'url', 'username', 'uuid', 'yesNo',
            'audio', 'video', 'file', 'pdf',
        ];

        foreach ($showcaseFieldTypes as $type) {
            $showcaseTemplate->fields()->create([
                'type' => $type,
                'label' => Str::headline($type),
                'name' => $type,
                'required' => false,
                'display_in_index' => false,
            ]);
        }
    }
}
