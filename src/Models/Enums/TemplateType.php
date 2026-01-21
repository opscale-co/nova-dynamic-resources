<?php

namespace Opscale\NovaDynamicResources\Models\Enums;

enum TemplateType: string
{
    case Dynamic = 'Dynamic';
    case Inherited = 'Inherited';
    case Composited = 'Composited';
}
