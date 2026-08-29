# Package Assurance

## Change

- Added `.playwright-mcp/` to the project `.gitignore`.
- Added `.playwright-mcp/` to the Phing Packager `exclude` list.

## Verification

1. Created `.playwright-mcp/must-not-ship.txt` as a temporary marker.
2. Built the release package with Phing target `3. Package release`.
3. Inspected the ZIP file list.
4. Confirmed that neither `.playwright-mcp/` nor the marker was present.
5. Removed the temporary marker directory.

## Result

Passed. The final release ZIP contains only Joomla plugin files and is safe from browser-runtime artifact leakage.
