<?php

declare(strict_types=1);

use Opscale\NovaDynamicResources\Models\Action;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Field;
use Opscale\NovaDynamicResources\Models\Template;

it('persists a template with required attributes', function (): void {
    $template = Template::create([
        'label' => 'Articles',
        'singular_label' => 'Article',
        'uri_key' => 'articles',
        'title' => 'title',
        'type' => TemplateType::Dynamic,
    ]);

    expect($template->id)->toBeString()
        ->and($template->label)->toBe('Articles')
        ->and($template->singular_label)->toBe('Article')
        ->and($template->uri_key)->toBe('articles')
        ->and($template->type)->toBe(TemplateType::Dynamic);
});

it('auto-populates uri_key from label when not set', function (): void {
    $template = Template::create([
        'label' => 'Blog Posts',
        'singular_label' => 'Blog Post',
        'title' => 'title',
        'type' => TemplateType::Dynamic,
    ]);

    expect($template->uri_key)->toBe('blog-posts');
});

it('auto-populates singular_label from label when not set', function (): void {
    $template = Template::create([
        'label' => 'Categories',
        'uri_key' => 'categories',
        'title' => 'name',
        'type' => TemplateType::Dynamic,
    ]);

    expect($template->singular_label)->toBe('Category');
});

it('has many fields', function (): void {
    $template = Template::create([
        'label' => 'Items',
        'singular_label' => 'Item',
        'uri_key' => 'items',
        'title' => 'name',
        'type' => TemplateType::Dynamic,
    ]);

    $template->fields()->create([
        'type' => 'name',
        'label' => 'Name',
        'name' => 'name',
        'required' => true,
        'display_in_index' => true,
    ]);

    expect($template->fields)->toHaveCount(1)
        ->and($template->fields->first())->toBeInstanceOf(Field::class);
});

it('has many actions', function (): void {
    $template = Template::create([
        'label' => 'Tickets',
        'singular_label' => 'Ticket',
        'uri_key' => 'tickets',
        'title' => 'subject',
        'type' => TemplateType::Dynamic,
    ]);

    $template->actions()->create([
        'class' => 'App\\Actions\\Close',
        'label' => 'Close',
    ]);

    expect($template->actions)->toHaveCount(1)
        ->and($template->actions->first())->toBeInstanceOf(Action::class);
});

it('only includes Dynamic and Inherited via instantiables scope', function (): void {
    Template::create([
        'label' => 'Dynamics',
        'singular_label' => 'Dynamic',
        'uri_key' => 'dynamics',
        'title' => 'name',
        'type' => TemplateType::Dynamic,
    ]);

    Template::create([
        'label' => 'Inheriteds',
        'singular_label' => 'Inherited',
        'uri_key' => 'inheriteds',
        'title' => 'name',
        'type' => TemplateType::Inherited,
        'related_class' => 'App\\Nova\\User',
    ]);

    Template::create([
        'label' => 'Composites',
        'singular_label' => 'Composite',
        'uri_key' => 'composites',
        'title' => 'name',
        'type' => TemplateType::Composited,
        'related_class' => 'App\\Nova\\User',
    ]);

    $instantiables = Template::instantiables()->get();

    expect($instantiables)->toHaveCount(2)
        ->and($instantiables->pluck('type')->all())
        ->toContain(TemplateType::Dynamic, TemplateType::Inherited)
        ->not->toContain(TemplateType::Composited);
});
