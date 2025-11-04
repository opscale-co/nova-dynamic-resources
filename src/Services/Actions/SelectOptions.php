<?php

namespace Opscale\NovaDynamicResources\Services\Actions;

use Lorisleiva\Actions\Action;
use Opscale\NovaCatalogs\Models\Catalog;

class SelectOptions extends Action
{
    /**
     * Get select options from a catalog based on resource and field.
     *
     * @return array<string, string>
     */
    public function handle(?string $catalog = null): array
    {
        return Catalog::options($catalog);
    }
}
