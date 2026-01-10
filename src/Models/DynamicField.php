<?php

namespace Opscale\NovaDynamicResources\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Opscale\NovaDynamicResources\Models\Repositories\DynamicFieldRepository;

class DynamicField extends Model
{
    use DynamicFieldRepository;
    use HasUlids;
    use SoftDeletes;
    use ValidatorTrait;

    /**
     * The validation rules for the model.
     *
     * @var array<string, array<int, string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    public array $validationRules = [
        'resource_id' => [
            'required',
            'ulid',
            'exists:dynamic_resources,id',
        ],
        'type' => [
            'required',
            'string',
            'max:255',
        ],
        'label' => [
            'required',
            'string',
            'max:255',
        ],
        'name' => [
            'required',
            'string',
            'max:255',
        ],
        'required' => [
            'required',
            'boolean',
        ],
        'rules' => [
            'nullable',
            'array',
        ],
        'config' => [
            'nullable',
            'array',
        ],
        'hooks' => [
            'nullable',
            'array',
        ],
        'metadata' => [
            'nullable',
            'array',
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'resource_id',
        'type',
        'label',
        'name',
        'required',
        'rules',
        'config',
        'hooks',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rules' => 'array',
        'config' => 'array',
        'hooks' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get an attribute from the model, checking metadata for appended attributes.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (in_array($key, $this->appends, true)) {
            $metadata = $this->getRawMetadata();

            return $metadata[$key] ?? null;
        }

        return parent::getAttribute($key);
    }

    /**
     * Set an attribute on the model, storing appended attributes in metadata.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->appends, true)) {
            $metadata = $this->getRawMetadata();
            $metadata[$key] = $value;
            $this->attributes['metadata'] = json_encode($metadata);

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Get the resource that owns the field.
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(DynamicResource::class, 'resource_id');
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
