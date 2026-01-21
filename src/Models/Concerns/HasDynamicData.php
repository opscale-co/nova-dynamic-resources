<?php

namespace Opscale\NovaDynamicResources\Models\Concerns;

use Opscale\NovaDynamicResources\Casts\AsDynamicData;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasDynamicData
{
    /**
     * Initialize the HasDynamicData trait.
     */
    public function initializeHasDynamicData(): void
    {
        $this->casts = array_merge($this->casts ?? [], ['data' => AsDynamicData::class]);
    }

    /**
     * Determine if the given key is cast.
     *
     * @param  string  $key
     * @param  array|string|null  $types
     * @return bool
     */
    public function hasCast($key, $types = null)
    {
        if (in_array($key, $this->appends ?? [], true)) {
            return true;
        }

        return parent::hasCast($key, $types);
    }

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        if (in_array($key, $this->appends ?? [], true)) {
            return true;
        }

        return parent::hasGetMutator($key);
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasSetMutator($key)
    {
        if (in_array($key, $this->appends ?? [], true)) {
            return true;
        }

        return parent::hasSetMutator($key);
    }

    /**
     * Get a dynamic data value by key (with casts applied).
     */
    public function getData(string $key, mixed $default = null): mixed
    {
        $data = $this->data ?? [];

        return $data[$key] ?? $default;
    }

    /**
     * Set a dynamic data value by key.
     */
    public function setData(string $key, mixed $value): static
    {
        $data = $this->getRawData();
        $data[$key] = $value;
        $this->attributes['data'] = json_encode($data);

        return $this;
    }

    /**
     * Check if a dynamic data key exists.
     */
    public function hasData(string $key): bool
    {
        $data = $this->data ?? [];

        return array_key_exists($key, $data);
    }

    /**
     * Remove a dynamic data key.
     */
    public function removeData(string $key): static
    {
        $data = $this->getRawData();
        unset($data[$key]);
        $this->attributes['data'] = json_encode($data);

        return $this;
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        if (in_array($key, $this->appends ?? [], true)) {
            return $this->getData($key);
        }

        return parent::mutateAttribute($key, $value);
    }

    /**
     * Set the value of an attribute using its mutator.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function setMutatedAttributeValue($key, $value)
    {
        if (in_array($key, $this->appends ?? [], true)) {
            return $this->setData($key, $value);
        }

        return parent::setMutatedAttributeValue($key, $value);
    }

    /**
     * Get raw data without triggering casts.
     *
     * @return array<string, mixed>
     */
    protected function getRawData(): array
    {
        $raw = $this->attributes['data'] ?? null;

        if ($raw === null) {
            return [];
        }

        if (is_array($raw)) {
            return $raw;
        }

        return json_decode($raw, true) ?? [];
    }
}
