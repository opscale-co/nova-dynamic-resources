<?php

namespace Opscale\NovaDynamicResources\Casts;

use DateTime;
use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * @implements CastsAttributes<array<string, mixed>, array<string, mixed>>
 */
class AsDynamicData implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        // Decode the JSON data
        $data = $this->decodeValue($value);

        // Get fields from the model
        $fields = $model->template?->fields;

        if ($fields === null || $fields->isEmpty()) {
            return $data;
        }

        // Apply casts to each field based on configuration
        foreach ($fields as $field) {
            $fieldName = $field->name;

            if (! array_key_exists($fieldName, $data)) {
                continue;
            }

            // Get the cast type from config
            /** @var array{cast?: string}|null $fieldConfig */
            $fieldConfig = Config::get('nova-dynamic-resources.fields.' . $field->type, null);

            if ($fieldConfig !== null && isset($fieldConfig['cast'])) {
                $data[$fieldName] = $this->castValue($data[$fieldName], $fieldConfig['cast']);
            }
        }

        return $data;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return json_encode($value);
    }

    /**
     * Decode the JSON value.
     *
     * @return array<string, mixed>
     */
    protected function decodeValue(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        return [];
    }

    /**
     * Cast a value to the specified type.
     */
    protected function castValue(mixed $value, string $castType): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($castType) {
            'int', 'integer' => (int) $value,
            'real', 'float', 'double' => (float) $value,
            'decimal' => is_numeric($value) ? (string) $value : $value,
            'string' => (string) $value,
            'bool', 'boolean' => (bool) $value,
            'array', 'json' => is_string($value) ? json_decode($value, true) : $value,
            'collection' => collect(is_string($value) ? json_decode($value, true) : $value),
            'date', 'datetime' => $value instanceof DateTimeInterface ? $value : new DateTime($value),
            default => $value,
        };
    }
}
