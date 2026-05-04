# Feature Specification: Template Relationships

**Feature Branch**: `001-template-relationships`
**Created**: 2026-05-03
**Status**: Draft
**Input**: User description: "Add Relationship as a first-class Template concern, parallel to Field and Action."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Define a BelongsTo relationship between two Templates (Priority: P1)

As an admin authoring a Template (e.g. `Products`), I want to declare that each product **belongs to** a Category by referencing another Template (`Categories`). Once declared, the Nova create/edit form for a Product must show a searchable BelongsTo selector populated with existing Categories, and the persisted product must remember its category so the relation works in queries and detail views.

**Why this priority**: BelongsTo is the most common relationship in dynamic data modeling and unlocks parent/foreign-key semantics that the current `parent` field type only fakes. Without it the package cannot model real one-to-many domains.

**Independent Test**: Create two Dynamic Templates (Categories, Products), declare a BelongsTo relationship from Products to Categories, seed a couple of category records, then open `/nova/resources/products/new` and verify the relationship selector appears and persists the link.

**Acceptance Scenarios**:

1. **Given** a Template `Products` with a BelongsTo Relationship to Template `Categories` (foreign key `category_id`), **When** an admin opens the Product create form, **Then** a Nova BelongsTo field labeled with the relationship label is rendered and lists existing Category records as options.
2. **Given** a Product record was saved with a chosen category, **When** the admin re-opens the detail view, **Then** the related Category is shown and clicking through navigates to the Category detail.
3. **Given** the same Product, **When** loaded programmatically, **Then** accessing the relationship method (e.g. `$record->category`) returns the related Category record without an additional query when eager-loaded.

---

### User Story 2 - Expose the inverse HasMany on the related Template (Priority: P2)

As an admin viewing a Category record, I want to see the list of Products that belong to it without having to define a second Relationship row. Declaring a `BelongsTo` from Products with an `inverse_name` of `products` should automatically expose `products` as a HasMany on the Category Template's resource.

**Why this priority**: Inverse exposure halves the configuration burden and prevents drift between the two sides. It is non-essential for an MVP (an admin could declare both rows manually) but greatly improves ergonomics.

**Independent Test**: With Story 1 set up plus an `inverse_name` of `products` on the BelongsTo row, navigate to a Category detail page and verify a "Products" related index lists the category's products.

**Acceptance Scenarios**:

1. **Given** a single BelongsTo Relationship from Products to Categories with `inverse_name = "products"`, **When** an admin opens any Category record's detail, **Then** a HasMany "Products" panel lists all products that reference this category.
2. **Given** the same setup, **When** a Product's category is changed and saved, **Then** the previous category's HasMany panel no longer lists that product and the new one does.

---

### User Story 3 - Replace deprecated `parent`/`children` field types (Priority: P2)

As an admin upgrading a Template that previously used the legacy `parent` (BelongsTo) and `children` (HasMany) field types, I want those types removed from the field registry and recreated as Relationship rows so the package has one canonical way to model relationships.

**Why this priority**: Cleanup is required to avoid two paths doing the same thing inconsistently, but it has no end-user visible value beyond avoiding confusion.

**Independent Test**: Verify `config/nova-dynamic-resources.php` no longer registers `parent`/`children` field types and that the workbench Showcase template uses a Relationship row instead, with the create form rendering the relation correctly.

**Acceptance Scenarios**:

1. **Given** the new package version, **When** an admin opens the Field type select while editing a Template, **Then** `parent` and `children` are absent.
2. **Given** the workbench Showcase Template now declares a self-referential Relationship instead of a `parent` field, **When** the admin opens `/nova/resources/showcases/new`, **Then** a relationship selector to existing Showcase records is rendered.

---

### Edge Cases

- What happens when the related Template is soft-deleted? Treat the Relationship row as effectively orphaned: hide it from the form and skip rendering, but do not delete stored ids in `data` so restoration recovers the link.
- What happens when a record's `data` JSON has no value for the foreign key? The BelongsTo selector renders empty (no preselection) and the relation method returns null.
- What happens for self-referential relationships (e.g. parent showcase referencing showcases)? Allowed; `template_id` and `related_template_id` may be equal.
- What happens when two relationships on the same Template share the same name? Disallowed at write time via uniqueness validation `(template_id, name)`.
- What happens when many records load in a list view? Eager loading by name must batch a single query against the related Template, regardless of dataset size.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow declaring a Relationship per Template with: name, label, cardinality (BelongsTo / HasOne / HasMany), required related Template, and the foreign-key key inside the record `data` JSON.
- **FR-002**: System MUST validate that `related_template_id` exists in the templates table and that `foreign_key` matches `^[a-z][a-z0-9_]*$`.
- **FR-003**: System MUST allow self-referential relationships (template equals related template).
- **FR-004**: System MUST enforce uniqueness of relationship `name` per Template (no duplicate method names).
- **FR-005**: System MUST resolve relationship method calls on records dynamically without requiring the developer to add explicit relation methods to the Eloquent model.
- **FR-006**: System MUST store the BelongsTo foreign key in the source record's existing `data` JSON column (no pivot table).
- **FR-007**: System MUST store the HasOne / HasMany foreign key in the target records' `data` JSON column (the "many" side owns the FK).
- **FR-008**: System MUST allow eager loading by relationship name, batching a single query per name.
- **FR-009**: System MUST render Relationships in Nova create/edit/detail/index forms with the correct related Resource class.
- **FR-010**: System MUST always show Relationships in the index view (no per-relationship `display_in_index` toggle).
- **FR-011**: System MUST optionally derive the inverse relationship from a single row when `inverse_name` is provided (BelongsTo↔HasMany; HasOne↔HasOne).
- **FR-012**: System MUST remove the legacy `parent` and `children` field types from the field type registry.
- **FR-013**: System MUST allow editing a Template's relationships inline within the Template create/edit form, similarly to Fields and Actions.
- **FR-014**: System MUST soft-delete Relationship rows; soft-deleted relationships do not render in Nova or resolve in code; the underlying foreign keys in `data` are preserved.

### Key Entities

- **Relationship**: declares one named relation owned by a Template. Carries: id (ULID), owning template, name, label, cardinality, related template, foreign-key string, optional inverse name, required flag, optional rules and config, soft-delete timestamps.
- **Template**: gains a `relationships` collection alongside its existing `fields` and `actions`.
- **Record / Inherited host / Composited host**: source or target of relationships; the foreign-key value lives in their existing `data` JSON column.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: An admin can declare a BelongsTo relationship between two Dynamic Templates and have the related selector working in the create form within a single page reload (no extra deploy).
- **SC-002**: Loading a list of 100 source records with 1 relationship eager-loaded executes at most 2 SQL queries against the relevant tables (1 for sources, 1 batched for targets), measured via the Laravel query log.
- **SC-003**: 100% of relationships declared via a single row with `inverse_name` set expose both directions (forward and inverse) without additional rows.
- **SC-004**: After upgrading, no Template in the workbench seeders relies on the legacy `parent` or `children` field types; all browser tests pass against the new model.
- **SC-005**: All three cardinalities (BelongsTo, HasOne, HasMany) render correctly in Nova create/edit forms with the related Resource auto-resolved (no manual resource class wiring).

## Assumptions

- The package uses ULID identifiers; foreign-key values stored in `data` are ULID strings.
- The dynamic Resource binding `'dynamic-{uri_key}'` registered in `PackageServiceProvider::generateResources()` exists and is available before relationship rendering happens within `Nova::serving`.
- The host models that use `UsesTemplate` (Records, Inherited, Composited) all expose a writable `data` JSON column.
- The runtime database (SQLite, MySQL, MariaDB, Postgres) supports JSON path queries via Laravel's `data->key` Eloquent syntax.
- BelongsToMany and polymorphic relationships are intentionally out of scope for this version.
- Template-level cascade delete already removes `fields` and `actions` rows; the new `relationships` table will follow the same cascade convention.
