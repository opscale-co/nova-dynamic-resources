<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Casts;

use DateTime;
use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Opscale\NovaDynamicResources\Models\Template;
use Override;

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
    #[Override]
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        // Decode the JSON data
        $data = $this->decodeValue($value);

        /** @var Template|null $template */
        $template = $model->getAttribute('template');
        $fields = $template?->fields;

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
            $fieldConfig = Config::get('nova-dynamic-resources.fields.'.$field->type, null);

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
    #[Override]
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        $encoded = json_encode($value);

        return $encoded === false ? '{}' : $encoded;
    }

    /**
     * Decode the JSON value.
     *
     * @return array<string, mixed>
     */
    final protected function decodeValue(mixed $value): array
    {
        if (is_array($value)) {
            /** @var array<string, mixed> $value */
            return $value;
        }

        if (! is_string($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return [];
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * Cast a value to the specified type.
     */
    final protected function castValue(mixed $value, string $castType): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($castType) {
            'int', 'integer' => is_numeric($value) ? (int) $value : 0,
            'real', 'float', 'double' => is_numeric($value) ? (float) $value : 0.0,
            'decimal' => is_numeric($value) ? (string) $value : $value,
            'string' => is_scalar($value) ? (string) $value : '',
            'bool', 'boolean' => (bool) $value,
            'array', 'json' => is_string($value) ? json_decode($value, true) : $value,
            'collection' => Collection::make($this->toIterable($value)),
            'date', 'datetime' => $value instanceof DateTimeInterface
                ? $value
                : (is_string($value) ? new DateTime($value) : new DateTime),
            default => $value,
        };
    }

    /**
     * @return iterable<int|string, mixed>
     */
    final protected function toIterable(mixed $value): iterable
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_iterable($value) ? $value : [];
    }
}
