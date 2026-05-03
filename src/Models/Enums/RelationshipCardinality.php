<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Models\Enums;

enum RelationshipCardinality: string
{
    case BelongsTo = 'BelongsTo';
    case HasOne = 'HasOne';
    case HasMany = 'HasMany';
}
