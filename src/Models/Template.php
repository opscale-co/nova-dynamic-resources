<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Opscale\NovaDynamicResources\Models\Concerns\CastsValidationData;
use Opscale\NovaDynamicResources\Models\Concerns\HasDynamicData;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Repositories\TemplateRepository;
use Opscale\Validations\Validatable;

/**
 * @property string $id
 * @property TemplateType $type
 * @property string|null $related_class
 * @property string $singular_label
 * @property string $label
 * @property string $uri_key
 * @property string|null $title
 * @property array<string, mixed>|null $data
 * @property-read EloquentCollection<int, Field> $fields
 * @property-read EloquentCollection<int, Action> $actions
 * @property-read EloquentCollection<int, Relationship> $relationships
 */
class Template extends Model
{
    use CastsValidationData;
    use HasDynamicData;
    use HasUlids;
    use SoftDeletes;
    use TemplateRepository;
    use Validatable;

    /**
     * The validation rules for the model.
     *
     * @var array<string, array<int, string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    public static array $validationRules = [
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
     * @var list<string>
     */
    protected $with = [
        'fields',
        'actions',
        'relationships',
    ];

    /**
     * Get the fields for the template.
     *
     * @return HasMany<Field, $this>
     */
    final public function fields(): HasMany
    {
        return $this->hasMany(Field::class);
    }

    /**
     * Get the actions for the template.
     *
     * @return HasMany<Action, $this>
     */
    final public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    /**
     * Get the relationships for the template.
     *
     * @return HasMany<Relationship, $this>
     */
    final public function relationships(): HasMany
    {
        return $this->hasMany(Relationship::class);
    }
}
