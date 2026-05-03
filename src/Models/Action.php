<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Opscale\NovaDynamicResources\Models\Concerns\CastsValidationData;
use Opscale\Validations\Validatable;

/**
 * @property string $id
 * @property string $template_id
 * @property string $class
 * @property string $label
 * @property array<string, mixed>|null $config
 * @property array<string, mixed>|null $data
 * @property-read Template|null $template
 */
class Action extends Model
{
    use CastsValidationData;
    use HasUlids;
    use SoftDeletes;
    use Validatable;

    /**
     * The validation rules for the model.
     *
     * @var array<string, array<int, string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    public static array $validationRules = [
        'template_id' => [
            'required',
            'ulid',
            'exists:dynamic_resources_templates,id',
        ],
        'class' => [
            'required',
            'string',
            'max:255',
        ],
        'label' => [
            'required',
            'string',
            'max:255',
        ],
        'config' => [
            'nullable',
            'array',
        ],
        'data' => [
            'nullable',
            'array',
        ],
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dynamic_resources_actions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'template_id',
        'class',
        'label',
        'config',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'config' => 'array',
        'data' => 'array',
    ];

    /**
     * Get the template that owns this action.
     *
     * @return BelongsTo<Template, $this>
     */
    final public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
