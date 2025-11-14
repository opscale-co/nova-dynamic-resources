<?php

namespace Opscale\NovaDynamicResources\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NovaDynamicResources\Models\DynamicResource;

class CreateRecord extends Action
{
    /**
     * Indicates if this action is available on the resource index.
     */
    public $showOnIndex = true;

    /**
     * Indicates if this action is available on the resource detail.
     */
    public $showOnDetail = true;

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
    public $name = 'Create Record';

    /**
     * Perform the action on the given models.
     *
     * @param  Collection<int, DynamicResource>  $models
     */
    public function handle(ActionFields $fields, Collection $models): mixed
    {
        $resource = $models->first();
        $uriKey = $resource->uri_key;

        return ActionResponse::visit("/resources/{$uriKey}/new");
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
