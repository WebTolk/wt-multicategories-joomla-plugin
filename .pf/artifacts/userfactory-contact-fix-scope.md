# Scope: Contact MVCFactory guard

## Goal

Do not replace the Joomla MVC factory for a component when WT Multicategories has no configured custom field for that component.

## In Scope

- Guard `com_contact` site MVC factory replacement by `multicategories_com_contact_field_id`.
- Guard `com_content` site and administrator MVC factory replacement by `multicategories_com_content_field_id`.
- Keep administrator replacement dependent on `work_in_admin`.
- Verify package build, installation, REST API behavior, administrator behavior and frontend contact/content routes on `Joomla.local`.

## Out of Scope

- Changes to mapping persistence or SQL query behavior.
- New public settings or language strings.
- Release publication or version bump.
