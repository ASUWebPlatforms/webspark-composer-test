# Testing your subsite with Playwright

[Installation](#installation) •
[Getting the site ready](#getting-the-site-ready) •
[Writing tests](#writing-tests) •
[Running tests](#running-tests) •
[Dealing with failures](#dealing-with-failures)

The Webspark team has chosen [Playwright](https://playwright.dev) for front-end testing. This README serves as a guide for how to begin testing your specific subsite with Playwright.

## Installation

Please refer to `./test/playwright/README.md` for documentation on installing Playwright.

<div align="right"><a href="#testing-your-subsite-with-playwright">↑ Top</a></div>
<br>
<br>

## Getting the site ready

This will largely depend on your specific situation. For Webspark, the team prefers to use a clean install of Drupal in order to verify all components are working properly. You do not have to follow this, and it is perfectly okay to run your tests against a test or even live environment.

If you choose to test against an existing environment, please be sure to clean up after any tests that may create or alter data. You would not want to leave behind junk content in the database of your site!

If you wish to test your site with a clean install, please refer to `./test/playwright/README.md` for tips on setting up your site for testing with a clean install.

<div align="right"><a href="#testing-your-subsite-with-playwright">↑ Top</a></div>
<br>
<br>

## Writing tests

If you maintain a subsite (e.g., analytics.asu.edu, search.asu.edu, news.asu.edu) and want to add Playwright tests, please keep in mind that:

- Your tests should be isolated from core Webspark tests
- You don't need Webspark-specific environment variables (like `DRUPAL_USER`)
- Your tests should run independently with their own project configuration
- You only ever want to run **your** tests

### 1. Add your project to the subsites config

Edit `./test/playwright/subsites.config.js` and add a new project entry for your subsite:

```js
export const subsites = {
  //...
  <subsite>: {
    url: process.env.<SUBSITE>_URL || 'https://<subsite>.asu.edu',
    tag: '@<subsite>',
  },
}
```

For example:

```js
export const subsites = {
  analytics: {
    url: process.env.ANALYTICS_URL || 'https://analytics.asu.edu',
    tag: '@analytics',
  }
}
```

#### 1a. Add an entry to the DDEV config (optional)

Notice the use of `process.env.<SUBSITE>_URL`. This is optional, but if you would prefer not to have your subsite URL visible to all, you can instead utilize DDEV environment variables. Add a new variable to `.ddev/.env` and add your URL there. As this file is meant to contain sensitive data, **do not commit this file to code!**

For example:

```env
# .ddev/.env
ANALYTICS_URL="https://analytics.asu.edu"
```

After you have added your value, you will need to restart DDEV in order for it to be read:

```bash
ddev restart
```

### 2. Create a folder for your subsite tests

Create a new folder under `./test/playwright/tests` named after your subsite:

```bash
mkdir -p ./test/playwright/tests/<subsite>
```

For example:

```bash
mkdir -p ./test/playwright/tests/analytics
```

### 3. Create your test files

When creating your tests, follow this naming convention:

```
<SUBSITE>_<FEATURE>_<NUMBER>_description.spec.js
```

For example:

```
ANALYTICS_SAML_001_redirect.spec.js
```

Following this convention not only keeps the project organized, but it also allows you to run your tests in a logical order if needed.

### 4. Add tags to your tests

Within your test, use tags to make it easier to filter your tests by project and purpose:

```js
test.describe('My feature', { tag: ['@<subsite>'] }, () => {})
```

For example:

```js
// ./test/playwright/tests/analytics/ANALYTICS_SAML_001_redirect.spec.js
import { test, expect } from '@playwright/test'

test.describe('Analytics login redirect', { tag: ['@analytics'] }, () => {
  test('verify SAML redirect', async ({ page }) => {
    await page.goto('/')
    await expect(page.getByText('Not a student or faculty/staff?')).toBeVisible()
  })
})
```

### 5. Run your subsite tests

```bash
# Run all tests for your subsite by project
ddev playwright test --project <subsite>

# Run all tests for your subsite by tag
ddev playwright test --grep @<subsite>
```

For example:

```bash
ddev playwright test --project analytics
# or
ddev playwright test --grep @analytics
```

### 6. Add a README to your folder (optional)

Create `./test/playwright/tests/<subsite>/README.md` so that you can document:

- Any special setup requirements
- Needed environment variables (if any)
- Links to relevant documentation or resources

### Tips for writing tests

Please refer to `./test/playwright/README.md` for tips on writing your tests.

<div align="right"><a href="#testing-your-subsite-with-playwright">↑ Top</a></div>
<br>
<br>

## Running tests

Please refer to `./test/playwright/README.md` for documentation on the various ways you can run your tests.

<div align="right"><a href="#testing-your-subsite-with-playwright">↑ Top</a></div>
<br>
<br>

## Dealing with failures

Please refer to `./test/playwright/README.md` for tips on dealing with failures in your tests.

<div align="right"><a href="#testing-your-subsite-with-playwright">↑ Top</a></div>
<br>
<br>
