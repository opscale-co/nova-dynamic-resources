<?php

declare(strict_types=1);

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Opscale\NovaCatalogs\Models\Catalog;
use Opscale\NovaDynamicResources\Models\Action;
use Opscale\NovaDynamicResources\Models\Enums\RelationshipCardinality;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Record;
use Opscale\NovaDynamicResources\Models\Template;
use Workbench\App\Models\Bundle;
use Workbench\App\Models\Item;
use Workbench\App\Models\User;
use Workbench\App\Services\Actions\DummyAction;

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

        // Create Composited template for Products. Composited (not
        // Inherited) is the correct type because Workbench\App\Nova\Item
        // is a regular Nova Resource (not a Record subclass) — dynamic
        // template fields live in the host model's `data` JSON column
        // alongside its native columns.
        $productTemplate = Template::create([
            'label' => 'Products',
            'singular_label' => 'Product',
            'uri_key' => 'products',
            'title' => 'name',
            'type' => TemplateType::Composited,
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

        // Create paired Dynamic templates to demonstrate BelongsTo + HasMany.
        // Author has many Books; Book belongs to Author.
        $authorsTemplate = Template::create([
            'label' => 'Authors',
            'singular_label' => 'Author',
            'uri_key' => 'authors',
            'title' => 'name',
            'type' => TemplateType::Dynamic,
            'related_class' => null,
        ]);

        $authorsTemplate->fields()->createMany([
            [
                'type' => 'name',
                'label' => 'Name',
                'name' => 'name',
                'required' => true,
                'display_in_index' => true,
            ],
            [
                'type' => 'description',
                'label' => 'Biography',
                'name' => 'biography',
                'required' => false,
                'display_in_index' => false,
            ],
        ]);

        $booksTemplate = Template::create([
            'label' => 'Books',
            'singular_label' => 'Book',
            'uri_key' => 'books',
            'title' => 'title',
            'type' => TemplateType::Dynamic,
            'related_class' => null,
        ]);

        $booksTemplate->fields()->createMany([
            [
                'type' => 'title',
                'label' => 'Title',
                'name' => 'title',
                'required' => true,
                'display_in_index' => true,
            ],
            [
                'type' => 'date',
                'label' => 'Published At',
                'name' => 'published_at',
                'required' => false,
                'display_in_index' => true,
            ],
        ]);

        // Book → Author (BelongsTo): renders before the fields on a Book.
        $booksTemplate->relationships()->create([
            'name' => 'author',
            'label' => 'Author',
            'cardinality' => RelationshipCardinality::BelongsTo,
            'related_template_id' => $authorsTemplate->id,
            'foreign_key' => 'author_id',
            'inverse_name' => 'books',
            'required' => true,
        ]);

        // Author → Books (HasMany): renders as a tab after the fields on an Author.
        $authorsTemplate->relationships()->create([
            'name' => 'books',
            'label' => 'Books',
            'cardinality' => RelationshipCardinality::HasMany,
            'related_template_id' => $booksTemplate->id,
            'foreign_key' => 'author_id',
            'inverse_name' => 'author',
            'required' => false,
        ]);

        $jane = Record::create([
            'template_id' => $authorsTemplate->id,
            'data' => [
                'name' => 'Jane Austen',
                'biography' => 'English novelist known for her social commentary.',
            ],
        ]);

        $george = Record::create([
            'template_id' => $authorsTemplate->id,
            'data' => [
                'name' => 'George Orwell',
                'biography' => 'English novelist, essayist, and critic.',
            ],
        ]);

        Record::create([
            'template_id' => $booksTemplate->id,
            'data' => [
                'title' => 'Pride and Prejudice',
                'published_at' => '1813-01-28',
                'author_id' => $jane->id,
            ],
        ]);

        Record::create([
            'template_id' => $booksTemplate->id,
            'data' => [
                'title' => 'Sense and Sensibility',
                'published_at' => '1811-10-30',
                'author_id' => $jane->id,
            ],
        ]);

        Record::create([
            'template_id' => $booksTemplate->id,
            'data' => [
                'title' => '1984',
                'published_at' => '1949-06-08',
                'author_id' => $george->id,
            ],
        ]);

        Record::create([
            'template_id' => $booksTemplate->id,
            'data' => [
                'title' => 'Animal Farm',
                'published_at' => '1945-08-17',
                'author_id' => $george->id,
            ],
        ]);

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
            'address', 'audio', 'bpmn', 'city', 'color', 'country', 'date',
            'dbml', 'description', 'document', 'email', 'file', 'gender',
            'hash', 'image', 'ip', 'language', 'maritalStatus', 'moment',
            'money', 'name', 'options', 'password', 'pdf', 'phone',
            'postalCode', 'post', 'quantity', 'rating', 'region', 'slug',
            'snippet', 'state', 'title', 'token', 'ulid', 'url', 'username',
            'uuid', 'video', 'yesNo',
            'location', 'place', 'geofence', 'area', 'route',
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

        // Self-referential relationship to validate dynamic resolution end-to-end.
        $showcaseTemplate->relationships()->create([
            'name' => 'parent',
            'label' => 'Parent',
            'cardinality' => RelationshipCardinality::BelongsTo,
            'related_template_id' => $showcaseTemplate->id,
            'foreign_key' => 'parent_id',
            'inverse_name' => 'children',
            'required' => false,
        ]);

        // Fully populated showcase record so the detail view exercises every
        // renderable field type with a representative value.
        Record::create([
            'template_id' => $showcaseTemplate->id,
            'data' => [
                'address' => '742 Evergreen Terrace, Springfield, OR 97477',
                'audio' => 'showcase/sample.mp3',
                'bpmn' => 'showcase/sample.bpmn',
                'city' => 'option-1',
                'color' => '#3490DC',
                'country' => 'US',
                'date' => '2025-12-25',
                'dbml' => "Table showcase {\n  id integer [pk]\n  name varchar\n}",
                'description' => 'A fully populated showcase record used to exercise every renderable field type in the dynamic resources package.',
                'document' => 'AB-12345678',
                'email' => 'showcase@example.com',
                'file' => 'showcase/sample.txt',
                'gender' => 'option-1',
                'hash' => 'a1b2c3d4e5f67890abcdef0123456789',
                'image' => 'showcase/sample.png',
                'ip' => '192.168.1.100',
                'language' => 'option-1',
                'maritalStatus' => 'option-2',
                'moment' => '2025-12-25 14:30:00',
                'money' => 1299.99,
                'name' => 'Showcase One',
                'options' => 'option-3',
                'password' => null,
                'pdf' => 'showcase/sample.pdf',
                'phone' => '555-123-4567',
                'postalCode' => '97477',
                'post' => '<p>Hello <strong>world</strong> from the showcase.</p>',
                'quantity' => 42,
                'rating' => 4.5,
                'region' => 'Pacific Northwest',
                'slug' => 'showcase-one',
                'snippet' => "<?php\n\necho 'Hello, world!';",
                'state' => 'option-4',
                'title' => 'Showcase Title',
                'token' => 'tk_abcdef0123456789abcdef0123456789',
                'ulid' => '01HZX2J0K5M8N3P6Q9R7S4T2V0',
                'url' => 'https://example.com/showcase',
                'username' => 'showcase_user',
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'video' => 'showcase/sample.mp4',
                'yesNo' => true,

                // Geospatial fields — all stored as GeoJSON. Coordinates are
                // [lng, lat] per the GeoJSON spec.
                'location' => [
                    'type' => 'Point',
                    'coordinates' => [-122.6765, 45.5231], // Portland, OR
                ],
                'place' => [
                    'type' => 'Point',
                    'coordinates' => [-122.4194, 37.7749], // San Francisco, CA
                    'properties' => [
                        'formatted' => '1 Market St, San Francisco, CA 94105, USA',
                    ],
                ],
                'geofence' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-122.4250, 37.7700],
                        [-122.4100, 37.7700],
                        [-122.4100, 37.7800],
                        [-122.4250, 37.7800],
                        [-122.4250, 37.7700],
                    ]],
                ],
                'area' => [
                    'type' => 'Point',
                    'coordinates' => [-122.3321, 47.6062], // Seattle, WA
                    'properties' => [
                        'radius' => 1500,
                    ],
                ],
                'route' => [
                    'type' => 'LineString',
                    'coordinates' => [
                        [-122.6765, 45.5231], // Portland
                        [-122.4194, 37.7749], // San Francisco
                        [-118.2437, 34.0522], // Los Angeles
                    ],
                    'properties' => [
                        'waypoints' => [
                            [-122.6765, 45.5231],
                            [-122.4194, 37.7749],
                            [-118.2437, 34.0522],
                        ],
                        'distance' => 1635000,
                        'duration' => 60000,
                    ],
                ],
            ],
        ]);

        // Composited counterpart of the Dynamic Showcase. Layers every
        // renderable field type on top of the real Item Eloquent model so
        // composited template rendering can be exercised end-to-end and
        // so the Bundle TemplatedRepeater has a second Item-targeting
        // template in its "+ Add" menu.
        $compositedShowcaseTemplate = Template::create([
            'label' => 'Composited Showcases',
            'singular_label' => 'Composited Showcase',
            'uri_key' => 'composited-showcases',
            'title' => 'name',
            'type' => TemplateType::Composited,
            'related_class' => \Workbench\App\Nova\Item::class,
        ]);

        foreach ($showcaseFieldTypes as $type) {
            $compositedShowcaseTemplate->fields()->create([
                'type' => $type,
                'label' => Str::headline($type),
                'name' => $type,
                'required' => false,
                'display_in_index' => false,
            ]);
        }

        Item::create([
            'template_id' => $compositedShowcaseTemplate->id,
            'name' => 'Composited Showcase One',
            'description' => 'Host model attributes (name, description, price, stock) coexist with every dynamic field type.',
            'price' => 299.99,
            'stock' => 7,
            'data' => [
                'address' => '742 Evergreen Terrace, Springfield, OR 97477',
                'audio' => 'showcase/sample.mp3',
                'bpmn' => 'showcase/sample.bpmn',
                'city' => 'option-1',
                'color' => '#9F7AEA',
                'country' => 'US',
                'date' => '2026-05-16',
                'dbml' => "Table composited_showcase {\n  id integer [pk]\n  name varchar\n}",
                'description' => 'Composited record that exercises every dynamic field type alongside the host Item model.',
                'document' => 'CS-87654321',
                'email' => 'composited@example.com',
                'file' => 'showcase/sample.txt',
                'gender' => 'option-2',
                'hash' => 'fedcba9876543210fedcba9876543210',
                'image' => 'showcase/sample.png',
                'ip' => '10.0.0.42',
                'language' => 'option-2',
                'maritalStatus' => 'option-3',
                'moment' => '2026-05-16 09:00:00',
                'money' => 299.99,
                'name' => 'Composited Showcase One',
                'options' => 'option-4',
                'password' => null,
                'pdf' => 'showcase/sample.pdf',
                'phone' => '555-987-6543',
                'postalCode' => '94105',
                'post' => '<p>Composited <em>showcase</em> body.</p>',
                'quantity' => 84,
                'rating' => 3.5,
                'region' => 'Bay Area',
                'slug' => 'composited-showcase-one',
                'snippet' => "<?php\n\nreturn 'composited';",
                'state' => 'option-5',
                'title' => 'Composited Showcase Title',
                'token' => 'tk_composited0123456789abcdef012345',
                'ulid' => '01HZX2J0K5M8N3P6Q9R7S4T2V1',
                'url' => 'https://example.com/composited-showcase',
                'username' => 'composited_user',
                'uuid' => '650e8400-e29b-41d4-a716-446655440001',
                'video' => 'showcase/sample.mp4',
                'yesNo' => false,

                // Geospatial fields (same GeoJSON shapes as the Dynamic Showcase).
                'location' => [
                    'type' => 'Point',
                    'coordinates' => [-74.0060, 40.7128], // New York, NY
                ],
                'place' => [
                    'type' => 'Point',
                    'coordinates' => [-87.6298, 41.8781], // Chicago, IL
                    'properties' => [
                        'formatted' => '233 S Wacker Dr, Chicago, IL 60606, USA',
                    ],
                ],
                'geofence' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-87.6400, 41.8700],
                        [-87.6200, 41.8700],
                        [-87.6200, 41.8900],
                        [-87.6400, 41.8900],
                        [-87.6400, 41.8700],
                    ]],
                ],
                'area' => [
                    'type' => 'Point',
                    'coordinates' => [-71.0589, 42.3601], // Boston, MA
                    'properties' => [
                        'radius' => 2500,
                    ],
                ],
                'route' => [
                    'type' => 'LineString',
                    'coordinates' => [
                        [-74.0060, 40.7128], // New York
                        [-75.1652, 39.9526], // Philadelphia
                        [-77.0369, 38.9072], // Washington, DC
                    ],
                    'properties' => [
                        'waypoints' => [
                            [-74.0060, 40.7128],
                            [-75.1652, 39.9526],
                            [-77.0369, 38.9072],
                        ],
                        'distance' => 365000,
                        'duration' => 14400,
                    ],
                ],
            ],
        ]);

        // Seed a Bundle holding several child Items from both Composited
        // templates (Products + Composited Showcases). Exercises the
        // TemplatedRepeater field on the Bundle Nova resource — its
        // "+ Add" menu should list one Repeatable per Composited template
        // mapped to the Item Nova resource.
        $bundle = Bundle::create([
            'name' => 'Sample Bundle',
            'description' => 'Demonstrates the TemplatedRepeater HasMany layout with mixed Inherited child rows.',
        ]);

        Item::create([
            'bundle_id' => $bundle->id,
            'template_id' => $productTemplate->id,
            'name' => 'Bundled Widget',
            'description' => 'Product row attached to the bundle.',
            'price' => 24.99,
            'stock' => 15,
            'data' => [
                'weight' => 2.1,
                'height' => 12,
                'width' => 6,
            ],
        ]);

        Item::create([
            'bundle_id' => $bundle->id,
            'template_id' => $productTemplate->id,
            'name' => 'Bundled Gadget',
            'description' => 'Second product row in the bundle.',
            'price' => 59.00,
            'stock' => 8,
            'data' => [
                'weight' => 4.0,
                'height' => 22,
                'width' => 9,
            ],
        ]);

        Item::create([
            'bundle_id' => $bundle->id,
            'template_id' => $compositedShowcaseTemplate->id,
            'name' => 'Bundled Showcase',
            'description' => 'Composited showcase row mixed into the same bundle.',
            'price' => 199.99,
            'stock' => 3,
            'data' => [
                'name' => 'Bundled Showcase',
                'title' => 'Bundle Showcase Title',
                'slug' => 'bundled-showcase',
                'date' => '2026-06-01',
                'moment' => '2026-06-01 10:30:00',
                'email' => 'bundled@example.com',
                'url' => 'https://example.com/bundled-showcase',
                'color' => '#38B2AC',
                'yesNo' => true,
                'quantity' => 12,
                'rating' => 4.0,
            ],
        ]);

        // Seed DummyAction rows to verify Template Actions render correctly
        // through RenderAction → Nova. The chainable label() setter is fed
        // from config.label so each row appears with its own name.
        Action::create([
            'template_id' => $eventsTemplate->id,
            'class' => DummyAction::class,
            'label' => 'Mark as Confirmed',
            'config' => ['label' => ['Mark as Confirmed']],
            'data' => null,
        ]);

        Action::create([
            'template_id' => $eventsTemplate->id,
            'class' => DummyAction::class,
            'label' => 'Send Reminder',
            'config' => ['label' => ['Send Reminder']],
            'data' => null,
        ]);

        Action::create([
            'template_id' => $productTemplate->id,
            'class' => DummyAction::class,
            'label' => 'Restock',
            'config' => ['label' => ['Restock']],
            'data' => null,
        ]);

        Action::create([
            'template_id' => $authorsTemplate->id,
            'class' => DummyAction::class,
            'label' => 'Publish Profile',
            'config' => ['label' => ['Publish Profile']],
            'data' => null,
        ]);
    }
}
