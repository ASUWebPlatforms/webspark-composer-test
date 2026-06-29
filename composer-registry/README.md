# Composer Package Registry

This directory holds the machinery that turns this repo into a static
[Composer repository](https://getcomposer.org/doc/05-repositories.md#composer)
for selected Webspark packages — a simple,
[Satis](https://composer.github.io/satis) alternative custom-built for ASU
Webspark. Package subdirectories are packaged as zip artifacts, stored as GitHub
Release assets on this repo, and advertised via a `packages.json` served from
GitHub Pages.

For **consumer** instructions (how to require these packages in a project), see
the "Composer Package Registry" section of the repo-root
[`README.md`](../README.md). This document covers how the pipeline works and how
to maintain it.

## Layout

```
composer-registry/
├── package.sh              zips a package subdirectory + emits its metadata record
├── merge-packages-json.sh  merges new version records into packages.json
├── index.html              human-readable browse page (served from Pages)
└── README.md               this file

packages.json               the accumulator of record (repo ROOT — see below)
.github/workflows/composer-packages-publish.yml   the pipeline
```

`packages.json` deliberately lives at the **repo root**, not in this directory.
It is the committed source of record that the workflow guards, merges, commits,
and serves; keeping it at root keeps that contract simple. The Pages step copies
both `packages.json` and `index.html` side-by-side into the deployed site, so
the served layout is flat (`/packages.json` + `/index.html`) regardless of where
`index.html` lives in the repo — which is why `index.html`'s relative
`fetch("packages.json")` works.

## How it works

This all runs in **one repo** — there is no separate registry repo, and no
cross-repo token. The single workflow
`.github/workflows/composer-packages-publish.yml` does everything using the
built-in `GITHUB_TOKEN`:

1. **`ensure-release`** — on a tag push (or `dev-main` for a branch build),
   creates the GitHub Release that will hold this version's assets.
2. **`publish`** (matrix) — zips each selected package subdirectory with
   `composer-registry/package.sh` and uploads it as a Release asset, then
   verifies the asset downloads and its checksum matches.
3. **`rebuild`** — merges the new version records into the committed
   `packages.json` accumulator with `composer-registry/merge-packages-json.sh`,
   commits it back to `main`, and deploys `packages.json` + `index.html` to
   GitHub Pages.

### `packages.json` is an append-only accumulator

`packages.json` is committed to the repo and only ever has version entries
added or replaced. Removing a package from the publish matrix stops new versions
being added; it does **not** remove previously published entries. Consumers
pinned to an existing version keep working as long as that entry and its release
asset survive.

### Packaging honors `.gitignore` exactly

`package.sh` archives exactly the files **git tracks** (via `git ls-files`), so
`.gitignore` is honored precisely — including build artifacts force-added under
`node_modules`, which must ship with the package. A blanket filesystem exclude
could not tell an ignored file apart from one deliberately force-added past
`.gitignore`.

### No `[skip ci]` on the bot commit — on purpose

The `rebuild` job commits `packages.json` back to `main` with a plain message
(no `[skip ci]`). Pushes made with the built-in `GITHUB_TOKEN` do **not** trigger
new workflow runs (GitHub's built-in recursion guard), so the loop is already
prevented. Adding `[skip ci]` would additionally suppress any **release tag** a
human cuts off that commit — and releases cut off `main` land on exactly the
bot's commit — which would stop the publish from running.

## Adding or removing a package

The list of packaged subdirectories is the `matrix.package` array in the
`publish` job of the workflow. Add a path to publish it; remove a path to stop
publishing new versions of it (existing versions remain — see above).

## Promoting to production

The workflow's `REPO`, `BASE_URL`, and `PAGES_URL` env values are the single
flip-point: change them to the production repo (`webspark-mirror`) and its Pages
URL. Production will also be public, so the built-in `GITHUB_TOKEN` model carries
over unchanged — no PAT or GitHub App required.

## Local testing

You can exercise the scripts without running the workflow:

```bash
# Package a subdirectory into ./dist (zip + metadata record)
./composer-registry/package.sh docroot/profiles/contrib/webspark/modules/asu_brand dev-main dist

# Merge that record into a copy of packages.json
jq -s '.' dist/*.json > records.json
./composer-registry/merge-packages-json.sh packages.json records.json \
  https://github.com/ASUWebPlatforms/webspark-composer-test/releases/download
```
