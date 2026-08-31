<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Support;

/**
 * Formateadores de display reutilizables para los campos declarados en
 * config/nova-dynamic-resources.php.
 *
 * Los valores de `config` se guardan en un archivo de configuración y se pasan
 * tal cual a los métodos del campo Nova (ver RenderField). Un Closure ahí rompe
 * `php artisan config:cache` porque no es serializable. Por eso el display se
 * expresa como el string callable `FieldFormatters::urlHost`, que sí es
 * serializable y sigue siendo un callable válido para `displayUsing()`.
 */
class FieldFormatters
{
    /**
     * Muestra solo el host de una URL (o el valor original si no aplica).
     */
    public static function urlHost(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return parse_url($value, PHP_URL_HOST) ?? $value;
    }
}
