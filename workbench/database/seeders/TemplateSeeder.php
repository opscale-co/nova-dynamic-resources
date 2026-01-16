<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Opscale\NovaCatalogs\Models\Catalog;
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

        // Get all field types from config
        $fieldTypes = Config::get('nova-dynamic-resources.fields', []);

        // Create the default template
        $template = Template::create([
            'label' => 'Test Resources',
            'singular_label' => 'Test Resource',
            'uri_key' => 'resources-test',
            'title' => 'name',
            'base_class' => null,
        ]);

        // Create one field for each type
        foreach (array_keys($fieldTypes) as $type) {
            $template->fields()->create([
                'type' => $type,
                'label' => ucfirst($type),
                'name' => $type,
            ]);
        }

    }
}
