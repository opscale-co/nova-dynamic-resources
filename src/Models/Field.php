<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Opscale\NovaDynamicResources\Models\Concerns\CastsValidationData;
use Opscale\NovaDynamicResources\Models\Repositories\FieldRepository;
use Opscale\Validations\Validatable;

/**
 * @property string $id
 * @property string $template_id
 * @property string $type
 * @property string $label
 * @property string $name
 * @property bool $required
 * @property bool $display_in_index
 * @property array<int|string, mixed>|null $rules
 * @property array<string, mixed>|null $config
 * @property array<string, mixed>|null $hooks
 * @property array<string, mixed>|null $data
 * @property-read Template|null $template
 */
class Field extends Model
{
    use CastsValidationData;
    use FieldRepository;
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
        'display_in_index' => [
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
    protected $table = 'dynamic_resources_fields';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'template_id',
        'type',
        'label',
        'name',
        'required',
        'display_in_index',
        'rules',
        'config',
        'hooks',
        'data',
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
        'data' => 'array',
    ];

    /**
     * Get the template that owns this field.
     *
     * @return BelongsTo<Template, $this>
     */
    final public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
