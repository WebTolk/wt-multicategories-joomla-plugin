# WT Multicategories 1.2.1 Migration Notes

## Migration Required or Not

No migration is required.

## What Changes

The release updates plugin code and language files only. It does not introduce a database schema change.

## Who Is Affected

Sites using WT Multicategories `1.2.0`, especially installations where the Contact additional-categories field is not selected.

## Upgrade Steps

Install the `1.2.1` ZIP through Joomla's extension installer. Existing settings and category mappings are retained.

## Rollback Notes

Reinstall the previous `1.2.0` package if rollback is required. The Contact page bug fixed by `1.2.1` will return after rollback.
