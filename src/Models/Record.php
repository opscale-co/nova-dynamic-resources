<?php

namespace Opscale\NovaDynamicResources\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Opscale\NovaDynamicResources\Models\Concerns\UsesTemplate;

class Record extends Model
{
    use HasUlids;
    use UsesTemplate;
    use ValidatorTrait;

    /**
     * The validation rules for the model.
     *
     * @var array<string, array<int, string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    public array $validationRules = [
        'template_id' => [
            'required',
            'string',
            'exists:dynamic_resources_templates,id',
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
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dynamic_resources_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'template_id',
        'data',
        'metadata',
    ];
}
