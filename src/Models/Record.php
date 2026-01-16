<?php

namespace Opscale\NovaDynamicResources\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Opscale\NovaDynamicResources\Models\Concerns\HasDynamicData;

class Record extends Model
{
    use HasDynamicData;
    use HasUlids;
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
     * The relationships that should always be loaded.
     *
     * @var array<int, string>
     */
    protected $with = [
        'template.fields',
        'template.actions',
    ];

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

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::retrieved(function (Record $record) {
            $record->loadAppends();
        });
    }

    /**
     * Get the fields for the record (from template).
     *
     * @return Collection<int, Field>
     */
    public function fields(): Collection
    {
        return $this->template?->fields ?? new Collection;
    }

    /**
     * Get the template that this record belongs to.
     *
     * @return BelongsTo<Template, $this>
     */
    final public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id', 'id');
    }

    /**
     * Load dynamic appends from template fields.
     */
    protected function loadAppends(): void
    {
        $fieldNames = $this->template->fields->pluck('name')->toArray();
        $this->appends = array_merge($this->appends, $fieldNames);
    }
}
