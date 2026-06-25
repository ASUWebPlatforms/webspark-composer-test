---
name: "pw explore"
description: "Website exploration for testing using Playwright MCP"
agent: "agent"
---

# Website Exploration for Testing

Your goal is to explore the website and identify key functionalities that align with Webspark-specific (or site-specific) features, then propose test cases following the Playwright coding standards.

## Before You Begin

Review the Playwright coding standards at `.github/instructions/playwright.instructions.md`. Pay special attention to:

- **What to test**: Only Webspark-specific or site-specific features. Do NOT test Drupal core, UDS, or third-party functionality.
- **Page Object Model (POM)**: Tests should use models that inherit from `Node` (for content) or `Block` (for components).
- **File naming**: `WS_[CATEGORY]_[NUMBER]_[feature-name].spec.js` (e.g., `WS_BLOCK_001_accordion.spec.js`)
- **Test structure**: Use `test.describe()` with tags, `beforeAll()`/`afterAll()`, and serial mode when needed.
- **Locators**: Use accessible locators (`getByRole`, `getByLabel`, `getByText`, `getByAltText`) over CSS/XPath.

## Specific Instructions

1. **Navigate and explore**: Use the Playwright MCP Server to navigate to the provided URL. If no URL is provided, ask the user for one.
2. **Identify Webspark-specific features**: Focus on features unique to Webspark or the target site:
   - Custom blocks in Layout Builder
   - Brand/layout components (headers, footers, menus)
   - Content types and fields
   - Customized CKEditor plugins
   - Site-specific workflows or interactions
   - Do NOT explore standard Drupal core functionality or UDS components unless customized.
3. **Document user interactions**: For 3-5 core features:
   - Record the user interaction (click, fill, select, etc.)
   - Capture locators using accessible methods (`getByRole`, `getByLabel`, etc.)
   - Note the expected outcome and UI changes
   - Identify any form fields, buttons, or state changes
4. **Identify model requirements**: Based on interactions, determine:
   - What base class to extend (`Node` for content, `Block` for components)
   - What selectors and methods are needed in the model
   - Whether tests should run in serial mode (if state matters)
5. **Document locator strategies**: For each element:
   - Prefer accessible locators (role, label, text, alt text)
   - Use exact matching (`exact: true`) where needed for text
   - Note any data attributes or unique identifiers
   - Avoid hardcoded indices; use role-based or content-based selectors
6. **Close the browser**: Close the browser context when complete.
7. **Provide findings summary**: Document:
   - Features explored and their purposes
   - Key user workflows identified
   - Proposed model classes (extending Node/Block)
   - Recommended test cases (CRUD operations, variant testing, error handling)
8. **Propose test cases**: Based on exploration, suggest:
   - Test file name and category
   - Test structure (create, verify, update, delete tests)
   - Model class name and required selectors
   - Tags for filtering (`@webspark`, `@block`, etc.)
   - Any special considerations (serial mode, fixtures, etc.)
