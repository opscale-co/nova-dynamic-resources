<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Opscale\NovaDynamicResources\Models\Concerns\UsesTemplate;
use Opscale\Validations\Validatable;

/**
 * @property int $id
 * @property string|null $uuid
 * @property int $template_id
 * @property int|null $bundle_id
 * @property array|null $data
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property int $stock
 */
class Item extends Model
{
    use UsesTemplate, Validatable;

    /**
     * @var array<string, list<string>>
     */
    public static array $validationRules = [
        'template_id' => ['required', 'exists:dynamic_resources_templates,id'],
        'bundle_id' => ['nullable', 'exists:bundles,id'],
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
        'bundle_id',
        'uuid',
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

    protected static function booted(): void
    {
        // Backfill uuid for rows created without one so Nova's Repeater
        // HasMany preset can diff them via uniqueField('uuid').
        static::creating(function (self $item): void {
            if ($item->uuid === null) {
                $item->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * @return BelongsTo<Bundle, self>
     */
    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }
}
