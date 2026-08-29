# Changed Files

## Summary

WT Multicategories now leaves a Joomla component's MVC factory untouched when the corresponding multicategory custom field is not configured.

## Files Changed

- `src/Extension/Wtmulticategories.php`
  - Normalizes the booted component name once.
  - Returns before component container access when the feature is inactive.
  - Enables Contact replacement only for the site client with a configured contact field.
  - Enables Content replacement only with a configured content field; administrator replacement additionally requires `work_in_admin`.
- `.pf/artifacts/userfactory-contact-fix-*.md`
  - Records scope, invariants, public contract, implementation plan and assurance evidence.

## Contract Impact

No manifest, parameter, model, database schema or public API contract changed. Empty field ids now consistently mean that the plugin does not modify that component's MVC services.

## Follow-up Notes

- The release ZIP remains version `1.2.0`; versioning and publication are outside this run.
- Browser MCP creates `.playwright-mcp/` in the repository root. It was removed before packaging and after verification so it is not present in the ZIP or worktree.
