<?php

namespace Workbench\App\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Model;
use Opscale\NovaDynamicResources\Models\Concerns\UsesTemplate;

/**
 * @property int $id
 * @property int $template_id
 * @property array|null $data
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property int $stock
 */
class Item extends Model
{
    use UsesTemplate, ValidatorTrait;

    /**
     * @var array<string, list<string>>
     */
    public array $validationRules = [
        'template_id' => ['required', 'exists:dynamic_resources_templates,id'],
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'price' => ['required', 'numeric', 'min:0'],
        'stock' => ['required', 'integer', 'min:0'],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'template_id',
        'name',
        'description',
        'price',
        'stock',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'data' => 'array',
    ];
}
