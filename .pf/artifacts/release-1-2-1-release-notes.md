# WT Multicategories 1.2.1 Release Notes

## Summary

Bugfix release for Contact pages when the Contact additional-categories field is not configured.

## User-Visible Changes

- Contact pages now open correctly when no additional-categories field is selected for contacts in the plugin settings.

## Internal Changes Worth Noting

- The custom Contact integration is activated only when its field is configured.
- The Contact category query override follows the current Joomla query API.
- The custom MVC factory receives the dependencies required by Joomla 6.

## Operational Notes

- Install the `1.2.1` ZIP through the standard Joomla extension installer.
- Existing plugin settings are preserved during update.

## Known Limitations

- No new features are included in this bugfix release.
