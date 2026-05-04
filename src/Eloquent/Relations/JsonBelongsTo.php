<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * BelongsTo relation that stores the foreign key inside the parent's `data` JSON column.
 *
 * Extends Eloquent's BelongsTo so Nova/relation introspection (getForeignKeyName,
 * getOwnerKeyName, etc.) works out of the box; only the constraint/match logic
 * is overridden to read the FK from `data[$foreignKey]`.
 */
class JsonBelongsTo extends BelongsTo
{
    public function __construct(Builder $query, Model $parent, string $foreignKey)
    {
        parent::__construct(
            query: $query,
            child: $parent,
            foreignKey: $foreignKey,
            ownerKey: $query->getModel()->getKeyName(),
            relationName: $foreignKey,
        );
    }

    #[Override]
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $value = $this->getForeignKeyFrom($this->parent);
            $this->query->where($this->related->getQualifiedKeyName(), $value);
        }
    }

    /**
     * @param  array<int, Model>  $models
     */
    #[Override]
    public function addEagerConstraints(array $models): void
    {
        $ids = [];
        foreach ($models as $model) {
            $value = $this->getForeignKeyFrom($model);

            if ($value !== null && ! in_array($value, $ids, true)) {
                $ids[] = $value;
            }
        }

        $this->query->whereIn($this->related->getQualifiedKeyName(), $ids ?: [null]);
    }

    /**
     * @param  array<int, Model>  $models
     * @return array<int, Model>
     */
    #[Override]
    public function initRelation(array $models, $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }

        return $models;
    }

    /**
     * @param  array<int, Model>  $models
     * @param  Collection<int, Model>  $results
     * @return array<int, Model>
     */
    #[Override]
    public function match(array $models, Collection $results, $relation): array
    {
        $key = $this->related->getKeyName();
        $dictionary = [];

        foreach ($results as $result) {
            $resultKey = $result->getAttribute($key);

            if (is_scalar($resultKey)) {
                $dictionary[(string) $resultKey] = $result;
            }
        }

        foreach ($models as $model) {
            $value = $this->getForeignKeyFrom($model);

            if (is_scalar($value) && isset($dictionary[(string) $value])) {
                $model->setRelation($relation, $dictionary[(string) $value]);
            }
        }

        return $models;
    }

    #[Override]
    public function getResults(): ?Model
    {
        $value = $this->getForeignKeyFrom($this->parent);

        return $value === null ? null : $this->query->first();
    }

    final protected function getForeignKeyFrom(Model $model): mixed
    {
        $data = $model->getAttribute('data');

        if (! is_array($data)) {
            return null;
        }

        return $data[$this->foreignKey] ?? null;
    }
}
