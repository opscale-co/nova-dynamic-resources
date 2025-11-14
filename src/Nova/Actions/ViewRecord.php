<?php

namespace Opscale\NovaDynamicResources\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NovaDynamicResources\Models\DynamicRecord;

class ViewRecord extends Action
{
    /**
     * Indicates if this action is available on the resource index.
     */
    public $showOnIndex = true;

    /**
     * Indicates if this action is available on the resource detail.
     */
    public $showOnDetail = false;

    /**
     * Indicates if this action is available on the table row.
     */
    public $showInline = true;

    /**
     * Indicates if the action should be shown without confirmation.
     */
    public $withoutConfirmation = true;

    /**
     * The displayable name of the action.
     */
    public $name = 'View';

    /**
     * Perform the action on the given models.
     *
     * @param  Collection<int, DynamicRecord>  $models
     */
    public function handle(ActionFields $fields, Collection $models): mixed
    {
        $record = $models->first();
        $record->load('resource');
        $resource = $record->resource;

        $uriKey = $resource->uri_key;
        $recordId = $record->id;

        return ActionResponse::visit("/resources/{$uriKey}/{$recordId}");
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
