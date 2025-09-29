<?php

namespace Opscale\NovaDynamicResources\Nova\Repeatables;

use Illuminate\Support\Facades\Config;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Override;

class Field extends Repeatable
{
    /**
     * Get the fields for this repeatable.
     */
    /**
     * @return array<mixed>
     */
    #[Override]
    final public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Label')
                ->rules('required', 'string', 'min:1', 'max:25'),

            Slug::make('Name')
                ->from('label')
                ->rules('required', 'string', 'min:1', 'max:25', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/'),

            Select::make('Type')
                ->options($this->getBusinessTypeOptions())
                ->displayUsingLabels()
                ->searchable()
                ->rules('required'),

            Boolean::make('Required')
                ->default(false),

            KeyValue::make('Validation Rules', 'rules')
                ->keyLabel('Rule')
                ->valueLabel('Value')
                ->nullable(),

            KeyValue::make('Config', 'config')
                ->keyLabel('Method')
                ->valueLabel('Parameters')
                ->nullable(),
        ];
    }

    /**
     * Get the business type options for the select field.
     */
    /**
     * Get the business type options for the select field.
     *
     * @return array<string, string>
     */
    final protected function getBusinessTypeOptions(): array
    {
        /** @var array<string, mixed> $configFields */
        $configFields = Config::get('nova-dynamic-resources.fields', []);

        $options = [];
        foreach (array_keys($configFields) as $key) {
            $options[$key] = $key;
        }

        return $options;
    }
}
