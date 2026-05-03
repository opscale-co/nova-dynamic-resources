<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Opscale\NovaDynamicResources\Models\Concerns\CastsValidationData;
use Opscale\NovaDynamicResources\Models\Concerns\UsesTemplate;
use Opscale\Validations\Validatable;

/**
 * @property string $id
 * @property string $template_id
 * @property array<string, mixed> $data
 * @property array<string, mixed>|null $metadata
 * @property-read Template|null $template
 */
class Record extends Model
{
    use CastsValidationData;
    use HasUlids;
    use UsesTemplate;
    use Validatable;

    /**
     * The validation rules for the model.
     *
     * @var array<string, array<int, string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    public static array $validationRules = [
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
