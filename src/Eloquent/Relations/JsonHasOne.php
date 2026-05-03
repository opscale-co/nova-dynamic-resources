<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Override;

/**
 * HasOne relation that stores the foreign key inside the related model's
 * `data` JSON column. Extends Eloquent's HasOne so Nova introspection works.
 */
class JsonHasOne extends HasOne
{
    public function __construct(Builder $query, Model $parent, string $foreignKey)
    {
        parent::__construct(
            query: $query,
            parent: $parent,
            foreignKey: $foreignKey,
            localKey: $parent->getKeyName(),
        );
    }

    #[Override]
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $this->query->where('data->'.$this->foreignKey, $this->parent->getKey());
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
            $key = $model->getKey();

            if ($key !== null && ! in_array($key, $ids, true)) {
                $ids[] = $key;
            }
        }

        $this->query->whereIn('data->'.$this->foreignKey, $ids ?: [null]);
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
        $dictionary = [];
        foreach ($results as $result) {
            $data = $result->getAttribute('data');

            if (! is_array($data)) {
                continue;
            }

            $key = $data[$this->foreignKey] ?? null;

            if (! is_scalar($key)) {
                continue;
            }

            $dictionary[(string) $key] = $result;
        }

        foreach ($models as $model) {
            $modelKey = $model->getKey();

            if (! is_scalar($modelKey)) {
                continue;
            }

            $key = (string) $modelKey;

            if (isset($dictionary[$key])) {
                $model->setRelation($relation, $dictionary[$key]);
            }
        }

        return $models;
    }

    #[Override]
    public function getResults(): ?Model
    {
        return $this->parent->getKey() === null ? null : $this->query->first();
    }
}
