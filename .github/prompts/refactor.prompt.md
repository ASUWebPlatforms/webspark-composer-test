---
name: "refactor"
description: "Refactor code while applying the correct project coding standards"
agent: "agent"
---

# Instruction-Aware Refactor Prompt

Your goal is to refactor the target file(s) while preserving behavior and applying the correct coding standards for this repository.

## 1) Determine Applicable Instructions

Inspect the file path(s) and apply the most specific instructions:

- **Drupal files**: Any file under `docroot/**` must follow `.github/instructions/drupal.instructions.md`.
- **Playwright tests**: Any file under `test/playwright/**` must follow `.github/instructions/playwright.instructions.md`.
- **Global constraints**: Always apply `.github/copilot-instructions.md` for repository rules and all other files.

If multiple instruction sets apply, prioritize the most specific (path-based) rules first, then global rules.

## 2) Hard Repository Rules

- **Never modify Webspark core without permission**: Do not edit `docroot/profiles/contrib/webspark/` unless the user has given you permission to do so.
- **Dependencies**: Do not add dependencies to root `composer.json`. Use the custom dependency system.
- **Multi-site awareness**: Avoid changes that would break other sites; prefer safe, backward-compatible refactors.
- **Default to ASCII**: Avoid adding non-ASCII unless the file already uses it and it is justified.

## 3) Refactor Principles

- Preserve all existing behavior unless the user explicitly requests functional changes.
- Prefer small, focused changes with clear intent.
- Improve readability, maintainability, and testability.
- Avoid style-only edits unless needed to support the refactor or outlined coding standards.

## 4) Drupal-Specific Refactor Rules (for docroot/\*\*)

- Follow Drupal coding standards and best practices.
- Use Drupal APIs (Entity, Form, Render) instead of direct DB queries.
- Use dependency injection and services instead of globals/static calls.
- Use render arrays and theming, not raw HTML output.
- Sanitize output, validate input, and enforce permissions.
- Use Translation API for user-facing strings.
- Implement cache tags/contexts when rendering.
- Use vanilla JS with `Drupal.behaviors` (avoid jQuery unless unavoidable).
- Keep business logic out of templates.
- Ensure PHPDoc/DocBlocks follow Drupal standards:
  - All public functions, hooks, and class methods must have complete DocBlocks.
  - Include correct `@param` types, names, and descriptions.
  - Include accurate `@return` types and descriptions.
  - Include `@throws` for all thrown exceptions.
  - Use full, grammatical sentences and end descriptions with periods.
  - Use fully qualified class names where required by Drupal standards.
- Variables should be named using lowercase, and words should be separated with uppercase characters (camelCase).
- Functions should be named using lowercase, and words should be separated with an underscore (snake_case).
- Functions should in addition have the grouping/module name as a prefix, to avoid name collisions between modules.

## 5) Playwright-Specific Refactor Rules (for test/playwright/\*\*)

- Use the Page Object Model (POM) with `Node`/`Block` inheritance.
- Use accessible locators (`getByRole`, `getByLabel`, `getByText`, `getByAltText`).
- Keep tests focused, small, and independent.
- Use `test.describe()` with tags and `beforeAll`/`afterAll` when needed.
- Use `mode: 'serial'` when tests share state.
- Use faker data; avoid hardcoded test data unless required.
- Respect naming: `WS_[CATEGORY]_[NUMBER]_[feature-name].spec.js`.

## 6) Refactor Workflow

1. **Analyze** the target file(s) and identify code smells or duplication.
2. **Plan** minimal, safe changes and note any behavior risks.
3. **Refactor** while preserving behavior and applying correct standards.
4. **Update references** or dependent code if needed.
5. **Validate**: suggest or run relevant tests/linters for the file type.

## 7) Output Requirements

When done, provide:

- The file(s) refactored.
- A short explanation of the changes and why they were made.
- Any recommended tests to run.
- Any assumptions or limitations.
