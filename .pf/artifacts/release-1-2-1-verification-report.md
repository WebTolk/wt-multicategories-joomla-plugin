# WT Multicategories 1.2.1 Verification Report

## Release Decision

- The current public GitHub release is `v.1.2.0`; there is no published `1.3.0` release.
- The fix is backward-compatible and corrects faulty activation of the Contact MVC factory, so the release version is `1.2.1`.
- The release manifest and Phing package configuration both use `1.2.1`.

## nerudas.local 1.3.0 Comparison

The installed `1.3.0` copy was inspected read-only before implementation.

- `ArticlesModel`, `AdminArticlesModel`, `CategoryModel`, `MappingService`, and `ItemsFinderTrait` matched the pre-change repository implementation.
- `RebuildMappingsCommand` differed only in its file-header version.
- The `1.3.0` extension factory was less compatible with Joomla 6: it replaced the MVC factory without injecting form, dispatcher, database, router, cache, user, and mailer dependencies.
- The `1.3.0` factory was activated for Contact even when no Contact field was configured.
- The remaining manifest differences were version/date/copyright and SQL install/uninstall path layout, not new multicategory behavior.

Conclusion: the local `1.3.0` deployment contains no feature that should be pulled into this bugfix. Its factory implementation would reproduce the reported `UserFactory not set` failure.

## Joomla 6.1.3 Core Review

The following live Joomla `6.1.3` models were compared with the connected local Joomla `6.1.2` source package:

- `components/com_content/src/Model/ArticlesModel.php`
- `administrator/components/com_content/src/Model/ArticlesModel.php`
- `components/com_contact/src/Model/CategoryModel.php`

All three files are identical between `6.1.2` and `6.1.3`; Joomla `6.1.3` introduced no additional model change in this surface.

The plugin Content models call `parent::getListQuery()`, so they automatically retain the current core implementation. The Contact model owns a modified copy of the query and required synchronization with current core practices:

- query creation now uses `createQuery()`;
- slug and category slug expressions now use the database query API instead of MySQL-specific `CHAR_LENGTH` and `CONCAT_WS` expressions;
- a local `getSlugColumn()` helper is necessary because the core helper is private.

The Joomla `6.1.3` MVC factory service provider injects seven dependencies. The custom factory now mirrors that contract, including `UserFactoryInterface`, and is installed only when the relevant custom field is configured.

## Package Assurance

- Phing target: `3. Package release`.
- Package: `.packages/System - WT Multicategories_1.2.1.zip`.
- SHA-256: `DABDC7853B68F538BBE829DA8F2A35A948FFD1BB60F9EDBE5B26CF6A7F982576`.
- ZIP manifest version: `1.2.1`.
- ZIP contains only extension files; Process Forge, IDE, Playwright, and packaging work files are excluded.
- Installation over the existing plugin on `joomla.local` completed successfully.

## Joomla.local Runtime Matrix

Stand: Joomla `6.1.3`, PHP `8.4` CLI installer, administrator and REST operations performed with the dedicated test identity.

Passed scenarios:

- plugin enabled, Contact field empty, linked-user contact page: HTTP 200, no `UserFactory not set` error;
- plugin enabled, valid Contact field selected, linked-user contact page: HTTP 200;
- primary Contact category with active override: contact rendered, no SQL or factory error;
- mapped secondary Contact category with active override: contact rendered exactly once;
- Contact field cleared while the custom field still existed: secondary category no longer rendered the contact;
- linked-user contact after clearing the field: HTTP 200, no `UserFactory not set` error;
- frontend Content route with configured Content field: HTTP 200;
- administrator Content list with `work_in_admin=0`: HTTP 200;
- administrator Content list with `work_in_admin=1`: HTTP 200.

Static and package checks:

- PHP syntax checks passed for both changed PHP files;
- PhpStorm project build passed;
- `git diff --check` passed;
- project-local PHPUnit, PHPStan, PHPCS, or Composer configuration is not present;
- Joomla logs contain no `UserFactory`, WT Multicategories, uncaught, or fatal errors from the run.

## Cleanup And Final Stand State

- Temporary contact, Contact custom field, secondary category, and generated mapping were removed.
- REST verification shows no contacts and no Contact custom fields; the original `Uncategorised` Contact category remains.
- Plugin state is enabled and not checked out.
- Plugin parameters were restored to Content field `1`, empty Contact field, and `work_in_admin=0`.
- Installed manifest version is `1.2.1`.

## Result

Release candidate `1.2.1` passes the required Joomla `6.1.3` regression and compatibility matrix.
