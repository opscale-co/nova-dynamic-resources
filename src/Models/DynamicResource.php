<?php

namespace Opscale\NovaDynamicResources\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Opscale\NovaDynamicResources\Models\Rules\JsonSchemaRule;

class DynamicResource extends Model
{
    use HasUlids;
    use SoftDeletes;
    use ValidatorTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'singular_label',
        'label',
        'uri_key',
        'fields',
        'actions',
    ];

    /**
     * The validation rules for the model.
     *
     * @return array<string, array<int, string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    final public function validationRules(): array
    {
        return [
            'singular_label' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'label' => [
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
                'unique:dynamic_resources,uri_key',
            ],
            'fields' => [
                'required',
                JsonSchemaRule::make('fields'),
            ],
            'actions' => [
                'nullable',
                JsonSchemaRule::make('actions'),
            ],
        ];
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
            'fields' => 'array',
            'actions' => 'array',
        ];
    }
}
