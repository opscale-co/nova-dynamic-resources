<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Nova\Repeatables;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Template;
use Opscale\NovaDynamicResources\Services\Actions\RenderField;
use Override;

/**
 * Renders one Repeatable per Composited Template, so Nova's
 * "+ Add" dropdown lists every available template as a distinct block
 * type. Each row writes a HasMany child row carrying the chosen
 * template_id plus the inline values of the template's dynamic fields.
 *
 * Composited (not Inherited) is the right type here because each child
 * row is a real Eloquent model with its own host columns — dynamic
 * field values live alongside them in the model's `data` JSON column.
 *
 * Consumers build the list with the static factory and pass the result
 * to Repeater::repeatables():
 *
 *     Repeater::make('Line Items')
 *         ->asHasMany()
 *         ->uniqueField('uuid')
 *         ->repeatables(Record::repeatablesFor(
 *             LineItem::class,
 *             fn ($query) => $query->where('related_class', \App\Nova\LineItem::class),
 *         ));
 *
 * Known limitation: Nova's HasMany preset hydrates existing rows by
 * model class, so when reading back saved rows it picks the first
 * generated Repeatable in the list and renders its fields. Save flow
 * works correctly — each row persists its own template_id and dynamic
 * data. Detail/edit of pre-existing rows happens on the child's own
 * Nova resource.
 */
abstract class Record extends Repeatable
{
    public static ?Template $template = null;

    /**
     * Build one anonymous Record subclass per Composited Template, then
     * return their make() instances ready to hand to
     * Repeater::repeatables().
     *
     * @return array<int, self>
     */
    final public static function repeatablesFor(string $childModel, ?Closure $filter = null): array
    {
        /** @var Builder<Template> $query */
        $query = Template::query()->where('type', TemplateType::Composited);

        if ($filter !== null) {
            $filter($query);
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, Template> $templates */
        $templates = $query->orderBy('label')->get();

        $base = self::class;
        $templateClass = Template::class;
        $instances = [];

        foreach ($templates as $template) {
            // Mirrors the dynamic Resource generation pattern in
            // PackageServiceProvider — each eval call produces a
            // distinct anonymous class so Nova's key()/label() static
            // dispatch resolves per-template. The $template property
            // is redeclared so each subclass owns its static slot
            // instead of writing through to the abstract parent.
            $anonymous = eval("return new class extends \\{$base} {
                public static ?\\{$templateClass} \$template = null;
            };");

            if (! is_object($anonymous)) {
                continue;
            }

            /** @var class-string<self> $class */
            $class = get_class($anonymous);
            $class::$model = $childModel;
            $class::$template = $template;

            $instances[] = $class::make();
        }

        return $instances;
    }

    #[Override]
    public static function key(): string
    {
        if (static::$template === null) {
            return parent::key();
        }

        return 'record-'.static::$template->id;
    }

    #[Override]
    public static function label(): string
    {
        if (static::$template === null) {
            return parent::label();
        }

        return static::$template->label;
    }

    #[Override]
    public static function singularLabel(): string
    {
        if (static::$template === null) {
            return parent::singularLabel();
        }

        return static::$template->singular_label;
    }

    /**
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    #[Override]
    public function fields(NovaRequest $request): array
    {
        $template = static::$template;

        if ($template === null) {
            /** @var array<int, \Laravel\Nova\Fields\Field> $empty */
            $empty = parent::fields($request);

            return $empty;
        }

        $fields = [
            Hidden::make(__('Template'), 'template_id')
                ->default($template->id)
                ->rules('required'),
        ];

        foreach ($template->fields as $templateField) {
            /** @var array{success: bool, instance: \Laravel\Nova\Fields\Field} $result */
            $result = RenderField::run([
                'type' => $templateField->type,
                'label' => $templateField->label,
                'name' => $templateField->name,
                'required' => $templateField->required,
                'display_in_index' => $templateField->display_in_index,
                'rules' => $templateField->rules ?? [],
                'config' => $templateField->config ?? [],
            ]);

            $fields[] = $result['instance'];
        }

        /** @var array<int, \Laravel\Nova\Fields\Field> $merged */
        $merged = [
            ...parent::fields($request),
            ...$fields,
        ];

        return $merged;
    }
}
