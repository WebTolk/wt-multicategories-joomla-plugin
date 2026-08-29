# Project Onboarding Report

## Status

applied

## Project

- id: wt-multicategories-joomla-plugin
- name: wt-multicategories-joomla-plugin
- type: joomla-extension

## Process Boundary

Project onboarding creates only the project-local `.pf/` flow root and links it to an existing workplace. It does not recreate the workplace, copy global packages into the project, or write local absolute paths to public files.

## Created First-Run Files

- .pf/START_AGENT_HERE.md
- .pf/assignments/first-assignment.yaml
- .pf/contexts/project-context.snapshot.yaml
- .pf/artifacts/project-onboarding-report.md
- .pf/reviews/project-onboarding-review.md
- .pf/handoffs/project-ready-handoff.md

## Doctor Status

fail

## Doctor Output

```text
PASS: .pf/process-forge.yaml found
PASS: public manifest has no local absolute paths
PASS: public manifest contains no secret values
PASS: project ProcessForge manifest is valid YAML
PASS: project ProcessForge manifest matches process-forge-manifest.schema.json
PASS: process-forge.local.yaml found
PASS: workplace manifest is reachable
PASS: workplace distributions registry is reachable
PASS: processforge distribution exists
PASS: processforge distribution has tools/
PASS: processforge distribution has schemas/
PASS: processforge distribution has processes/
PASS: processforge distribution has packages/
PASS: processforge distribution has templates/
PASS: .pf/hooks.yaml is valid YAML
PASS: .pf/hooks.yaml hooks.targets is a list
PASS: hooks.targets[0].id present
PASS: hooks.targets[0].type present
PASS: hooks.targets[0].enabled present
PASS: hooks.targets[0].event_types present
PASS: hooks.targets[0].event_types is a string list
PASS: hooks.targets[1].id present
PASS: hooks.targets[1].type present
PASS: hooks.targets[1].enabled present
PASS: hooks.targets[1].event_types present
PASS: hooks.targets[1].event_types is a string list
PASS: project coordination mode is inherit
PASS: effective coordination mode is organized
PASS: organized project has workplace director capability
PASS: organized project has Director Office available
FAIL: .gitignore missing .pf/process-forge.local.yaml

Why:
  Private local config and runtime data must stay out of public project files.

Fix:
  add .pf/process-forge.local.yaml to .gitignore
FAIL: .gitignore missing .pf/runtime/

Why:
  Private local config and runtime data must stay out of public project files.

Fix:
  add .pf/runtime/ to .gitignore
FAIL: .gitignore missing .pf/cache/

Why:
  Private local config and runtime data must stay out of public project files.

Fix:
  add .pf/cache/ to .gitignore
PASS: project package draft exists
PASS: required capabilities are resolved or built in
PASS: .pf/contexts/project-context.snapshot.yaml contains no local absolute paths
PASS: .pf/contexts/project-context.snapshot.md contains no local absolute paths
PASS: knowledge resource index for project.wt-multicategories-joomla-plugin optional for project-local package resources
PASS: knowledge resource index for frontend-frameworks.documentation found
PASS: knowledge resource index for joomla.context7.documentation found
PASS: knowledge resource index for joomla.core.documentation found
PASS: knowledge resource index for joomla.core.source.1 found
PASS: knowledge resource index for joomla.core.source.1-5 found
PASS: knowledge resource index for joomla.core.source.1-6 found
PASS: knowledge resource index for joomla.core.source.1-7 found
PASS: knowledge resource index for joomla.core.source.2-5 found
PASS: knowledge resource index for joomla.core.source.3 found
PASS: knowledge resource index for joomla.core.source.4 found
PASS: knowledge resource index for joomla.core.source.5 found
PASS: knowledge resource index for joomla.core.source.6 found
PASS: knowledge resource index for joomla.third-party-extension-docs found
PASS: knowledge resource index for joomshopping.core.source.5-9 found
PASS: knowledge resource index for marketing-seo-geo-ai-knowledge found
WARN: knowledge resource index for openai.codex-cli.documentation missing

Why:
  Expected resource index for workspace package `openai.codex-cli.documentation`.

Fix:
  python bin/pf.py knowledge-resource-index-build --project-root . --package openai.codex-cli.documentation --apply
PASS: knowledge resource index for radicalmart-express.core.source.3 found
PASS: knowledge resource index for radicalmart.core.source.2 found
PASS: knowledge resource index for radicalmart.core.source.3 found
PASS: knowledge resource index for radicalmart.extension.source.analytics found
PASS: knowledge resource index for radicalmart.extension.source.export-core found
PASS: knowledge resource index for radicalmart.extension.source.export-plugins-plg-radicalmart-export-yml found
PASS: knowledge resource index for radicalmart.extension.source.import-core found
PASS: knowledge resource index for radicalmart.extension.source.import-plugins-plg-radicalmart-import-excel found
PASS: knowledge resource index for radicalmart.extension.source.modules-filter-extended-mod-msg-rmf found
PASS: knowledge resource index for radicalmart.extension.source.plugins-crm-retailcrm found
PASS: knowledge resource index for radicalmart.extension.source.plugins-fields-plg-radicalmart-fields-gallery found
PASS: knowledge resource index for radicalmart.extension.source.plugins-fields-plg-radicalmart-fields-standard found
PASS: knowledge resource index for radicalmart.extension.source.plugins-media-plg-radicalmart-media-resize found
PASS: knowledge resource index for radicalmart.extension.source.plugins-media-plg-radicalmart-media-video found
PASS: knowledge resource index for radicalmart.extension.source.plugins-message-plg-radicalmart-message-email found
PASS: knowledge resource index for radicalmart.extension.source.plugins-payment-plg-radicalmart-payment-robokassa found
PASS: knowledge resource index for radicalmart.extension.source.plugins-payment-plg-radicalmart-payment-yookassa found
PASS: knowledge resource index for radicalmart.extension.source.plugins-shipping-plg-radicalmart-shipping-addresses found
PASS: knowledge resource index for radicalmart.extension.source.plugins-shipping-plg-radicalmart-shipping-apiship found
PASS: knowledge resource index for radicalmart.extension.source.plugins-shipping-plg-radicalmart-shipping-standard found
PASS: knowledge resource index for radicalmart.extension.source.plugins-shipping-plg-radicalmart-shipping-zone found
PASS: knowledge resource index for radicalmart.extension.source.templates-uikit-pkg-radicalmart-uikit found
PASS: knowledge resource index for rest-api.documentation found
PASS: knowledge resource index for seo.flow.documentation found
PASS: knowledge resource index for virtuemart.core.source.4-4 found
PASS: knowledge resource index for web.documentation found
PASS: .pf/START_AGENT_HERE.md found
PASS: .pf/runtime/bin/pf.py found
PASS: .pf/assignments/first-assignment.yaml found
PASS: .pf/artifacts/project-profile.md found
PASS: .pf/artifacts/project-classification-report.md found
PASS: .pf/artifacts/repository-map.md found
PASS: .pf/artifacts/project-conventions.md found
PASS: .pf/artifacts/global-resource-matching-report.md found
PASS: .pf/artifacts/project-init-proposal.md found
PASS: .pf/artifacts/project-onboarding-report.md found
PASS: .pf/reviews/project-init-review.md found
PASS: .pf/reviews/project-onboarding-review.md found
PASS: .pf/handoffs/project-ready-handoff.md found
```

## Fix Hints

- Resolve the FAIL lines above, then rerun `pf doctor-project --project-root .`.

## Post-Onboarding Verification

- Final status: ready.
- The generated `.gitignore.candidate` entries were applied to `.gitignore`.
- The manifest was narrowed to the operator-requested Joomla-only platform stack and configured for `software-feature-development` with `specialization.fullstack`.
- The current snapshot is `ctx-20260829-060006-9fc02e` and `project-context-check` reports `fresh` with policy action `continue`.
- Final `doctor-project` passed.
- Final `events-validate` passed.
- Required first-assignment artifact: `.pf/artifacts/first-assignment-readiness-note.md`.
