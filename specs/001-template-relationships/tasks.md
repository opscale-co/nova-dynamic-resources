# Tasks: Template Relationships

**Branch**: `001-template-relationships` | **Date**: 2026-05-03 | **Plan**: [plan.md](plan.md)

Tasks are dependency-ordered. `[P]` marks tasks that can run in parallel after their dependencies are met.

## T01 — Enum: `RelationshipCardinality`

Create `src/Models/Enums/RelationshipCardinality.php` mirroring `TemplateType.php`. Cases: `BelongsTo`, `HasOne`, `HasMany`. PHP 8.3 backed string enum.

## T02 — Migration: `dynamic_resources_relationships`

Create `database/migrations/2026_05_03_000001_create_dynamic_resources_relationships_table.php`:

- ULID `id`, ULID `template_id` (FK cascade), string `name`, string `label`, string `cardinality`, ULID `related_template_id` (FK cascade), string `foreign_key`, string `inverse_name` nullable, boolean `required`, json `rules` nullable, json `config` nullable, timestamps, softDeletes.
- Indexes: `template_id`, `(related_template_id, cardinality)`, unique `(template_id, name)`.

Depends: T01.

## T03 — Model: `Relationship`

Create `src/Models/Relationship.php`:

- Traits `HasUlids`, `SoftDeletes`, `Validatable`, `CastsValidationData`.
- Static `$validationRules` covering all columns; `related_template_id` required + `exists:dynamic_resources_templates,id`; `foreign_key` regex; unique `(template_id, name)` per template.
- Casts: `cardinality => RelationshipCardinality::class`, `rules => array`, `config => array`, `required => bool`.
- `template(): BelongsTo` (owner) and `relatedTemplate(): BelongsTo` (target).

Depends: T01, T02.

## T04 — Repository trait: `RelationshipRepository` [P]

Create `src/Models/Repositories/RelationshipRepository.php` mirroring `FieldRepository.php`. Auto-derive `name` from label slug-snake when blank.

Depends: T03.

## T05 — Update `Template` model

Modify `src/Models/Template.php`:

- Add `relationships(): HasMany` returning `Relationship` (with `withoutTrashed()`).
- Add `'relationships'` to `$with`.
- Update class-level `@property-read` docblock.

Depends: T03.

## T06 — Custom Relation: `JsonBelongsTo` [P]

Create `src/Eloquent/Relations/JsonBelongsTo.php` extending `Illuminate\Database\Eloquent\Relations\Relation`:

- Constructor takes the parent model, related Builder, and `foreign_key`.
- `addConstraints()`: `$query->where('id', $this->parent->data[$fk] ?? null)`.
- `addEagerConstraints($models)`: collect `data[$fk]` from each model, `whereIn('id', ...)`.
- `match($models, $results, $relation)`: assign single result by id matching each model's `data[$fk]`.
- `initRelation($models, $relation)`: set null default.
- `getResults()`: returns first.

Depends: T01.

## T07 — Custom Relation: `JsonHasOne` [P]

Create `src/Eloquent/Relations/JsonHasOne.php`. Same pattern as `JsonHasMany` (T08) but `take(1)` and single result.

Depends: T01.

## T08 — Custom Relation: `JsonHasMany` [P]

Create `src/Eloquent/Relations/JsonHasMany.php`:

- `addConstraints()`: `$query->where('data->'.$fk, $this->parent->id)`.
- `addEagerConstraints($models)`: `$query->whereIn('data->'.$fk, $models->pluck('id'))`.
- `match`: group results by `data[$fk]`, assign collections to source models.
- `initRelation`: default empty Collection.
- `getResults()`: returns the relation builder result collection.

Depends: T01.

## T09 — Trait: `HasDynamicRelationships`

Create `src/Models/Concerns/HasDynamicRelationships.php`:

- Per-instance cache `protected array $dynamicRelCache = []`.
- `resolveDynamicRelationship(string $name)`: lookup forward, then inverse via `inverse_name`.
- `buildDynamicRelation(Relationship $rel)`: pick target Eloquent class from related Template (Dynamic → `Record`, Inherited/Composited → `related_class`), pre-scope query (`where('template_id', $relatedTemplate->id)` for Dynamic), instantiate the matching `Json*` Relation.
- `__call($method, $parameters)`: try dynamic relation; otherwise `parent::__call`.
- `isRelation($key)`: extend parent.

Depends: T03, T05, T06, T07, T08.

## T10 — Apply trait to `Record` and `UsesTemplate` (Eloquent)

- Modify `src/Models/Record.php`: `use HasDynamicRelationships;`.
- Modify `src/Models/Concerns/UsesTemplate.php`: `use HasDynamicRelationships;` and extend `initializeUsesTemplate` to push `'template.relationships'` into `$with`.

Depends: T09.

## T11 — Render service: `RenderRelationship`

Create `src/Services/Actions/RenderRelationship.php` mirroring `RenderField.php`:

- Parameters: `cardinality` (string), `name`, `label`, `related_template_uri_key`, `rules` (array), `config` (array).
- Resolve resource class via `get_class(app('dynamic-'.$relatedTemplateUriKey))`.
- Build the Nova field via `match` on cardinality (BelongsTo / HasOne / HasMany).
- Apply rules + config (via `withMeta` for arbitrary keys, or method invocation when method exists on the field).

Depends: T03.

## T12 — Nova consumption trait

Modify `src/Nova/Concerns/UsesTemplate.php`:

- Add `renderTemplateRelationships(): array` that iterates `static::$template->relationships`, calls `RenderRelationship::run([...])`, collects fields.

Depends: T11.

## T13 — Nova Record resource

Modify `src/Nova/Record.php`:

- Append `...$this->renderTemplateRelationships()` to `fields()` after the dynamic field block.

Depends: T12.

## T14 — Nova Resource: `Relationship`

Create `src/Nova/Relationship.php`:

- `$displayInNavigation = false`, `$model = Relationship::class`.
- Static `defaultFields()` reusing `Relationship::$validationRules` for label, name (Slug from label, snake separator), cardinality (Select), related_template_id (BelongsTo template), foreign_key, inverse_name, required, rules (KeyValue), config (KeyValue).
- `fields()` adds BelongsTo Template (owner).

Depends: T03, T05.

## T15 — Nova Repeatable: `Relationship` [P]

Create `src/Nova/Repeatables/Relationship.php` mirroring `Repeatables/Field.php`. Drops `rules`/`config` from inline editing.

Depends: T14.

## T16 — Nova Template resource

Modify `src/Nova/Template.php`:

- Add a "Relationships" tab with a `HasMany` of the new Resource.
- Embed `RelationshipRepeatable::make()->asHasMany()` next to the existing Field/Action repeaters in the form.

Depends: T15.

## T17 — Service Provider

Modify `src/PackageServiceProvider.php`:

- Register `Nova\Relationship::class` in the resource list.
- No changes to `generateResources()` (the dynamic binding `'dynamic-{uri_key}'` already exists and is consumed by `RenderRelationship`).

Depends: T14.

## T18 — Config cleanup

Modify `config/nova-dynamic-resources.php`:

- Remove the `'parent'` and `'children'` entries from the `'fields'` block.
- Add a `'relationships'` block with three keys (`belongs_to`, `has_one`, `has_many`) mapping to the corresponding Nova field classes with default rules and empty config.

Depends: T11.

## T19 — Workbench seeder

Modify `workbench/database/seeders/TemplateSeeder.php`:

- Remove the `parent_event` field of type `parent` from the Showcase template.
- Add a `Relationship` row on Showcase: `name='parent'`, cardinality `BelongsTo`, `related_template_id = $showcaseTemplate->id` (self-ref), `foreign_key='parent_id'`, `inverse_name='children'`.
- Update the `data` of seeded Showcase records (if any) to include `parent_id` examples.

Depends: T03, T18.

## T20 — Test: Relationship model [P]

Create `tests/Unit/Models/RelationshipTest.php`:

- Persists with required attributes (cardinality cast, BelongsTo Template, BelongsTo relatedTemplate).
- Auto-derives `name` from label.
- Validates `related_template_id` required + exists.
- Validates `foreign_key` regex.
- Enforces unique `(template_id, name)`.

Depends: T03, T04.

## T21 — Test: dynamic relation resolution [P]

Create `tests/Unit/Models/Concerns/HasDynamicRelationshipsTest.php`:

- Build two Dynamic Templates `A`, `B`.
- Create a `BelongsTo` Relationship `b` from A→B with `foreign_key='b_id'`, `inverse_name='as'`.
- Insert one Record of B and one Record of A whose `data['b_id']` equals B's id.
- Assert `$recordA->b` returns the B Record (single).
- Assert `$recordB->as` returns a Collection containing recordA (HasMany inverse auto-derived).
- Assert `Record::with('b')->get()` returns matching records and uses ≤ 2 queries (use `DB::enableQueryLog` to count).

Depends: T09, T10, T20.

## T22 — Test: RenderRelationship feature [P]

Create `tests/Feature/Actions/RenderRelationshipTest.php`:

- For each cardinality (`BelongsTo`, `HasOne`, `HasMany`) build a related Template and bind a fake Resource to `'dynamic-{uri_key}'`, run `RenderRelationship::run([...])`, assert returned instance is the expected Nova field class.
- Assert resource class resolution falls back when binding missing (throws or skips, per `buildDynamicRelation` policy).

Depends: T11.

## T23 — Test: browser regression for Showcase

Modify `tests/Browser/SeededTemplatesTest.php`:

- Remove `parent` from `expectedLabels` (legacy field type gone).
- Add a new test or assertion that verifies the Showcase create form shows the new self-referential Relationship's selector labeled as configured (e.g. "Parent").

Depends: T19.

## T24 — Quality gates

Run, in order:

1. `DB_DATABASE=vendor/orchestra/testbench-dusk/laravel/database/database.sqlite ./vendor/bin/testbench migrate:fresh`
2. `./vendor/bin/pest tests/Unit tests/Feature` — must be green.
3. `./vendor/bin/pest -c phpunit.dusk.xml` — must be green.
4. `./vendor/bin/duster lint --dirty` — must pass (autofix style with `npm run fix` if needed before commit).
5. `npm run analyse` — PHPStan must be green.

Depends: T01–T23.

## T25 — Commit

`git add -A` and create a single commit on the feature branch summarising the change. Hook (`before_plan` / `after_implement`) handled by the spec-kit git extension if applicable.

Depends: T24.
