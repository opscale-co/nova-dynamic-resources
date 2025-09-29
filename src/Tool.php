<?php

namespace Opscale\NovaDynamicResources;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool as NovaTool;
use Opscale\NovaDynamicResources\Nova\DynamicResource;

class Tool extends NovaTool
{
    final public function boot(): void
    {
        parent::boot();
        Nova::script('nova-dynamic-resources', __DIR__ . '/../dist/js/tool.js');
        Nova::style('nova-dynamic-resources', __DIR__ . '/../dist/css/tool.css');
    }

    final public function menu(Request $request): MenuItem
    {
        return MenuItem::resource(DynamicResource::class);
    }
}
