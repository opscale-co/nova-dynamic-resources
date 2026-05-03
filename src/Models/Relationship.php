<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Opscale\NovaDynamicResources\Models\Concerns\CastsValidationData;
use Opscale\NovaDynamicResources\Models\Enums\RelationshipCardinality;
use Opscale\NovaDynamicResources\Models\Repositories\RelationshipRepository;
use Opscale\Validations\Validatable;

/**
 * @property string $id
 * @property string $template_id
 * @property string $name
 * @property string $label
 * @property RelationshipCardinality $cardinality
 * @property string $related_template_id
 * @property string $foreign_key
 * @property string|null $inverse_name
 * @property bool $required
 * @property array<int|string, mixed>|null $rules
 * @property array<string, mixed>|null $config
 * @property-read Template|null $template
 * @property-read Template|null $relatedTemplate
 */
class Relationship extends Model
{
    use CastsValidationData;
    use HasUlids;
    use RelationshipRepository;
    use SoftDeletes;
    use Validatable;

    /**
     * @var array<string, array<int, string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    public static array $validationRules = [
        'template_id' => [
            'required',
            'ulid',
            'exists:dynamic_resources_templates,id',
        ],
        'name' => [
            'required',
            'string',
            'max:255',
            'regex:/^[a-z][a-zA-Z0-9_]*$/',
        ],
        'label' => [
            'required',
            'string',
            'max:255',
        ],
        'cardinality' => [
            'required',
            'string',
        ],
        'related_template_id' => [
            'required',
            'ulid',
            'exists:dynamic_resources_templates,id',
        ],
        'foreign_key' => [
            'required',
            'string',
            'max:255',
            'regex:/^[a-z][a-z0-9_]*$/',
        ],
        'inverse_name' => [
            'nullable',
            'string',
            'max:255',
            'regex:/^[a-z][a-zA-Z0-9_]*$/',
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
    ];

    /**
     * @var string
     */
    protected $table = 'dynamic_resources_relationships';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'template_id',
        'name',
        'label',
        'cardinality',
        'related_template_id',
        'foreign_key',
        'inverse_name',
        'required',
        'rules',
        'config',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'cardinality' => RelationshipCardinality::class,
        'required' => 'boolean',
        'rules' => 'array',
        'config' => 'array',
    ];

    /**
     * @return BelongsTo<Template, $this>
     */
    final public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * @return BelongsTo<Template, $this>
     */
    final public function relatedTemplate(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'related_template_id');
    }
}
