# Implementation Plan

1. Normalize the booted component name before eligibility checks.
2. Add a private predicate that evaluates the current Joomla client and plugin parameters.
3. Return before resolving or replacing `MVCFactoryInterface` when the feature is inactive.
4. Keep the existing factory dependency injection and model mapping unchanged.
5. Run syntax, IDE inspection, style/static checks available in the project, and build through Phing.
6. Install the built ZIP on `Joomla.local` and verify the disabled-field regression plus enabled contact/content behavior through REST API and browser routes.
7. Record changed files, review findings, verification evidence and residual risks.
