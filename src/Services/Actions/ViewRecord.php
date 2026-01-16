<?php

namespace Opscale\NovaDynamicResources\Services\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Opscale\Actions\Action;
use Opscale\NovaDynamicResources\Models\Record;
use Override;

class ViewRecord extends Action
{
    /**
     * Indicates if this action is available on the resource index.
     */
    public bool $showOnIndex = true;

    /**
     * Indicates if this action is available on the resource detail.
     */
    public bool $showOnDetail = false;

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
        return 'view-record';
    }

    #[Override]
    public function name(): string
    {
        return __('View');
    }

    #[Override]
    public function description(): string
    {
        return __('Navigates to the detail page for the selected dynamic record');
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
            'message' => __('Navigating to record detail page'),
        ];
    }

    /**
     * Execute the action as a Nova action.
     *
     * @param  Collection<int, Record>  $models
     */
    public function asNovaAction(ActionFields $fields, Collection $models): ActionResponse
    {
        $record = $models->first();
        $record->load('template');
        $template = $record->template;

        $uriKey = $template->uri_key;
        $recordId = $record->id;

        return $this->visit("/resources/{$uriKey}/{$recordId}");
    }
}
