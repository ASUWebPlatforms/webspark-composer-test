---
name: "pw generate"
agent: "agent"
description: "Generate a Playwright test based on a scenario using Playwright MCP"
---

# Test Generation with Playwright MCP

Your goal is to generate a complete, working Playwright test based on the provided scenario, following all coding standards outlined in `.github/instructions/playwright.instructions.md`.

## Before You Begin

Familiarize yourself with the Playwright coding standards. Key requirements:

- **File naming**: `WS_[CATEGORY]_[NUMBER]_[feature-name].spec.js`
- **Test structure**: `test.describe()` with tags, `beforeAll()`/`afterAll()`, serial mode when needed
- **Page Object Model**: Create or use existing models (inheriting from `Node` or `Block`)
- **Locators**: Accessible locators only (`getByRole`, `getByLabel`, `getByText`, `getByAltText`)
- **Test cases**: Keep focused, independent, and single-purpose
- **Assertions**: Verify outcomes with expect(), avoid hard timeouts
- **Test data**: Use `faker.js` for realistic test data

## Specific Instructions

1. **Clarify the scenario**: If the user does not provide a scenario, ask for:
   - What feature/component to test (block, content type, page, etc.)
   - What user interaction/workflow to test
   - What expected outcomes should be verified
   - Whether this tests Webspark-specific or site-specific functionality

2. **Verify test scope**: Confirm the scenario is testing Webspark-specific (or site-specific) functionality ONLY:
   - Do NOT test Drupal core features
   - Do NOT test UDS components unless customized
   - Do NOT test third-party code unless modified by Webspark
   - If testing a customization, document what the override/customization is

3. **Explore and document**: Use Playwright MCP to:
   - Navigate to the test URL
   - Interact with the feature step by step
   - Document exact locators for each UI element
   - Note any async operations (AJAX, CSS animations)
   - Capture form fields, buttons, state changes, and error conditions
   - Identify any special Drupal attributes (drupal-data-selector, etc.)

4. **Design the test structure**:
   - Determine correct base model class to extend (`Node` for content, `Block` for components)
   - Plan required selectors (input* prefix for forms, el* prefix for elements)
   - Plan required methods (add, addContent, verify, update, delete, etc.)
   - Determine if tests need `mode: 'serial'`
   - Plan test cases (create, verify, possibly update/delete variants)

5. **Create or update the model class**:
   - If model doesn't exist, create it extending appropriate base class
   - Add all required selectors following naming conventions
   - Add methods for interactions (add, addContent, verify, etc.)
   - Use faker.js for test data generation
   - Keep component-specific logic out of the test file

6. **Generate the test file**:
   - Use correct naming: `WS_[CATEGORY]_[NUMBER]_[feature-name].spec.js`
   - Follow test structure: imports, page/model declarations, test.describe with tags
   - Include beforeAll/afterAll hooks
   - Write focused test cases (create, verify, update, delete)
   - Use accessible locators in model, not in test
   - Add comments for non-obvious logic
   - Use test.fixme() for known bugs

7. **Validate against checklist** before executing:
   - [ ] Test file named correctly per convention
   - [ ] Extends appropriate base model (Node, Block)
   - [ ] Uses accessible locators (no hardcoded CSS/XPath)
   - [ ] Has beforeAll/afterAll setup/teardown
   - [ ] Uses test.describe with tags (@webspark, @category, etc.)
   - [ ] Uses mode: 'serial' if tests share state
   - [ ] Test data from faker.js (not hardcoded)
   - [ ] Each test focused on single aspect
   - [ ] Proper assertions with expect()
   - [ ] Handles async operations (no hard timeouts)
   - [ ] Comments for complex logic
   - [ ] Only tests Webspark-specific functionality
   - [ ] Model selectors use input*/el* prefixes
   - [ ] Methods abstracted from individual tests

8. **Execute and iterate**:
   - Save the test file in `test/playwright/tests/webspark/` (or appropriate subfolder)
   - Run the test using `ddev playwright test [filename]`
   - If test fails, analyze the failure (screenshot, logs, error message)
   - Iterate on test code and model as needed
   - Ensure test passes consistently before completion
   - DO NOT commit if test is marked with test.fixme() unless documented

9. **Final verification**:
   - Test runs and passes on first execution
   - Test is reproducible (runs successfully multiple times)
   - Test uses only Webspark-specific functionality
   - Code follows all standards in the instructions file
   - Model is reusable for other tests
   - Test report shows no accessibility violations (if applicable)

## Output

Upon completion, provide:

- Path to the created/updated test file
- Path to the created/updated model file (if new)
- Summary of what the test verifies
- Any special considerations or known issues
- Test execution results (passing/failing)
