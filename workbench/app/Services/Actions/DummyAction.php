<?php

declare(strict_types=1);

namespace Workbench\App\Services\Actions;

use Illuminate\Support\Str;
use Opscale\Actions\Action;
use Override;

/**
 * Placeholder Action used while the real domain Actions are being scaffolded.
 *
 * `label()` is a chainable setter — `RenderAction` calls
 * `$instance->label('Mark as Paid')` from the row's `config.label` BEFORE
 * Nova reads the action's name, so the label is visible on the resource
 * page from the moment the Template renders.
 *
 * Wire it on a Template by creating a `dynamic_resources_actions` row with:
 *   class:  Workbench\App\Services\Actions\DummyAction
 *   label:  "Mark as Paid"          // shown in the platform admin
 *   config: {"label": ["Mark as Paid"]} // shown on the rendered Nova action
 */
class DummyAction extends Action
{
    protected ?string $resolvedLabel = null;

    /**
     * Chainable label setter consumed by RenderAction → Nova.
     */
    public function label(string $label): static
    {
        $this->resolvedLabel = $label;

        return $this;
    }

    #[Override]
    public function identifier(): string
    {
        return $this->resolvedLabel !== null
            ? Str::slug($this->resolvedLabel)
            : 'dummy-action';
    }

    #[Override]
    public function name(): string
    {
        return $this->resolvedLabel ?? 'Dummy Action';
    }

    #[Override]
    public function description(): string
    {
        return 'Placeholder action — returns success without performing any work. Replace with a real implementation when the behavior is defined.';
    }

    /**
     * No runtime user inputs — the label is metadata, not a parameter.
     *
     * @return array<int, array{name: string, description: string, type: string, rules: array<int, string>}>
     */
    #[Override]
    public function parameters(): array
    {
        return [];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{success: true}
     */
    #[Override]
    public function handle(array $attributes = []): array
    {
        return ['success' => true];
    }
}
