---
name: "Playwright Coding Standards"
description: "Coding standards and conventions for Playwright"
applyTo: "test/playwright/**/*"
---

# Playwright E2E Testing Standards & Conventions

This document provides comprehensive guidelines for creating and refactoring Playwright tests in the ASU Factory Stack 1 project. Our testing approach emphasizes the Page Object Model (POM), accessibility-first locators, and testing only Webspark-specific functionality.

## Table of Contents

- [Core Philosophy](#core-philosophy)
- [Test Structure & Organization](#test-structure--organization)
- [Page Object Model (POM) Patterns](#page-object-model-pom-patterns)
- [Test Writing Guidelines](#test-writing-guidelines)
- [Locator Best Practices](#locator-best-practices)
- [Helpers & Utilities](#helpers--utilities)
- [Test Data Management](#test-data-management)
- [Handling Asynchronous Operations](#handling-asynchronous-operations)
- [Test Naming Conventions](#test-naming-conventions)
- [Debugging & Troubleshooting](#debugging--troubleshooting)
- [Common Patterns & Examples](#common-patterns--examples)

---

## Core Philosophy

### What to Test

- **Test Webspark-specific or site-specific functionality only**: Do not test Drupal core features, UDS (Universal Design System) components, or third-party modules unless Webspark or a specific sub-site has customized them.
- **Test user workflows**: Focus on real user interactions and outcomes that users care about.
- **Test front-end behavior**: Verify what users see and interact with in the browser.
- **Test Drupal content management**: Verify that content creators can successfully create, edit, and manage content.

### What NOT to Test

- **Drupal core functionality**: Let Drupal's core test suite handle core features.
- **UDS component behavior**: UDS has its own test suite; we test how Webspark uses them.
- **Server-side operations**: Do not test PHP hooks, database queries, or Drupal API internals—use PHPUnit for that.
- **Third-party libraries**: Only test if Webspark or a sub-site has customized the behavior.

---

## Test Structure & Organization

### File Organization

```
test/playwright/
├── tests/
│   ├── login.setup.js              # Authentication setup for all tests
│   ├── webspark/                   # Webspark-specific tests
│   │   ├── WS_BLOCK_001_accordion.spec.js
│   │   ├── WS_BLOCK_002_blockquote.spec.js
│   │   ├── WS_BRAND_001_anchor-menu.spec.js
│   │   ├── WS_NODE_001_article.spec.js
│   │   ├── WS_PAGE_001_home.spec.js
│   │   └── ...
│   ├── analytics/                  # Site-specific tests
│   │   └── ANALYTICS_SAML_001_redirect.spec.js
│   └── [sitename]/                 # Site-specific tests (for subsite projects)
│
├── models/                          # Page Object Models
│   ├── Node.js                      # Base class for content nodes
│   ├── BasicPage.js                 # For page content types
│   ├── Article.js                   # For article content types
│   ├── Block.js                     # Base class for blocks
│   ├── WebsparkBlocks.js            # Webspark-specific block models
│   ├── WebsparkCards.js             # Webspark card components
│   ├── AsuBrand.js                  # Brand/layout component models
│   ├── React.js                     # React component models
│   ├── Menu.js                      # Menu component models
│   ├── DegreeBlocks.js              # Degree-related block models
│   ├── CKEditor.js                  # CKEditor extension models
│   └── ...
│
├── helpers/
│   ├── Drupal.js                    # Global Drupal operations
│   └── Drush.js                     # Drush command execution
│
├── playwright.config.js             # Playwright configuration
├── subsites.config.js               # Multi-site configuration
└── README.md                        # Testing documentation
```

### Test File Naming Convention

Test files use a consistent naming pattern:

```
WS_[CATEGORY]_[NUMBER]_[feature-name].spec.js
```

**Components:**

- `WS_`: Prefix for Webspark tests (use `ANALYTICS_`, etc. for other sites)
- `[CATEGORY]`: Type of component being tested:
  - `BLOCK`: Layout Builder block components
  - `BRAND`: ASU Brand/layout components
  - `NODE`: Content types/nodes
  - `PAGE`: Page templates and page-level features
  - `MOBILE`: Mobile-specific tests
  - `CKE`: CKEditor plugin tests
  - `[FEATURE]`: Custom category for other features
- `[NUMBER]`: Padded sequence number (001, 002, etc.)
- `[feature-name]`: Kebab-case description of the test

**Examples:**

```
WS_BLOCK_001_accordion.spec.js
WS_BLOCK_007_cards-degree.spec.js
WS_BRAND_005_footer.spec.js
WS_NODE_003_degree-listing.spec.js
WS_PAGE_002_error-404.spec.js
ANALYTICS_SAML_001_redirect.spec.js
```

### Test File Structure

All test files follow this standard structure:

```javascript
import { test, expect } from "@playwright/test";
import { ModelName } from "../../models/ModelName.js";

/** @type {import('@playwright/test').Page} */
let page;
let model;
const title = "Component Name";

test.describe(title, { tag: ["@webspark", "@category"] }, () => {
  test.describe.configure({ mode: "serial" });

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage();
    model = new ModelName(page, title);
  });

  test.afterAll(async () => {
    await page.close();
  });

  test("create", async () => {
    await model.add();
    // Additional setup steps...
    await model.save();
  });

  test("verify", async () => {
    await model.verify();
  });

  test("update", async () => {
    // ... optional test cases
  });

  test("delete", async () => {
    // ... optional test cases
  });
});
```

**Key points:**

- Use `test.describe()` to group related tests with a clear title.
- Always tag tests for filtering (see [Test Tags](#test-tags) below).
- Use `test.describe.configure({ mode: 'serial' })` to run tests sequentially when state matters.
- Create page and model instances in `beforeAll()` to persist state across tests.
- Clean up resources in `afterAll()`.
- Keep test names simple and action-oriented (`create`, `verify`, `update`, `delete`).

### Test Tags

Organize tests with tags for easy filtering:

```javascript
test.describe(
  "Accordion Block",
  { tag: ["@webspark", "@block", "@variant-color"] },
  () => {
    // Tests here
  },
);
```

**Standard tags:**

- `@webspark`: All Webspark-specific tests
- `@block`: Layout Builder block tests
- `@brand`: Brand/layout component tests
- `@node`: Content type/node tests
- `@page`: Page template tests
- `@mobile`: Mobile-specific tests
- `@performance`: Performance-related tests
- `@a11y`: Accessibility tests
- Feature or variant tags: `@variant-color`, `@image-field`, etc.

**Running tests by tag:**

```bash
ddev playwright test --grep @webspark
ddev playwright test --grep @block
ddev playwright test --grep-invert @mobile  # Exclude mobile tests
```

---

## Page Object Model (POM) Patterns

The Page Object Model is a design pattern that encapsulates page/component UI and interactions into reusable classes. This keeps tests clean, maintainable, and DRY.

### Base Classes

**Node.js** – Base class for all content nodes:

```javascript
import { expect } from "@playwright/test";
import drupal from "../helpers/Drupal";

class Node {
  /**
   * Base Node model for Playwright tests.
   * @param {import('playwright').Page} page
   * @param {string} name
   */
  constructor(page, name) {
    this.page = page;
    this.name = name;
    this.url = null;
    this.alias = null;
    this.path = null;
    this.nid = null;

    // Common input selectors
    this.inputTitle = page.getByRole("textbox", { name: "Title *" });
    this.inputSave = page.getByRole("button", { name: "Save" });
    this.inputDelete = page.getByRole("button", { name: "Delete" });
    this.status = page.getByRole("status", { name: "Status message" });
  }

  async add() {
    throw new Error("add() must be implemented in the subclass");
  }

  async addContent() {
    throw new Error("addContent() must be implemented in the subclass");
  }

  async save() {
    await this.inputSave.click();
  }

  async edit() {
    await this.page.goto(`${this.path}/edit`);
  }

  async delete() {
    await this.page.goto(`${this.path}/delete`);
    await this.inputDelete.click();
  }

  async goToLayout() {
    await this.page.goto(`${this.path}/layout`);
  }

  async setNodeProperties() {
    // Captures node URL, alias, path, and nid for later use
    await this.#setNodeUrl();
    await this.#setNodeAlias();
    await this.#setNodePath();
    await this.#setNodeId();
  }

  // Helper methods (private)
  async #setNodeUrl() {
    this.url = this.page.url();
  }

  async #setNodeAlias() {
    if (!this.url) return;
    try {
      const urlObject = new URL(this.url);
      this.alias = urlObject.pathname;
    } catch (error) {
      console.error(`Error parsing URL: ${error}`);
    }
  }

  async #setNodePath() {
    if (!this.alias) return;
    try {
      this.path = await drupal.getNodePath(this.alias);
    } catch (error) {
      console.error(`Error getting node path: ${error}`);
    }
  }

  async #setNodeId() {
    try {
      this.nid = await drupal.getNodeIdByAlias(this.alias);
    } catch (error) {
      console.error(`Error getting node ID: ${error}`);
    }
  }
}

export { Node };
```

**Block.js** – Base class for all blocks:

```javascript
import { expect } from "@playwright/test";
import { faker } from "@faker-js/faker/locale/en";

class Block {
  /**
   * Base Block model for Playwright tests.
   * @param {import('playwright').Page} page
   * @param {string} name
   */
  constructor(page, name) {
    this.page = page;
    this.name = name;

    // Common block inputs
    this.inputAddBlock = page.getByRole("link", {
      name: "Add block in Content, First region",
    });
    this.inputAddBlockFirstRegion = page.getByRole("link", {
      name: "Add block in Top, First region",
    });
    this.inputCreateContentBlock = page.getByRole("link", {
      name: "Create content block",
    });
    this.inputAddByName = page.getByRole("link", { name: name, exact: true });
    this.inputBlockAdminTitle = page.getByRole("textbox", {
      name: "Block admin title",
    });
    this.inputDisplayBlockTitle = page.getByRole("checkbox", {
      name: "Display title",
    });
    this.inputSaveBlock = page.getByRole("button", { name: "Add block" });
    this.inputUpdateBlock = page.getByRole("button", { name: "Update" });
    this.inputSaveLayout = page.getByRole("button", { name: "Save layout" });
    this.inputAppearanceSettings = page.getByRole("button", {
      name: "Appearance Settings",
    });
  }

  async add() {
    await this.inputAddBlock.click();
    await this.inputCreateContentBlock.click();
    await this.inputAddByName.click();
    await this.inputBlockAdminTitle.fill(this.name);
  }

  async addContent() {
    throw new Error("addContent() must be implemented in the subclass");
  }

  async save() {
    await this.inputSaveBlock.click();
    await this.inputSaveLayout.click();
  }

  async update() {
    await this.inputUpdateBlock.click();
    await this.inputSaveLayout.click();
  }
}

export { Block };
```

### Extending Base Classes

Create specific models by extending base classes and adding component-specific locators and methods:

```javascript
import { expect } from "@playwright/test";
import { faker } from "@faker-js/faker/locale/en";
import { Block } from "./Block";

export class Accordion extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();
    this.content = faker.lorem.paragraph();

    // Input selectors
    this.inputColorOptions = page.getByRole("combobox", {
      name: "Color Options",
    });
    this.inputHeading = page.getByRole("textbox", { name: "Heading" });
    this.inputContent = page
      .getByLabel("Rich Text Editor")
      .getByRole("textbox");
    this.inputExpanded = page.getByRole("checkbox", {
      name: "Initially Expanded",
    });

    // Element selectors (for verification)
    this.el = page.locator(".accordion-item.accordion-item-maroon");
    this.elIcon = page.locator(".accordion-header.accordion-header-icon");
    this.elHeading = page.getByRole("button", { name: this.heading });
    this.elContent = page.getByText(this.content, { exact: true });
  }

  async addContent() {
    await this.inputColorOptions.selectOption({ label: "Maroon" });
    await this.inputHeading.fill(this.heading);
    await this.inputContent.fill(this.content);
    await this.inputExpanded.setChecked(true);
  }

  async verify() {
    await expect(this.el).toBeVisible();
    await expect(this.elHeading).toBeVisible();
    await expect(this.elContent).toBeHidden();

    // Verify interaction
    await this.elHeading.click();
    await expect(this.elContent).toBeVisible();
  }
}
```

### Model Selector Naming Convention

Organize selectors with clear prefixes:

```javascript
// Input/form field selectors – prefix with 'input'
this.inputTitle = page.getByRole("textbox", { name: "Title" });
this.inputAuthor = page.getByRole("textbox", { name: "Author" });
this.inputColorOptions = page.getByRole("combobox", { name: "Color" });
this.inputCheckbox = page.getByRole("checkbox", { name: "Published" });

// Element/output selectors – prefix with 'el' or 'el' + descriptor
this.el = page.locator(".card");
this.elHeading = page.getByRole("heading", { name: "Title" });
this.elContent = page.getByText("Content text");
this.elImage = page.getByRole("img", { name: "Image alt text" });
this.elSuccessMessage = page.getByRole("status");
```

---

## Test Writing Guidelines

### 1. Keep Tests Focused and Independent

Each test should verify a single aspect of functionality:

```javascript
// ✅ Good: Each test has a clear, specific purpose
test("create accordion with color option", async () => {
  await block.add();
  await block.selectColor("Maroon");
  await block.save();
});

test("verify accordion displays with selected color", async () => {
  await expect(block.el).toHaveClass(/maroon/);
});

// ❌ Avoid: Tests that do too many things
test("full accordion workflow", async () => {
  await block.add();
  await block.selectColor("Maroon");
  await block.save();
  await block.verify();
  // ... more assertions
});
```

### 2. Use Serial Mode When Tests Depend on Shared State

If tests share state (e.g., a node created in test A is edited in test B), use `mode: 'serial'`:

```javascript
test.describe("Article workflow", { tag: ["@webspark", "@node"] }, () => {
  test.describe.configure({ mode: "serial" }); // Run tests sequentially

  test("create", async () => {
    await article.add();
  });

  test("edit", async () => {
    await article.edit(); // Uses state from previous test
    await article.addContent();
  });

  test("verify", async () => {
    await article.verify(); // Uses state from previous tests
  });
});
```

### 3. Leverage Test Fixtures

Use Playwright's `beforeAll` and `afterAll` hooks for setup/teardown:

```javascript
test.beforeAll(async ({ browser }) => {
  // Setup happens once before all tests in this describe block
  page = await browser.newPage();
  model = new BlockName(page, "Test Block");
});

test.afterAll(async () => {
  // Cleanup happens after all tests complete
  await page.close();
});

test.beforeEach(async () => {
  // (Optional) Runs before each test
  await page.goto("/");
});

test.afterEach(async () => {
  // (Optional) Runs after each test
  // Use for cleanup if needed
});
```

### 4. Use Assertions Properly

- Use `expect()` for verification
- Prefer status/success checks over just completing actions
- Verify both visibility and content

```javascript
test("create page", async () => {
  await page.goto("/node/add/page");
  await page.getByRole("textbox", { name: "Title" }).fill("Test Page");
  await page.getByRole("button", { name: "Save" }).click();

  // Verify success
  await expect(
    page.getByRole("status", { name: "Status message" }),
  ).toHaveClass(/alert-success/);

  // Verify the page was created
  await expect(page.getByRole("heading", { name: "Test Page" })).toBeVisible();
});
```

### 5. Handle Errors and Edge Cases

Test error states and expected failures:

```javascript
test("display validation error for missing required field", async () => {
  await page.goto("/node/add/page");
  // Skip filling the title
  await page.getByRole("button", { name: "Save" }).click();

  // Verify error message appears
  await expect(page.getByText("Title field is required")).toBeVisible();
});
```

### 6. Use Page.fixme() for Known Bugs

When a test fails due to a bug in the component (not a test issue), mark it with `test.fixme()`:

```javascript
// FIXME: https://asudev.jira.com/browse/WS-1234 – Color selector not visible
test.fixme("verify accordion color applies correctly", async () => {
  await block.verify();
});
```

This signals that the test logic is correct but the feature has a known bug.

### 7. Add Comments for Complex Operations

Document non-obvious test logic:

```javascript
test("create block with media field", async () => {
  await block.add();

  // Wait for media modal to load before selecting media
  const mediaModal = page.locator(".media-library-widget-modal");
  await expect(mediaModal).toBeVisible();

  await block.selectMedia("sample");
  await block.save();
});
```

---

## Locator Best Practices

### Use Accessible Locators

Always prefer accessible locators in this order:

1. **getByRole()** – Most accessible, finds elements by ARIA role
2. **getByLabel()** – For form fields with associated labels
3. **getByText()** – For text content (use `exact: true` for precision)
4. **getByPlaceholder()** – For placeholder text in inputs
5. **getByAltText()** – For images with alt text

Only use `locator()` for CSS/XPath when the above don't work:

```javascript
// ✅ Best: Using accessible locators
const button = page.getByRole("button", { name: "Save" });
const input = page.getByLabel("Title");
const heading = page.getByRole("heading", { name: "Welcome" });
const image = page.getByAltText("Sample image");

// ⚠️ Acceptable: When accessible locators aren't sufficient
const icon = page.locator(".icon-down-dir").first();
const marker = page.locator('[data-testid="special-marker"]');

// ❌ Avoid: CSS/XPath for simple elements
page.locator("button.save-btn"); // Use getByRole instead
page.locator('input[name="title"]'); // Use getByLabel instead
```

### Use Exact Matching

When text matching, use `exact: true` to be precise:

```javascript
// ✅ Good: Exact matching prevents false positives
const link = page.getByRole("link", { name: "Home", exact: true });

// ❌ Problematic: Partial match could select wrong element
const link = page.getByRole("link", { name: "Home" }); // Might match "Home Page" too
```

### Leverage Attributes for Complex Elements

Use data attributes when available:

```javascript
// In Layout Builder, use drupal-data-selector to target dynamic elements
const blockItem = page.locator(
  '[drupal-data-selector="layout.section.0.block.0"]',
);
```

### Avoid Overly Specific Selectors

Make selectors resilient to layout/styling changes:

```javascript
// ❌ Too specific: Breaks if CSS classes change
const block = page.locator("div.ws-block.blue.mt-4.p-8");

// ✅ Better: Target by semantic role or content
const block = page.locator(".ws-block").filter({ hasText: "Content" });
```

---

## Helpers & Utilities

### Drupal Helper (`drupal.js`)

Use the Drupal helper for global operations that should not be in models:

```javascript
import drupal from "../helpers/Drupal";

// Enable/disable modules
await drupal.enableModule("module_name");
await drupal.disableModule("module_name");

// Login helpers
await drupal.loginAsAdmin(page);
await drupal.loginAsUser(page, "username");

// Global Drupal operations
const siteName = await drupal.getSiteName();
const nodePath = await drupal.getNodePath("/about");
const nodeId = await drupal.getNodeIdByAlias("/about");

// Common field operations
await drupal.addMediaField(page);
await drupal.addCTAField(page, 0, "https://asu.edu", "Click here", "Maroon");
```

**When to add to Drupal helper:**

- Global, site-wide operations
- Operations that affect multiple models
- Drupal API calls that should be centralized
- Utilities for module management, login, site config

**When NOT to add:**

- Model-specific operations (add to the model)
- Component-specific interactions (add to the model)

### Drush Helper (`drush.js`)

The Drush helper wraps Drush command execution:

```javascript
import drush from "../helpers/Drush";

await drush.rebuild();
await drush.enableModule("module_name");
await drush.updateDB();
const adminUrl = await drush.getAdminLogin();
```

Keep Drush calls in helpers only; avoid calling Drush directly in tests.

---

## Test Data Management

### Use Faker.js for Consistent Test Data

Generate realistic, random test data using faker.js:

```javascript
import { faker } from "@faker-js/faker/locale/en";

class Article extends Node {
  constructor(page, name) {
    super(page, name);

    // Generate unique test data in constructor
    this.title = `Playwright ${this.name}`;
    this.author = faker.person.fullName();
    this.byline = faker.lorem.sentence();
    this.body = faker.lorem.sentence();
    this.excerpt = faker.lorem.words(5);
  }

  async add() {
    await this.page.goto("/node/add/article");
    await this.inputTitle.fill(this.title);
    await this.inputAuthor.fill(this.author);
    await this.inputByline.fill(this.byline);
    await this.save();
  }
}
```

**Faker method categories:**

- `faker.person.fullName()` – Random names
- `faker.lorem.sentence()` – Random sentences
- `faker.lorem.paragraph()` – Random paragraphs
- `faker.lorem.words(n)` – Random word lists
- `faker.company.name()` – Company names
- `faker.internet.url()` – URLs
- `faker.date.future()` – Dates

### Keep Test Data Realistic

- Use meaningful placeholder text
- Match the expected data length/type
- Avoid hardcoded values unless testing specific content

```javascript
// ✅ Good: Realistic placeholder data
this.heading = faker.lorem.words(3); // 3-word heading
this.content = faker.lorem.paragraph(); // Full paragraph
this.phone = faker.phone.number("(###) ###-####");

// ❌ Avoid: Generic or meaningless data
this.heading = "asdf";
this.content = "test test test";
this.phone = "1234567890";
```

---

## Handling Asynchronous Operations

### Wait for Network Requests

For AJAX calls and other async operations, wait for the request to complete:

```javascript
// Wait for a specific response
const responsePromise = page.waitForResponse((response) =>
  response.url().includes("/layout"),
);
await page.getByRole("button", { name: "Save" }).click();
const response = await responsePromise;

if (!response.ok()) {
  throw new Error(`Save failed: ${response.status()}`);
}

// Verify page updated after AJAX
await expect(page.getByText("Saved successfully")).toBeVisible();
```

### Wait for Elements

Use built-in visibility waits instead of `setTimeout`:

```javascript
// ✅ Good: Wait for element visibility
await expect(page.getByRole("heading", { name: "Welcome" })).toBeVisible();

// ✅ Good: Wait for condition
await expect(page.getByRole("status")).toHaveText(/success/);

// ❌ Avoid: Hard timeouts
await page.waitForTimeout(2000);
```

### Handle Page Navigation

Be explicit about waiting for navigation:

```javascript
await page.goto("/about");
await expect(page).toHaveURL(/.*about/);
```

---

## Test Naming Conventions

### Test Describe Blocks

Use clear, descriptive titles:

```javascript
// ✅ Good
test.describe("Accordion Block", () => {});
test.describe("Hero Image - Large Viewport", () => {});
test.describe("Login Workflow", () => {});

// ❌ Avoid
test.describe("Test", () => {});
test.describe("Component", () => {});
```

### Test Cases

Use action-oriented names that describe what is being tested:

```javascript
// ✅ Good: Clear action and subject
test("create accordion block", async () => {});
test("verify accordion expands on click", async () => {});
test("update accordion color", async () => {});
test("delete accordion block", async () => {});
test("display validation error for missing heading", async () => {});

// ✅ Also good: Variant testing
test("create accordion with gold color", async () => {});
test("verify accordion with image variant", async () => {});

// ❌ Avoid
test("test accordion", async () => {});
test("works correctly", async () => {});
test("accordion 1", async () => {});
```

### Standard Test Case Names

Follow this pattern for consistency:

```javascript
// Create/setup tests
test("create", async () => {});
test("create [variant]", async () => {});

// Verification tests
test("verify", async () => {});
test("verify [specific aspect]", async () => {});
test("verify [with variant]", async () => {});

// Interaction tests
test("expand accordion on click", async () => {});
test("navigate to page on link click", async () => {});

// Update/modification tests
test("update [property]", async () => {});
test("update color option", async () => {});

// Deletion tests
test("delete", async () => {});

// Error handling tests
test("display validation error for missing [field]", async () => {});
test("prevent save with invalid [data]", async () => {});
```

---

## Debugging & Troubleshooting

### Run Tests with Debug Mode

```bash
# Run in debug mode with browser visible
ddev playwright test --headed test/playwright/tests/webspark/WS_BLOCK_001_accordion.spec.js

# Run with the Playwright Inspector
ddev playwright test --debug
```

### View Test Reports

```bash
# Generate and view HTML report
ddev playwright show-report --host=0.0.0.0
```

### Use the Playwright UI

```bash
# Interactive test runner (recommended for debugging)
ddev playwright test --ui
```

### Add Debugging Output

When a test fails, add logging to understand state:

```javascript
test("create block", async () => {
  console.log("Page URL before creation:", page.url());
  await block.add();
  console.log("Page URL after creation:", page.url());

  const title = await page.locator("h1").textContent();
  console.log("Page title:", title);

  await block.save();
});
```

### Screenshot on Failure

Playwright automatically captures screenshots on failure. Review them in the test report:

```bash
ddev playwright show-report --host=0.0.0.0
```

### Increase Timeouts for Slow Operations

If tests fail intermittently due to slow page loads:

```javascript
test(
  "slow operation",
  async () => {
    await page.goto("/slow-page", { waitUntil: "networkidle" });
    // Test code...
  },
  { timeout: 60000 },
); // 60 second timeout for this test
```

---

## Common Patterns & Examples

### Pattern 1: Create and Verify Workflow

```javascript
import { test, expect } from "@playwright/test";
import { Accordion } from "../../models/WebsparkBlocks.js";
import { BasicPage } from "../../models/BasicPage.js";

let page, node, block;

test.describe("Accordion Block", { tag: ["@webspark", "@block"] }, () => {
  test.describe.configure({ mode: "serial" });

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage();
    node = new BasicPage(page, "Test Page");
    block = new Accordion(page, "Accordion");
  });

  test.afterAll(async () => {
    await page.close();
  });

  test("create page with accordion", async () => {
    await node.add();
    await node.goToLayout();
    await block.add();
    await block.addContent();
    await block.save();
  });

  test("verify accordion displays correctly", async () => {
    await block.verify();
  });
});
```

### Pattern 2: Multiple Variants

```javascript
import { test, expect } from "@playwright/test";
import { Hero } from "../../models/WebsparkBlocks.js";
import { BasicPage } from "../../models/BasicPage.js";

let page, node, block;

test.describe("Hero Block", { tag: ["@webspark", "@block"] }, () => {
  test.describe.configure({ mode: "serial" });

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage();
    node = new BasicPage(page, "Test Page");
  });

  test.afterAll(async () => {
    await page.close();
  });

  // Default variant
  test("create default hero", async () => {
    block = new Hero(page, "Hero - Default");
    await node.add();
    await node.goToLayout();
    await block.add();
    await block.addContent();
    await block.save();
  });

  test("verify default hero", async () => {
    await block.verify();
  });

  // Image variant
  test("create hero with background image", async () => {
    block = new Hero(page, "Hero - Image");
    await node.goToLayout();
    await block.addVariantWithImage();
    await block.save();
  });

  test("verify hero with background image", async () => {
    await block.verifyWithImage();
  });
});
```

### Pattern 3: Testing User Permissions

```javascript
test("viewer cannot edit page", async () => {
  // Login as viewer (non-admin)
  await drupal.loginAsUser(page, "viewer");
  await page.goto(`/node/${nodeId}/edit`);

  // Verify access is denied
  await expect(page).toHaveURL(/access-denied|404/);
});

test("admin can edit page", async () => {
  // Login as admin
  await drupal.loginAsAdmin(page);
  await page.goto(`/node/${nodeId}/edit`);

  // Verify edit form is visible
  await expect(page.getByRole("button", { name: "Save" })).toBeVisible();
});
```

### Pattern 4: Testing Error States

```javascript
test("display error for missing required field", async () => {
  await page.goto("/node/add/page");
  // Skip filling the title
  await page.getByRole("button", { name: "Save" }).click();

  await expect(page.getByText("Title field is required")).toBeVisible();
});
```

### Pattern 5: Testing Accessibility

```javascript
test("heading has proper level", async () => {
  await page.goto("/");
  const heading = page.getByRole("heading", { level: 1 });
  await expect(heading).toBeVisible();
});

test("form labels are associated with inputs", async () => {
  await page.goto("/node/add/page");
  const input = page.getByLabel("Title");
  await expect(input).toBeVisible();
});

test("color is not the only indicator", async () => {
  // Verify required fields are indicated by text, not just color
  await expect(page.getByText("(required)", { exact: false })).toBeVisible();
});
```

---

## Quick Reference Checklist

When creating or refactoring a Playwright test:

- [ ] Test file named correctly (`WS_CATEGORY_NUMBER_feature-name.spec.js`)
- [ ] Extends appropriate base model (`Node`, `Block`, etc.)
- [ ] Uses accessible locators (getByRole, getByLabel, etc.)
- [ ] Has beforeAll/afterAll for setup/teardown
- [ ] Uses test.describe with tags
- [ ] Uses `mode: 'serial'` if tests depend on shared state
- [ ] Test data generated with faker.js
- [ ] Each test focused on single aspect
- [ ] Good error messages with expect()
- [ ] Handles async operations properly (no hard timeouts)
- [ ] Comments explain non-obvious logic
- [ ] Only tests Webspark-specific functionality
- [ ] No testing of Drupal core, UDS, or third-party code
- [ ] Uses Page Object Model pattern
- [ ] Model selectors named with `input*` and `el*` prefixes
- [ ] Abstract common methods to base classes
