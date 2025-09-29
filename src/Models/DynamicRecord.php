<?php

namespace Opscale\NovaDynamicResources\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DynamicRecord extends Model
{
    use HasUlids;
    use ValidatorTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'resource_id',
        'data',
        'metadata',
    ];

    /**
     * The validation rules for the model.
     *
     * @return array<string, array<int, string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    final public function validationRules(): array
    {
        return [
            'resource_id' => [
                'required',
                'string',
                'exists:dynamic_resources,id',
            ],
            'data' => [
                'required',
                'array',
            ],
            'metadata' => [
                'nullable',
                'array',
            ],
        ];
    }

    /**
     * Get the dynamic resource that this record belongs to.
     *
     * @return BelongsTo<DynamicResource, $this>
     */
    final public function resource(): BelongsTo
    {
        return $this->belongsTo(DynamicResource::class, 'resource_id', 'id');
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     *
     * @phpstan-ignore solid.lsp.parentCall
     */
    final protected function casts(): array
    {
        return [
            'data' => 'array',
            'metadata' => 'array',
        ];
    }
}
