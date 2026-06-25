# Copilot Instructions for ASU Factory 1 (Webspark)

This is an **Acquia Cloud Site Factory** running the **Webspark** Drupal distribution for Arizona State University. It manages 200+ multi-site instances with custom modules, themes, and centralized configuration.

## Project Context

- **Platform**: Drupal 10+ on Acquia Cloud Site Factory
- **Distribution**: Webspark (ASU's custom Drupal profile)
- **Architecture**: Multi-site factory with shared codebase
- **Local Environment**: DDEV with PHP 8.3 + MySQL 5.7
- **Build Tools**: Composer 2, Yarn

## Development Workflow

### Local Development

```bash
# Start DDEV
ddev start

# Install dependencies
ddev composer install
ddev yarn

# List all sites in the factory
ddev asusf:list-sites
```

### Build & Lint Commands

**PHP Quality Tools:**

```bash
# Run all Webspark linting
ddev ws:lint

# Individual PHP tools
ddev phpstan [path]           # Static analysis
ddev phpcs [path]             # Check coding standards
ddev phpcbf [path]            # Auto-fix coding standards
```

**Frontend Quality Tools:**

```bash
# JavaScript
ddev yarn run eslint          # Lint JS
ddev yarn run eslint:fix      # Fix JS

# CSS/SCSS
ddev yarn run stylelint       # Lint styles
ddev yarn run stylelint:fix   # Fix styles
```

**Playwright Tests:**

```bash
ddev playwright test          # Run E2E tests
ddev playwright test --ui     # Run with UI
ddev playwright show-report   # View latest report
```

> **Scope Note**: By default, `ws:lint` and package.json scripts target only core Webspark files (`docroot/profiles/contrib/webspark`). Custom code should use the individual `ddev phpstan`, `ddev phpcs`, etc., commands with explicit paths.

## Architecture & Key Conventions

### Multi-Site Factory Structure

- **Core Webspark**: `docroot/profiles/contrib/webspark/` - The upstream distribution (DO NOT modify without the users permission first)
- **Custom Modules**: `docroot/modules/custom/` - Site-specific or shared custom modules
- **Custom Themes**: `docroot/themes/custom/` - Site-specific themes
- **Configuration**: `config/sync/` - Shared configuration, `config/[sitename]/` - Site-specific config
- **Factory Hooks**: `factory-hooks/` - Acquia Cloud Site Factory deployment hooks

### Dependency Management

**DO NOT** add dependencies directly to root `composer.json`. Use the custom dependency system:

```bash
# Add custom dependencies
composer custom-require vendor/package
composer update

# Remove custom dependencies
composer custom-remove vendor/package
composer update
```

This keeps custom dependencies in `custom-dependencies/composer.json`, separate from Webspark core dependencies in `webspark-dependencies/`, preventing merge conflicts during upstream updates.

**Webspark Patches**: Add to `webspark-dependencies/patches.custom.json`, not root `composer.patches.json`.
**Custom Patches**: Add to `custom-dependencies/patches.custom.json`, not root `composer.patches.json`.

### Drupal Patterns

Follow patterns defined in `.github/instructions/drupal.instructions.md`:

- Use Drupal APIs (Entity, Form, Render) over direct DB queries
- Dependency injection in services/controllers/forms
- Vanilla JavaScript + `Drupal.behaviors` (avoid jQuery)
- Proper caching with cache tags/contexts
- Security: sanitize output, validate input, check permissions

### Testing

- **Playwright**: E2E tests in `test/playwright/` (see `.github/instructions/playwright.instructions.md`)
- **PHPUnit**: Kernel/Functional tests for custom modules
- Follow WCAG 2.1 AA accessibility standards (see `.github/agents/a11y.agent.md`)

## Common Tasks

### Working with Sites

```bash
# Pull site database and files
ddev asusf:pull [sitename]

# Initialize new site
ddev asusf:init

# Find site groups
ddev asusf:find-site-groups
```

### Managing Configuration

```bash
# Export config
drush config:export

# Import config
drush config:import

# Clear caches
drush cr
```

### Releases

```bash
# Prepare release merge request
ddev asusf:prep-release-mr

# Set version
ddev asusf:set-version [version]

# Tag release
ddev asusf:tag-release [tag]
```

## File Organization

```
asufactory1/
├── .ddev/                      # DDEV configuration
│   └── commands/web/           # Custom DDEV commands (asusf:*, ws:*, phpstan, phpcs, etc.)
├── config/                     # Drupal configuration
│   ├── sync/                   # Shared config
│   └── [sitename]/             # Site-specific config
├── custom-dependencies/        # Custom composer packages & patches
│   ├── composer.json           # Add custom dependencies here
│   └── patches.custom.json     # Add custom patches here
├── docroot/
│   ├── modules/custom/         # Custom modules (100+ ASU-specific modules)
│   ├── themes/custom/          # Custom themes (50+ site themes)
│   └── profiles/contrib/webspark/  # Webspark core (DO NOT modify without the users permission first)
├── factory-hooks/              # Acquia Cloud Site Factory hooks
├── hooks/                      # Acquia Cloud hooks
├── test/playwright/            # End-to-end tests
├── webspark-dependencies/      # Webspark core dependencies (DO NOT modify without the users permission first)
└── .github/
    ├── instructions/           # Context-specific instructions (Drupal, Playwright)
    └── agents/                 # Custom AI agents (Drupal, A11y)
```

## Important Constraints

1. **Never modify Webspark core** (`docroot/profiles/contrib/webspark/`) - Use hooks, custom modules, or theme overrides
2. **Use custom-dependencies system** - Prevents merge conflicts with upstream Webspark updates
3. **Multi-site awareness** - Changes may affect 200+ sites; test thoroughly
4. **DDEV environment variables** - `ACQUIA_ENVIRONMENT_ID=asufactory1.01live` is set for local parity
5. **Configuration management** - Export all config changes; use `config/install` or `config/optional` in modules
6. **Code standards enforcement** - All PHP must pass Drupal/DrupalPractice standards before commit

## Specialized Agents

This project has custom agents for specialized tasks:

- **Drupal Expert** (`.github/agents/drupal.agent.md`) - Deep Drupal 10+ expertise
- **A11y Expert** (`.github/agents/a11y.agent.md`) - WCAG 2.1 AA compliance

Use `@drupal` or `@a11y` to invoke these agents for domain-specific guidance.

## MCP Servers

This project is configured with the following MCP servers (requires VS Code reload after configuration):

- **Playwright MCP** - Enhanced Playwright test development and debugging
  - Run tests and get results directly in chat
  - Query test configurations and project settings
  - Debug test failures with enhanced context
  - Location: `test/playwright/` with config at `test/playwright/playwright.config.js`
