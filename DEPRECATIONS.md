# Deprecation scan: WIP

This file is a living record of the deprecation warnings discovered while running the test suite and the corrective actions taken.

Status: scan not yet run on CI from this environment. I will run vendor/bin/phpunit locally/CI and update this file with full deprecation messages and locations.

Planned steps

1. Run vendor/bin/phpunit and capture all deprecation notices (message, file, line).
2. Update tests to replace deprecated PHPUnit APIs (assertInternalType -> assertIs*, setUp/tearDown signatures to return void, remove @expectedException annotations, update assertAttribute* usages, etc.).
3. Update source code to replace deprecated CakePHP APIs flagged by tests.
4. Update fixtures/bootstrap to avoid deprecated calls.
5. Re-run test suite until deprecation-free.
6. Add the full before/after test output to the PR description.

Notes
- Minimal, non-breaking changes will be preferred. Any deprecation that requires a breaking change will be documented and left for a major-version work item.
- Branch: copilot/update-deprecated-code-phpunit

Updates will be appended here as they are discovered and fixed.


Initial entries

- None yet (scan pending)