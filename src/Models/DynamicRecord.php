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
     * Check if the key is a base database attribute.
     *
     * @param  string  $key
     */
    public function isBaseAttribute($key): bool
    {
        return in_array($key, [
            $this->getKeyName(),
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
            'resource_id',
            'data',
            'metadata',
        ], true);
    }

    /**
     * Get an attribute from the model, checking data or metadata based on appends.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if ($this->isBaseAttribute($key) ||
            $this->isRelation($key) ||
            $this->hasGetMutator($key)) {
            return parent::getAttribute($key);
        }

        if (in_array($key, $this->appends, true)) {
            $metadata = $this->getRawMetadata();

            return $metadata[$key] ?? null;
        } else {
            $data = $this->getRawData();

            return $data[$key] ?? null;
        }
    }

    /**
     * Set an attribute on the model, storing in data or metadata based on appends.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if ($this->isBaseAttribute($key) ||
            $this->isRelation($key) ||
            $this->hasSetMutator($key)) {
            return parent::setAttribute($key, $value);
        }

        if (in_array($key, $this->appends, true)) {
            $metadata = $this->getRawMetadata();
            $metadata[$key] = $value;
            $this->attributes['metadata'] = json_encode($metadata);
        } else {
            $data = $this->getRawData();
            $data[$key] = $value;
            $this->attributes['data'] = json_encode($data);
        }

        return $this;
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
     * Get raw data without triggering casts.
     *
     * @return array<string, mixed>
     */
    protected function getRawData(): array
    {
        $raw = $this->attributes['data'] ?? null;

        if ($raw === null) {
            return [];
        }

        if (is_array($raw)) {
            return $raw;
        }

        return json_decode($raw, true) ?? [];
    }

    /**
     * Get raw metadata without triggering casts.
     *
     * @return array<string, mixed>
     */
    protected function getRawMetadata(): array
    {
        $raw = $this->attributes['metadata'] ?? null;

        if ($raw === null) {
            return [];
        }

        if (is_array($raw)) {
            return $raw;
        }

        return json_decode($raw, true) ?? [];
    }
}
