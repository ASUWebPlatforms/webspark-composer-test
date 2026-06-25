<div align="center">

# Analytics Groups

A module for managing Groups on the ASU Analytics website.

[Installation](#installation) •
[Features](#features) •
[Module Development](#module-development) •
[Resources](#resources) •
[License](#license)

</div>
<br>
<br>

# Installation

The Analytics website uses custom modules from GitLab. In order to successfully install them, you will need a [GitLab Personal Access Token](https://docs.gitlab.com/ee/user/profile/personal_access_tokens.html). If you attempt to install dependencies with Composer without first configuring the proper credentials, you will be prompted with a message that "Your credentials are required to fetch private repository metadata".

In the root of your local Drupal installation of the Analytics website, create an `auth.json` file with your GitLab Personal Access Token:

> This file is ignored by Git

```bash
touch auth.json
```

Place the following code into `auth.json`, ensuring to use your own GitLab Personal Access token:

```json
{
  "gitlab-token": {
    "gitlab.com": "YOUR-PERSONAL-ACCESS-TOKEN"
  }
}
```

You can now install the Composer dependecies:

```bash
composer install
```

By default, Composer will install the `main` branch. If you need to target a different branch, update the Drupal projects `composer.json` file to target the branch you need.

For example, if you need to target the `anprt-2057` branch, change the target branch from `dev-main` to `anprt-2057` in the require section:

```json
// composer.json
"require": {
  "analytics/analytics_groups": "dev-anprt-2057"
}
```

Note that this approach assumes that it may be your first time installing the dependencies. If you already have a `composer.lock` file pointing to a different branch, an alternative approach is to reinstall or update the module via Composer, using the correct branch as the target version.

Reinstalling the module using the branch as the target version:

```bash
composer require analytics/analytics_groups:dev-anprt-2057
```

You can also be more granular, and install a specific commit:

```bash
composer require analytics/analytics_groups:dev-anprt-2064#ce1bd1ac
```

<div align="right"><a href="#analytics-groups">↑ Top</a></div>
<br>
<br>

# Features

Coming soon.

<div align="right"><a href="#analytics-groups">↑ Top</a></div>
<br>
<br>

# Module Development

## Cloning the module locally

> Ensure that you have the proper access rights and permissions to clone this repo first!

```bash
git clone git@gitlab.com:asu-analytics/analytics/analytics_groups.git
```

## Working with branches

When creating a branch for this module, **always name your branch after the task in JIRA whenever possible**. This ensures that developers can easily associate your work with an appropraite task, and also ensures that your work is properly being accounted for.

For example, if you are about to begin work on task ANPRT-2057 in JIRA, your branch would have the name `anprt-2057`.

## Working with Multidevs and Pull Requests

When you are testing your code for functionality, you should create a Pantheon Multidev. Name your Multidev the same as your current branch to keep your workflow organized.

When you are ready to merge your code into the `main` branch, open a Pull Request (GitLab calls them Merge Requests). Put a link to the Multidev in the description of the PR, so that other developers that review your work have an easily accessible (and already functioning) environment to test with.

<div align="right"><a href="#analytics-groups">↑ Top</a></div>
<br>
<br>

# Resources

- [Drupal Module Development](https://www.drupal.org/docs/develop/creating-modules)
- [Pantheon Multidevs](https://docs.pantheon.io/guides/multidev)
- [GitLab Merge Requests](https://docs.gitlab.com/ee/user/project/merge_requests/creating_merge_requests.html)

<div align="right"><a href="#analytics-groups">↑ Top</a></div>
<br>
<br>

# License

Coming soon.

<div align="right"><a href="#analytics-groups">↑ Top</a></div>
<br>
<br>
