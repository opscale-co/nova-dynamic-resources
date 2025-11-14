<?php

namespace Opscale\NovaDynamicResources;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuGroup;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool as NovaTool;
use Opscale\NovaDynamicResources\Nova\DynamicRecord;
use Opscale\NovaDynamicResources\Nova\DynamicResource;

class Tool extends NovaTool
{
    final public function boot(): void
    {
        parent::boot();
        Nova::script('nova-dynamic-resources', __DIR__ . '/../dist/js/tool.js');
        Nova::style('nova-dynamic-resources', __DIR__ . '/../dist/css/tool.css');
    }

    final public function menu(Request $request): MenuGroup
    {
        return MenuGroup::make('Dynamic Resources', [
            MenuItem::resource(DynamicResource::class),
            MenuItem::resource(DynamicRecord::class),
        ])->collapsable();
    }
}
