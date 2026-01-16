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
        $property = $this->getDynamicProperty();
        $this->casts = array_merge($this->casts ?? [], [$property => AsDynamicData::class]);
    }

    /**
     * Get the attribute name for dynamic data storage.
     */
    public function getDynamicProperty(): string
    {
        return 'data';
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
    public function getDynamicData(string $key, mixed $default = null): mixed
    {
        $property = $this->getDynamicProperty();
        $data = $this->{$property} ?? [];

        return $data[$key] ?? $default;
    }

    /**
     * Set a dynamic data value by key.
     */
    public function setDynamicData(string $key, mixed $value): static
    {
        $property = $this->getDynamicProperty();
        $data = $this->getRawData();
        $data[$key] = $value;
        $this->attributes[$property] = json_encode($data);

        return $this;
    }

    /**
     * Check if a dynamic data key exists.
     */
    public function hasDynamicData(string $key): bool
    {
        $property = $this->getDynamicProperty();
        $data = $this->{$property} ?? [];

        return array_key_exists($key, $data);
    }

    /**
     * Remove a dynamic data key.
     */
    public function removeDynamicData(string $key): static
    {
        $property = $this->getDynamicProperty();
        $data = $this->getRawData();
        unset($data[$key]);
        $this->attributes[$property] = json_encode($data);

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
            return $this->getDynamicData($key);
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
            return $this->setDynamicData($key, $value);
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
        $property = $this->getDynamicProperty();
        $raw = $this->attributes[$property] ?? null;

        if ($raw === null) {
            return [];
        }

        if (is_array($raw)) {
            return $raw;
        }

        return json_decode($raw, true) ?? [];
    }
}
