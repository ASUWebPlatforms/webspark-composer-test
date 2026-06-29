#!/usr/bin/env bash
#
# merge-packages-json.sh
#
# Merge one or more package metadata records into the committed packages.json
# accumulator. Existing versions are preserved; only the named name->version
# entries in the metadata are added or replaced.
#
# This is the only logic that builds packages.json. There is no manifest:
# every record is derived from a package's own composer.json at build time
# (see webspark-composer-test scripts/package.sh).
#
# Usage:
#   merge-packages-json.sh <packages.json> <metadata.json> <base_url>
#
#   <packages.json>   Path to the committed accumulator (created if missing).
#   <metadata.json>   JSON array of records produced by package.sh, e.g.
#                     [ { "name": "...", "version": "...", "type": "...",
#                         "require": {...}, "filename": "...", "tag": "...",
#                         "reference": "...", "shasum": "..." }, ... ]
#   <base_url>        Release download base, e.g.
#                     https://github.com/ASUWebPlatforms/webspark-composer-test/releases/download
#
set -euo pipefail

PACKAGES_JSON="${1:?path to packages.json required}"
METADATA="${2:?path to metadata.json required}"
BASE_URL="${3:?release download base url required}"

command -v jq >/dev/null 2>&1 || { echo "::error::jq is required" >&2; exit 1; }
[ -f "$METADATA" ] || { echo "::error::metadata file not found: $METADATA" >&2; exit 1; }

# Ensure the accumulator exists and has the expected shape.
if [ ! -f "$PACKAGES_JSON" ]; then
  echo '{"packages":{}}' > "$PACKAGES_JSON"
fi

# Validate inputs are JSON before we touch anything.
jq empty "$PACKAGES_JSON"
jq empty "$METADATA"

tmp="$(mktemp)"
trap 'rm -f "$tmp"' EXIT

jq \
  --arg base "$BASE_URL" \
  --slurpfile recs "$METADATA" \
  '
  # Start from the existing accumulator, fold each record in.
  reduce $recs[0][] as $r (.;
    .packages[$r.name][$r.version] = {
      name:    $r.name,
      version: $r.version,
      type:    $r.type,
      require: ($r.require // {}),
      dist: {
        type:      "zip",
        url:       ($base + "/" + $r.tag + "/" + $r.filename),
        reference: $r.reference,
        shasum:    $r.shasum
      }
    }
  )
  ' "$PACKAGES_JSON" > "$tmp"

# Final shape check, then atomically replace.
jq empty "$tmp"
mv "$tmp" "$PACKAGES_JSON"
trap - EXIT

echo "Merged $(jq '.[] | .name' -r "$METADATA" | wc -l | tr -d ' ') record(s) into $PACKAGES_JSON"
