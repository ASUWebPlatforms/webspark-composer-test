#!/usr/bin/env bash
#
# package.sh
#
# Zip a monorepo subdirectory into a Composer-installable artifact AND emit the
# metadata record used to build packages.json. Name/type/require are read
# verbatim from the package's own composer.json (the source of truth) -- there
# is no separate manifest.
#
# Usage:
#   package.sh <package_path> <version> [out_dir]
#
#   <package_path>  Path to the package dir (must contain composer.json), e.g.
#                   docroot/profiles/contrib/webspark/modules/asu_brand
#   <version>       Composer version string, e.g. 2.15.0 or dev-main
#   [out_dir]       Where to write the zip + metadata (default: ./dist)
#
# Outputs (in out_dir):
#   <short-name>-<version>.zip   The artifact (composer.json at archive root).
#   <short-name>-<version>.json  One metadata record (object).
#
# Also prints the metadata record to stdout.
#
set -euo pipefail

PKG_PATH="${1:?package path required}"
VERSION="${2:?version required}"
OUT_DIR="${3:-dist}"

command -v jq  >/dev/null 2>&1 || { echo "::error::jq is required" >&2; exit 1; }
command -v zip >/dev/null 2>&1 || { echo "::error::zip is required" >&2; exit 1; }

# --- Validation -------------------------------------------------------------
if [ ! -d "$PKG_PATH" ]; then
  echo "::error::package path does not exist: $PKG_PATH" >&2
  exit 1
fi
if [ ! -f "$PKG_PATH/composer.json" ]; then
  echo "::error::no composer.json found in: $PKG_PATH" >&2
  exit 1
fi
jq empty "$PKG_PATH/composer.json" 2>/dev/null || {
  echo "::error::composer.json is not valid JSON: $PKG_PATH/composer.json" >&2
  exit 1
}

# --- Derive metadata from the package's own composer.json -------------------
FULL_NAME="$(jq -r '.name // empty' "$PKG_PATH/composer.json")"
PKG_TYPE="$(jq -r '.type // "library"' "$PKG_PATH/composer.json")"
REQUIRE_JSON="$(jq -c '.require // {}' "$PKG_PATH/composer.json")"

if [ -z "$FULL_NAME" ]; then
  echo "::error::composer.json has no \"name\" field: $PKG_PATH/composer.json" >&2
  exit 1
fi

# Short name (after the vendor/) drives the artifact filename.
SHORT_NAME="${FULL_NAME##*/}"
REFERENCE="$(git -C "$PKG_PATH" rev-parse HEAD 2>/dev/null || echo "")"

mkdir -p "$OUT_DIR"
OUT_DIR_ABS="$(cd "$OUT_DIR" && pwd)"
ZIP_NAME="${SHORT_NAME}-${VERSION}.zip"
ZIP_PATH="${OUT_DIR_ABS}/${ZIP_NAME}"
META_PATH="${OUT_DIR_ABS}/${SHORT_NAME}-${VERSION}.json"

# --- Build the zip with composer.json at the archive root -------------------
# Zip the *contents* of the dir (no leading parent path). Exclude build cruft.
rm -f "$ZIP_PATH"
(
  cd "$PKG_PATH"
  zip -r -q "$ZIP_PATH" . \
    -x '*.git*' \
    -x '*/node_modules/*' -x 'node_modules/*' \
    -x '*/.DS_Store' -x '.DS_Store'
)

# sha1 is what Composer expects for dist.shasum.
if command -v sha1sum >/dev/null 2>&1; then
  SHASUM="$(sha1sum "$ZIP_PATH" | awk '{print $1}')"
else
  SHASUM="$(shasum -a 1 "$ZIP_PATH" | awk '{print $1}')"
fi

# --- Emit the metadata record ----------------------------------------------
# "tag" is the release tag the asset will live under. For tagged releases this
# equals the version; for branch builds it is the fixed branch tag (e.g.
# dev-main) whose asset is clobbered each push. Caller may override via TAG.
TAG="${TAG:-$VERSION}"

jq -n \
  --arg name      "$FULL_NAME" \
  --arg version   "$VERSION" \
  --arg type      "$PKG_TYPE" \
  --argjson req   "$REQUIRE_JSON" \
  --arg filename  "$ZIP_NAME" \
  --arg tag       "$TAG" \
  --arg reference "$REFERENCE" \
  --arg shasum    "$SHASUM" \
  '{name:$name, version:$version, type:$type, require:$req,
    filename:$filename, tag:$tag, reference:$reference, shasum:$shasum}' \
  | tee "$META_PATH"

echo "::notice::packaged $FULL_NAME@$VERSION -> $ZIP_PATH (sha1 $SHASUM)" >&2
