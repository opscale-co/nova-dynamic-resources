<?php

namespace Opscale\NovaDynamicResources\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Opscale\NovaDynamicResources\Models\Repositories\DynamicResourceRepository;

class DynamicResource extends Model
{
    use DynamicResourceRepository;
    use HasUlids;
    use SoftDeletes;
    use ValidatorTrait;

    /**
     * The validation rules for the model.
     *
     * @var array<string, array<int, string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    public array $validationRules = [
        'label' => [
            'required',
            'string',
            'min:1',
            'max:255',
        ],
        'singular_label' => [
            'nullable',
            'string',
            'min:1',
            'max:255',
        ],
        'uri_key' => [
            'nullable',
            'string',
            'min:1',
            'max:255',
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'unique:dynamic_resources,uri_key',
        ],
        'title' => [
            'required',
            'string',
            'min:1',
            'max:255',
        ],
        'base_class' => [
            'nullable',
            'string',
            'max:255',
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'singular_label',
        'label',
        'uri_key',
        'title',
        'base_class',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array<int, string>
     */
    protected $with = [
        'fields',
        'actions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the fields for the resource.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(DynamicField::class, 'resource_id');
    }

    /**
     * Get the actions for the resource.
     */
    public function actions(): HasMany
    {
        return $this->hasMany(DynamicAction::class, 'resource_id');
    }
}
