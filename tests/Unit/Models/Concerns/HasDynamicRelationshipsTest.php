<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Opscale\NovaDynamicResources\Models\Enums\RelationshipCardinality;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Record;
use Opscale\NovaDynamicResources\Models\Template;

beforeEach(function (): void {
    $this->categories = Template::create([
        'label' => 'Categories',
        'singular_label' => 'Category',
        'uri_key' => 'categories',
        'title' => 'name',
        'type' => TemplateType::Dynamic,
    ]);

    $this->products = Template::create([
        'label' => 'Products',
        'singular_label' => 'Product',
        'uri_key' => 'products',
        'title' => 'name',
        'type' => TemplateType::Dynamic,
    ]);

    $this->products->relationships()->create([
        'name' => 'category',
        'label' => 'Category',
        'cardinality' => RelationshipCardinality::BelongsTo,
        'related_template_id' => $this->categories->id,
        'foreign_key' => 'category_id',
        'inverse_name' => 'products',
        'required' => false,
    ]);

    $this->category = Record::create([
        'template_id' => $this->categories->id,
        'data' => ['name' => 'Tools'],
    ]);

    $this->productA = Record::create([
        'template_id' => $this->products->id,
        'data' => ['name' => 'Hammer', 'category_id' => $this->category->id],
    ]);

    $this->productB = Record::create([
        'template_id' => $this->products->id,
        'data' => ['name' => 'Wrench', 'category_id' => $this->category->id],
    ]);
});

it('resolves a forward BelongsTo relationship via __call', function (): void {
    $product = Record::findOrFail($this->productA->id);

    $related = $product->category;

    expect($related)->toBeInstanceOf(Model::class);
    expect($related->id)->toBe($this->category->id);
});

it('resolves an inverse HasMany relationship via __call', function (): void {
    $category = Record::findOrFail($this->category->id);

    $children = $category->products;

    expect($children)->toBeInstanceOf(Collection::class);
    expect($children)->toHaveCount(2);
    expect($children->pluck('id')->all())
        ->toContain($this->productA->id, $this->productB->id);
});

it('reports dynamic relationship names through isRelation()', function (): void {
    $product = Record::findOrFail($this->productA->id);
    $category = Record::findOrFail($this->category->id);

    expect($product->isRelation('category'))->toBeTrue();
    expect($category->isRelation('products'))->toBeTrue();
    expect($product->isRelation('nope'))->toBeFalse();
});
