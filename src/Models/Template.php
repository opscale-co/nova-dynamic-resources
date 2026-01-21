<?php

namespace Opscale\NovaDynamicResources\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Opscale\NovaDynamicResources\Models\Concerns\HasDynamicData;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Repositories\TemplateRepository;

class Template extends Model
{
    use HasDynamicData;
    use HasUlids;
    use SoftDeletes;
    use TemplateRepository;
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
            'required',
            'string',
            'min:1',
            'max:255',
        ],
        'uri_key' => [
            'required',
            'string',
            'min:1',
            'max:255',
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'unique:dynamic_resources_templates,uri_key',
        ],
        'title' => [
            'required',
            'string',
            'min:1',
            'max:255',
        ],
        'type' => [
            'required',
            'string',
        ],
        'related_class' => [
            'nullable',
            'string',
            'max:255',
            'unique:dynamic_resources_templates,related_class',
        ],
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dynamic_resources_templates';

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
        'type',
        'related_class',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => TemplateType::class,
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
     * Get the fields for the template.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(Field::class);
    }

    /**
     * Get the actions for the template.
     */
    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }
}
