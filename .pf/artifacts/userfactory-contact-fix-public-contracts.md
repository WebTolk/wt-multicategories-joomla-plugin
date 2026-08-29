# Public Contracts

## Preserved

- Plugin type, element, namespace and manifest parameters.
- Existing values of `multicategories_com_content_field_id`, `multicategories_com_contact_field_id` and `work_in_admin`.
- Existing model replacement classes and mapping table behavior.
- Existing package version `1.2.0` for this non-release fix cycle.

## Behavioral Clarification

- An empty or zero field id means that WT Multicategories is inactive for that component and must leave its MVC factory untouched.

No new public API or backward-incompatible contract is introduced.
