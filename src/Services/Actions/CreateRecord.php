<?php

namespace Opscale\NovaDynamicResources\Services\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Opscale\Actions\Action;
use Opscale\NovaDynamicResources\Models\Template;
use Override;

class CreateRecord extends Action
{
    /**
     * Indicates if this action is available on the resource index.
     */
    public bool $showOnIndex = true;

    /**
     * Indicates if this action is available on the resource detail.
     */
    public bool $showOnDetail = true;

    /**
     * Indicates if this action is available on the table row.
     */
    public bool $showInline = true;

    /**
     * Indicates if the action should be shown without confirmation.
     */
    public bool $withoutConfirmation = true;

    #[Override]
    public function identifier(): string
    {
        return 'create-record';
    }

    #[Override]
    public function name(): string
    {
        return __('Create Record');
    }

    #[Override]
    public function description(): string
    {
        return __('Navigates to the create page for a new record in the selected dynamic resource');
    }

    #[Override]
    public function parameters(): array
    {
        return [];
    }

    /**
     * Execute the action.
     *
     * @param  array<string, mixed>  $attributes
     * @return array{success: bool, message: string}
     */
    #[Override]
    public function handle(array $attributes = []): array
    {
        return [
            'success' => true,
            'message' => __('Navigating to create record page'),
        ];
    }

    /**
     * Execute the action as a Nova action.
     *
     * @param  Collection<int, Template>  $models
     */
    public function asNovaAction(ActionFields $fields, Collection $models): ActionResponse
    {
        $resource = $models->first();
        $uriKey = $resource->uri_key;

        return $this->visit("/resources/{$uriKey}/new");
    }
}
