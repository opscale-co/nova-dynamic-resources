<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Models\Repositories;

use Opscale\NovaDynamicResources\Models\Relationship;

final class RelationshipResolver
{
    final public static function findInverse(string $relatedTemplateId, string $inverseName): ?Relationship
    {
        return Relationship::query()
            ->where('related_template_id', $relatedTemplateId)
            ->where('inverse_name', $inverseName)
            ->first();
    }
}
