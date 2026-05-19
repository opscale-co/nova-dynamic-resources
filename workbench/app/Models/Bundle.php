<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Opscale\Validations\Validatable;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 */
class Bundle extends Model
{
    use Validatable;

    /**
     * @var array<string, list<string>>
     */
    public static array $validationRules = [
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * @return HasMany<Item>
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
