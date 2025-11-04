<?php

namespace Opscale\NovaDynamicResources\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;

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
        $casts = [
            'data' => 'array',
            'metadata' => 'array',
        ];

        // Add dynamic casts for data fields based on resource configuration
        $resource = $this->resource;
        if ($resource !== null) {
            $fields = $resource->fields;

            foreach ($fields as $field) {
                $fieldType = $field->type;
                $fieldName = $field->name;

                /** @var array{cast?: string}|null $fieldConfig */
                $fieldConfig = Config::get('nova-dynamic-resources.fields.' . $fieldType, null);

                if ($fieldConfig !== null && isset($fieldConfig['cast'])) {
                    $casts[$fieldName] = $fieldConfig['cast'];
                }
            }
        }

        return $casts;
    }
}
