<?php

declare(strict_types=1);

use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Record;
use Opscale\NovaDynamicResources\Models\Template;
use Opscale\NovaDynamicResources\Nova\Repeatables\Record as RecordRepeatable;

beforeEach(function (): void {
    Template::query()->delete();
});

it('generates one Repeatable per Composited template, each with a unique key and template-driven labels', function (): void {
    $book = Template::create([
        'label' => 'Books',
        'singular_label' => 'Book',
        'uri_key' => 'books',
        'title' => 'title',
        'type' => TemplateType::Composited,
        'related_class' => 'App\\Nova\\LineItem',
    ]);

    $author = Template::create([
        'label' => 'Authors',
        'singular_label' => 'Author',
        'uri_key' => 'authors',
        'title' => 'title',
        'type' => TemplateType::Composited,
        'related_class' => 'App\\Nova\\LineItem',
    ]);

    $repeatables = RecordRepeatable::repeatablesFor(Record::class);

    expect($repeatables)->toHaveCount(2);

    [$first, $second] = [$repeatables[0], $repeatables[1]];

    expect($first::key())->toBe('record-'.$author->id);
    expect($first::label())->toBe('Authors');
    expect($first::singularLabel())->toBe('Author');

    expect($second::key())->toBe('record-'.$book->id);
    expect($second::label())->toBe('Books');
    expect($second::singularLabel())->toBe('Book');

    expect($first::key())->not->toBe($second::key());
    expect(get_class($first))->not->toBe(get_class($second));
});

it('skips templates that are not Composited', function (): void {
    Template::create([
        'label' => 'Dynamics',
        'singular_label' => 'Dynamic',
        'uri_key' => 'dynamics',
        'title' => 'title',
        'type' => TemplateType::Dynamic,
        'related_class' => null,
    ]);

    Template::create([
        'label' => 'Inheriteds',
        'singular_label' => 'Inherited',
        'uri_key' => 'inheriteds',
        'title' => 'title',
        'type' => TemplateType::Inherited,
        'related_class' => 'App\\Nova\\Record',
    ]);

    expect(RecordRepeatable::repeatablesFor(Record::class))->toBe([]);
});

it('applies the consumer filter closure to the query', function (): void {
    Template::create([
        'label' => 'Books',
        'singular_label' => 'Book',
        'uri_key' => 'books',
        'title' => 'title',
        'type' => TemplateType::Composited,
        'related_class' => 'App\\Nova\\LineItem',
    ]);

    Template::create([
        'label' => 'Widgets',
        'singular_label' => 'Widget',
        'uri_key' => 'widgets',
        'title' => 'title',
        'type' => TemplateType::Composited,
        'related_class' => 'App\\Nova\\Other',
    ]);

    $repeatables = RecordRepeatable::repeatablesFor(
        Record::class,
        fn ($query): mixed => $query->where('related_class', 'App\\Nova\\LineItem'),
    );

    expect($repeatables)->toHaveCount(1);
    expect($repeatables[0]::singularLabel())->toBe('Book');
});

it('renders the templates dynamic fields plus a hidden template_id default', function (): void {
    $template = Template::create([
        'label' => 'Notes',
        'singular_label' => 'Note',
        'uri_key' => 'notes',
        'title' => 'title',
        'type' => TemplateType::Composited,
        'related_class' => 'App\\Nova\\LineItem',
    ]);

    $template->fields()->create([
        'type' => 'name',
        'label' => 'Heading',
        'name' => 'heading',
        'required' => true,
        'display_in_index' => true,
    ]);

    $repeatable = RecordRepeatable::repeatablesFor(Record::class)[0];

    $fields = $repeatable->fields(new NovaRequest);

    $hidden = null;
    foreach ($fields as $field) {
        if ($field instanceof Hidden && $field->attribute === 'template_id') {
            $hidden = $field;
            break;
        }
    }

    expect($hidden)->toBeInstanceOf(Hidden::class);
    expect($hidden?->resolveDefaultCallback(new NovaRequest))->toBe($template->id);

    $attributes = array_map(static fn ($field): string => $field->attribute, $fields);
    expect($attributes)->toContain('data->heading');
});
