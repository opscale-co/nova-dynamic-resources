<?php

namespace Opscale\NovaDynamicResources\Services\Actions;

use Opscale\Actions\Action;
use Opscale\NovaCatalogs\Models\Catalog;
use Override;

class SelectOptions extends Action
{
    #[Override]
    public function identifier(): string
    {
        return 'select-options';
    }

    #[Override]
    public function name(): string
    {
        return __('Select Options');
    }

    #[Override]
    public function description(): string
    {
        return __('Gets select options from a catalog based on the catalog name');
    }

    #[Override]
    public function parameters(): array
    {
        return [
            [
                'name' => 'catalog',
                'description' => 'The name of the catalog to retrieve options from',
                'type' => 'string',
                'rules' => ['nullable', 'string'],
            ],
        ];
    }

    /**
     * Get select options from a catalog.
     *
     * @param  array{catalog?: string|null}  $attributes
     * @return array{success: bool, value: array<string, string>}
     */
    #[Override]
    public function handle(array $attributes = []): array
    {
        $this->fill($attributes);
        $validatedData = $this->validateAttributes();

        $catalog = $validatedData['catalog'] ?? null;

        return [
            'success' => true,
            'value' => Catalog::options($catalog),
        ];
    }
}
