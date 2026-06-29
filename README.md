<div align="center">
<h1 id="webspark">Stack 1: Webspark</h1>

Sites running the Webspark build.

[Local Development](#local-development) •
[Code Quality Tools](#code-quality-tools) •
[Composer Package Registry](#composer-package-registry) •
[Resources](#resources)

</div>
<br>
<br>

# Local Development

See the following documentation on getting your local DDEV environment setup for Acquia:

https://docs.google.com/document/d/1R-wFpJnxUmQJ35bbFEhtoXF4YZXkHe6ZVVy8oBUn9kQ/edit?usp=sharing

To ensure that the code quality tools outlined below are installed, run the following:

```bash
ddev composer install
ddev yarn
```

<div align="right"><a href="#webspark">↑ Top</a></div>
<br>
<br>

## Code Quality Tools

### PHP

**Static Analysis:**

- **PHPStan** - Static analysis tool
- **PHPStan Drupal Extension** - Drupal-specific rules for PHPStan

**Code Style & Standards:**

- **PHP_CodeSniffer** - Coding standards enforcement
- **Drupal Coder** - Drupal-specific coding standards for PHP_CodeSniffer
- **PHPCBF** - Auto-fix coding standards

### Frontend Tools

**JavaScript/TypeScript:**

- **ESLint** - JS linting and fixing

**CSS/SCSS:**

- **Stylelint** - CSS/SCSS linting and fixing

## Configuration Files

- **EditorConfig**: `.editorconfig` (copied from Drupal core)
- **PHPStan**: `phpstan.neon`
- **ESLint**: `.eslintrc.json` (extends Drupal core)
- **Stylelint**: `.stylelintrc.json` (extends Drupal core)

## Available Commands

### Composer Commands

> The following are configured to only scan core Webspark files.

```bash
ddev composer run phpstan        # Static analysis
ddev composer run phpcs          # Check PHP coding standards
ddev composer run phpcbf         # Fix PHP coding standards
```

### NPM Commands

> The following are configured to only scan core Webspark files.

```bash
ddev yarn run eslint              # Lint JavaScript
ddev yarn run eslint:fix          # Fix JavaScript
ddev yarn run stylelint           # Lint CSS
ddev yarn run stylelint:fix       # Fix CSS
```

### DDEV Commands

> The following command will run all code quality tools together for Webspark files.

```bash
ddev ws:lint                # Lint core Webspark against all tools
```

> Individual sites should use the following commands for linting their code.

```bash
ddev phpstan                # PHP static analysis
ddev phpcs                  # PHP coding standards
ddev phpcbf                 # Fix PHP coding standards
ddev eslint                 # JavaScript linting
ddev stylelint              # CSS linting
```

Use the `--help` flag to see available options for each command. For example:

`ddev phpstan --help`

<div align="right"><a href="#webspark">↑ Top</a></div>
<br>
<br>

## Composer Package Registry

This repo doubles as a static [Composer repository](https://getcomposer.org/doc/05-repositories.md#composer)
for some of the Webspark packages it contains. Selected subdirectories are
packaged as zip artifacts, stored as GitHub Release assets on this repo, and
advertised via a `packages.json` served from GitHub Pages — a simple,
[Satis](https://composer.github.io/satis) alternative custom-built for ASU
Webspark.

Browse the published packages at
[https://asuwebplatforms.github.io/webspark-composer-test](https://asuwebplatforms.github.io/webspark-composer-test).

### Consumer usage (test)

Add the repository to your project's `composer.json`:

```json
{
    "repositories": [
        { "type": "composer", "url": "https://asuwebplatforms.github.io/webspark-composer-test/" }
    ]
}
```

Then require packages as normal:

```bash
composer require asuwebplatforms/asu_brand
```

Notes for consumers:

- The repository must remain **public** for unauthenticated `composer install`.
- Branch builds are published as the `dev-main` version
  (`composer require asuwebplatforms/asu_brand:dev-main`).
- Release builds are published under the tag's version
  (`composer require asuwebplatforms/asu_brand:0.0.5`).

### Maintaining the registry

The publishing pipeline, packaging scripts, and browse page live in the
[`composer-registry/`](composer-registry/) directory. See
[`composer-registry/README.md`](composer-registry/README.md) for how it works and the file layout.

## Resources

- [DDEV setup for Acquia](https://docs.google.com/document/d/1R-wFpJnxUmQJ35bbFEhtoXF4YZXkHe6ZVVy8oBUn9kQ/edit?usp=sharing)
- [PHPStan](https://phpstan.org)
- [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer)
- [Drupal Coder](https://www.drupal.org/project/coder)
- [ESLint](https://eslint.org)
- [Stylelint](https://stylelint.io)
- [DDEV](https://ddev.com)
- [Drupal](https://www.drupal.org)

<div align="right"><a href="#webspark">↑ Top</a></div>
<br>
<br>
