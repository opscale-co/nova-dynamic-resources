<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Nova\Fields;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Repeater;
use Laravel\Nova\Resource;
use Opscale\NovaDynamicResources\Models\Template;
use Opscale\NovaDynamicResources\Nova\Repeatables\Record;

/**
 * Repeater preconfigured to render one Repeatable per Composited Template
 * whose related_class matches the host's child Nova resource. Replaces
 * the two-step "build array + pass to ->repeatables()" call with a
 * single fluent ->forResource() on the field itself.
 *
 *     TemplatedRepeater::make('Line Items', 'items')
 *         ->forResource(\App\Nova\LineItem::class)
 *         ->asHasMany(\App\Nova\LineItem::class)
 *         ->uniqueField('uuid');
 */
class TemplatedRepeater extends Repeater
{
    /**
     * Populate the repeatables list with one Record subclass per
     * Composited Template whose related_class matches the given Nova
     * resource. The optional $filter closure is applied on top of the
     * related_class match for advanced cases (sub-filter by tag, ACL,
     * etc.).
     *
     * @param  class-string<resource<Model>>  $novaResource
     * @param  (Closure(Builder<Template>): mixed)|null  $filter
     */
    final public function forResource(string $novaResource, ?Closure $filter = null): self
    {
        /** @var class-string<Model> $childModel */
        $childModel = $novaResource::$model;

        $combined = function (Builder $query) use ($novaResource, $filter): void {
            $query->where('related_class', $novaResource);
            if ($filter !== null) {
                $filter($query);
            }
        };

        return $this->repeatables(
            Record::repeatablesFor($childModel, $combined)
        );
    }
}
