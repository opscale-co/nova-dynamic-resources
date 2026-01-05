<?php

namespace Opscale\NovaDynamicResources\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Opscale\NovaDynamicResources\Casts\AsDynamicData;

class DynamicRecord extends Model
{
    use HasUlids;
    use ValidatorTrait;

    /**
     * The validation rules for the model.
     *
     * @var array<string, array<int, string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    public array $validationRules = [
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

    /**
     * The relationships that should always be loaded.
     *
     * @var array<int, string>
     */
    protected $with = [
        'resource',
    ];

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
            'data' => AsDynamicData::class,
            'metadata' => 'array',
        ];
    }

    /**
     * Check if the model has a given attribute.
     */
    protected function hasAttribute(string $key): bool
    {
        return in_array($key, $this->fillable, true)
            || array_key_exists($key, $this->attributes)
            || $this->hasGetMutator($key)
            || $this->hasCast($key);
    }

    /**
     * Get an attribute from the model, checking data and metadata.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        $value = parent::__get($key);

        if ($value !== null) {
            return $value;
        }

        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        if (isset($this->metadata[$key])) {
            return $this->metadata[$key];
        }

        return $value;
    }

    /**
     * Set an attribute on the model, storing in data or metadata if applicable.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        if ($this->hasAttribute($key) || $this->isRelation($key) || $this->hasSetMutator($key)) {
            parent::__set($key, $value);

            return;
        }

        $data = $this->data ?? [];
        $metadata = $this->metadata ?? [];

        if (array_key_exists($key, $metadata)) {
            $metadata[$key] = $value;
            $this->attributes['metadata'] = $metadata;

            return;
        }

        $data[$key] = $value;
        $this->attributes['data'] = $data;
    }
}
