# Testing with Playwright

[Installation](#installation) •
[Getting the site ready](#getting-the-site-ready) •
[Writing tests](#writing-tests) •
[Running tests](#running-tests) •
[Dealing with failures](#dealing-with-failures) •
[Resources](#resources)

We use [Playwright](https://playwright.dev) for front-end testing. We have installed the [Playwright for DDEV add-on](https://github.com/Lullabot/ddev-playwright) to aid in the process. See the Playwright documentation for how to write tests.

Playwright is an end-to-end framework, but our use case is to test the user experience. In a nutshell, we will use it to mimic user operations to be able to see what users see on the front and back end of Webspark sites. Although we do have the ability to get pretty advanced with things such as testing specific API calls or taking snapshots for visual regression, we will not fully utilize those features just yet. For now, we simply use it as a bot for generating and testing content.

The easiest way to put it is that if a user can do something in the browser, we want to use Playwright to verify it works. We do need to be conscious of _what_ we are writing tests for, however. Do not write tests for anything that Drupal core should already have a test for, and do not write a test for anything that UDS should already test for. We only want to test what _Webspark_ is responsible for.

Finally, do not use Playwright to attempt to test for server-side operations. For example, do not test that upon user login a particular Drupal hook executes or that a query is executed via the Drupal database API. If something happens purely in the PHP world, a tool like PHPUnit is the correct tool for that job.

## Installation

### Installing Playwright

Ensure Playwright is ready by running the following command:

```bash
ddev install-playwright
```

Sometimes this command can throw errors (especially after a DDEV update). If that happens, a quick fix is to first remove the add-on, and them immediately re-add it:

```bash
ddev add-on remove Lullabot/ddev-playwright
ddev add-on get Lullabot/ddev-playwright
ddev install-playwright
```

### Installing Playwright from scratch

Follow the steps below **only** if you need to install Playwright from scratch (the `test/playwright` folder is missing):

```bash
ddev add-on get Lullabot/ddev-playwright
ddev restart
mkdir -p test/playwright
# When running the command below, use JavaScript and also install Playwright operating system dependencies
ddev exec -d /var/www/html/test/playwright yarn create playwright
# Review the generated playwright.config.js file and make any necessary changes first
ddev install-playwright
```

<div align="right"><a href="#testing-with-playwright">↑ Top</a></div>
<br>
<br>

## Getting the site ready

> The tests assume that your local site is named `asufactory1` (the default name of the repo) and has a URL of `https://asufactory1.ddev.site`.

### (Optional) Setup a clean install

We do not want to run tests against any existing content in the database. This is because we do not want anything that might interfere with our tests (old database hooks that failed to run, existing content or users, etc.). The tests will seed the website with test content as they run.

You can optionally use a clean install of the site via the command below:

```bash
# Make a backup of the existing site first
ddev snapshot
# Setup a clean install
ddev drush site:install
```

Once this is complete, login to the website using the provided credentials and complete the Initial Configuration.

### Add a sample user

Create a generic admin user for the tests to use:

```bash
ddev drush ucrt playwright --mail='pw@example.com' --password='playwright'
ddev drush urol 'administrator' playwright
```

### Add sample media

> **Webspark** tests will fail without this step!

Visit `/admin/structure/media/manage/video_poster_image_73_100/form-display` and enable the Image field.

Next, visit `/media/add`, and add media for each available media type except Audio and Document (Webspark does not use those by default). Name all of them `sample`, and for images provide the alt text of `sample image` as well as for captions use `sample caption`. It is ok to use the exact same image for all. For remote videos, be sure to choose an appropriate YouTube video, preferably one from the official [Arizona State University](https://www.youtube.com/@arizonastateuniversity) channel. Here is a good one to use: [We build our future](https://www.youtube.com/watch?v=-pEMBc1mZZA) because it is short and has a simple title.

### Setup modules

> **Webspark** tests will fail without this step!

#### Enable the ASU Degrees and RFI module

Enable the "ASU Degrees and RFI" module and set a "Source ID" via:

```bash
ddev drush en asu_degree_rfi -y
ddev drush cset asu_degree_rfi.settings asu_degree_rfi.rfi_source_id "foo" -y
```

When we add RFI forms we only need to ensure they are running in test mode.

#### Disable the Editoria11y module

Uninstall the "Editoria11y" module via:

```bash
ddev drush pmu editoria11y
```

This module adds elements to the DOM that can interfere with Playwright.

### Set up DDEV environment variables

> **Webspark** tests will fail without this step!

You have two options for this next step. Either will work, but choose what fits your local development workflow best. Both options are explained in the [DDEV documentation](https://docs.ddev.com/en/stable/users/extend/customization-extendibility/#environment-variables-for-containers-and-services).

#### Option 1: Local DDEV config

Create a new `.env` file in the root `.ddev` folder (if one does not already exist), and add the following variables:

```env
# .ddev/.env
DRUPAL_USER=""
DRUPAL_PASSWORD=""
MOBILE_URL=""
```

Fill in the values for `DRUPAL_USER` and `DRUPAL_PASSWORD` using the values for the user that you created in the "[Add a sample user](#add-a-sample-user)" step above.

The value for `MOBILE_URL` is optional, but if you are creating tests for Webspark, you should provide a value. There is no reason to test the creation of components on a mobile device, however we do care about the mobile viewing experience. Because of this, our mobile tests specifically override the `baseURL` Playwright configuration value with the value for `MOBILE_URL`. A good default value to use would be the Webspark stable site found at https://websparkreleasestable-asufactory1.acquia.asu.edu. This site already contains all of the components found within a Webspark installation ready for review.

#### Option 2: Global DDEV config

Alternatively, you can add these values in your global DDEV config via:

```bash
# Be sure to add your correct value!
ddev config global --web-environment-add="DRUPAL_USER=<value>"
ddev config global --web-environment-add="DRUPAL_PASSWORD=<value>"
ddev config global --web-environment-add="MOBILE_URL=<value>"
```

After you have added your values, you will need to restart DDEV in order for them to be read:

```bash
ddev restart
```

### (Optional) Create a local snapshot

At this point, you are ready to begin running tests. You may want to create a snapshot of your site at this time to act as a starting point for future test runs:

```bash
ddev snapshot --name playwright-clean-install
```

Alternatively, you can also export the database to a file via:

```bash
ddev export-db --file=/path/to/export/playwright.sql.gz
```

<div align="right"><a href="#testing-with-playwright">↑ Top</a></div>
<br>
<br>

## Writing tests

### Generate tests via Codegen

> See [the docs](https://playwright.dev/docs/codegen#generate-tests-with-the-playwright-inspector) for all Codegen options

```bash
# Open the UI from https://asufactory1.ddev.site:8444
# Username is your local (computer home folder) username
# Password is 'secret'.
ddev playwright codegen

# You can also open to a URL (note the use of http, not https here)
# ddev playwright codegen http://asufactory1.ddev.site/user/login
ddev playwright codegen <url>

# You can also tell it the size of the viewport to use (note the use of http, not https here)
# ddev playwright codegen http://asufactory1.ddev.site --viewport-size=1440,720
ddev playwright codegen <url> --viewport-size=<width>,<height>
```

### Tips for writing tests

- Read the [Playwright documentation](https://playwright.dev) before attempting to write tests. There **is** a learning curve.
- Think critically about what you actually _need_ to test. Do not test things that are from Drupal core or UDS. Only test what your site has added.
- You will spend the most amount of time pinpointing the correct locators to use. The codegen won't always get what you need the first time around.
- Use accessible locators as much as possible. The `getByRole` locator and the accessibility tools in your browser's dev tools are your friend.
- Take advantage of implied visibility. For example, there is no reason to test `toBeVisible` on an element targeted by `getByText('foo')` because the element already needs to be visible in order for Playwright to target it to begin with.
- Be aware of AJAX calls and CSS transitions. Use generous timeouts to give the operation time to complete, or in the case of AJAX, use methods to listen for the requests to complete.
- If you need to use Drush in your test, you will need to build the `page` variable manually.
- Some elements in the Layout Builder are dynamically rendered. In these cases, the `drupal-data-selector` attribute is helpful.
- Writing tests will highlight weaknesses and inconsistencies _very_ fast. Take note of what needs improvemnt, and actually take action on it!

<div align="right"><a href="#testing-with-playwright">↑ Top</a></div>
<br>
<br>

## Running tests

You have three main options for running your tests, detailed below. If you plan to run the entire suite of tests at once, regardless of the method you choose, I have noticed that Playwright sometimes seems to get "tired," and a few tests will fail due to the page context closing. Failures can also happen if Layout Builder code fails to load, or if Drupal takes longer to load than expected. If this occurs, you can re-run those failed tests individually. **You will often find that they will pass without issue.**

If a test fails more than three times in a row, take a look at the provided screenshots either in the report or via the testing UI to investigate the issue, as it likely is something in the test itself that needs to be corrected.

### Run tests

```bash
# Run all tests for all browsers and projects
ddev playwright test

# Run tests for a specific project
# This is the preferred method of running tests
# Ex: ddev playwright test --project chrome
ddev playwright test --project <project>

# Run specific test file(s)
# Note that Playwright searches all test files for any part of the name
# So degree.spec.js will also match card-degree.spec.js
# For this reason, it is best to add the path
# Ex: ddev playwright test pages/homepage.spec.js blocks/charts.spec.js
ddev playwright test <file> <file>

# Run specific test file(s) within specific project(s)
# Ex: ddev playwright test WS_BLOCK_001 --project desktop
ddev playwright test <file> --project <project>

# Run tests by tag
# Ex: ddev playwright test --grep @webspark
ddev playwright test --grep <tag>

# You can also skip a tag
ddev playwright test --grep-invert <tag>

# Run tests by keyword(s)
# Ex: ddev playwright test footer chart
ddev playwright test <keyword> <keyword>

# Run tests with a specific title
# Ex: ddev playwright test -g "search from 404"
ddev playwright test -g "<title>"
```

### Run tests using the UI

```bash
# Open the UI from https://asufactory1.ddev.site:8444
# Username is your local (computer home folder) username
# Password is 'secret'.
ddev playwright test --ui
```

### Watch tests run in the browser

```bash
# Open the UI from https://asufactory1.ddev.site:8444
# Username is your local (computer home folder) username
# Password is 'secret'.
ddev playwright test --headed
```

### View test reports

```bash
# First, run the prompt below
ddev playwright show-report --host=0.0.0.0
# Then open the report from https://asufactory1.ddev.site:9324
```

<div align="right"><a href="#testing-with-playwright">↑ Top</a></div>
<br>
<br>

## Dealing with failures

Tests can fail for a variety of reasons. The most common reason is that an operation that the test depends on (loading a CSS or JS file, an AJAX call, etc) has not completed before the test is attempting to operate on it. In the cases of AJAX calls and CSS transitions, your test can often be fixed by applying a generous timeout to give the operation time to complete before the test runs.

This may not always be the best aproach for an AJAX call however. The call may complete in 200ms, but the test will still wait the full length of the set timeout. In these cases, you may instead want to implement methods that will listen specifically for the AJAX endpoint to return a 200 status code before continuing with the test. An example of this is the `waitForAjax` method found in the `Drupal.js` helper class.

When the test fails as a result of a bug in a component (yay, the test did its job!) it is a good practice to mark the test as failing. There are a few ways to do this, but the most common approach is to tell Playwright to skip the test via the `test.fixme` [annotation](https://playwright.dev/docs/test-annotations#introduction). Playwright will skip the test, but more importantly it signals to the team that the test logic is fine, but the test is failing due to a bug in _what_ it is testing.

When implementing annotations, it is best to leave a commnent with the JIRA ticket to address the issue:

```js
// FIXME: https://asudev.jira.com/browse/UDS-2070
test.fixme('verify', async () => {
  await block.verify();
});
```

<div align="right"><a href="#testing-with-playwright">↑ Top</a></div>
<br>
<br>

# Resources

- [Playwright](https://playwright.dev)
- [Playwright for DDEV](https://github.com/Lullabot/ddev-playwright)
- [Playwright annotations](https://playwright.dev/docs/test-annotations)
- [ASU on YouTube](https://www.youtube.com/@arizonastateuniversity)
- [Webspark stable site](https://websparkreleasestable-asufactory1.acquia.asu.edu)

<div align="right"><a href="#testing-with-playwright">↑ Top</a></div>
<br>
<br>
