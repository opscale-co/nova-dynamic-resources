<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Models\Repositories;

use Illuminate\Support\Str;
use Opscale\NovaDynamicResources\Models\Relationship;

trait RelationshipRepository
{
    final public static function bootRelationshipRepository(): void
    {
        static::creating(function (Relationship $model): void {
            if (empty($model->name) && ! empty($model->label)) {
                $model->name = Str::slug($model->label, '_');
            }

            if (empty($model->foreign_key) && ! empty($model->name)) {
                $model->foreign_key = $model->name.'_id';
            }
        });
    }
}
