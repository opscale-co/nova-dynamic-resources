<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Models\Concerns;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait CastsValidationData
{
    /**
     * Decode JSON-cast columns so array validation rules see array values.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    final public function validationData(array $data): array
    {
        foreach ($this->getCasts() as $key => $cast) {
            if (! isset($data[$key]) || ! is_string($data[$key])) {
                continue;
            }

            if (! $this->isJsonCast($cast)) {
                continue;
            }

            $decoded = json_decode($data[$key], true);

            if (is_array($decoded)) {
                $data[$key] = $decoded;
            }
        }

        return $data;
    }

    private function isJsonCast(string $cast): bool
    {
        return in_array($cast, ['array', 'json', 'collection', 'object'], true)
            || str_starts_with($cast, 'collection:')
            || str_starts_with($cast, 'Opscale\\NovaDynamicResources\\Casts\\AsDynamicData');
    }
}
