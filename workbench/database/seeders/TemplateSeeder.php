<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Opscale\NovaCatalogs\Models\Catalog;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Template;

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
            ],
            [
                'type' => 'description',
                'label' => 'Description',
                'name' => 'description',
                'required' => false,
            ],
            [
                'type' => 'address',
                'label' => 'Address',
                'name' => 'address',
                'required' => false,
            ],
            [
                'type' => 'date',
                'label' => 'Date',
                'name' => 'date',
                'required' => true,
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
            ],
        ]);
    }
}
