# Tech Media Unused View

A small helper module that adds a **Views field** to list, per **Node**, the **Layout Builder** blocks (inline/reusable) that reference **Media** items, and shows each media's **file name**, **file size**, and a **Delete** link.

> No site configuration changes required. Works with **inline** and **reusable** custom blocks.

---

## Features

- Traverses a node’s **Layout Builder** sections and components.
- Supports **inline** block revisions and **reusable** `block_content` entities.
- Detects any block fields of type **entity_reference → media**.
- For each media:
  - Resolves its **source file** (e.g., image/document) to display **file name** and **human-readable size**.
  - Displays a **Delete** link to `entity.media.delete_form` (permission-aware).
- **Options** in the Views field settings:
  - **Include node-level media (not via blocks)** — also scan the node entity for `entity_reference` → `media` fields.
  - **Cache max-age (seconds)** — set a non-zero value to cache the rendered table for performance.
  - **Filter by Media bundle** — include only selected media bundles (leave empty to include all).

---

## Requirements

- Drupal **10** or **11**
- Enabled core/contrib modules:
  - `layout_builder`
  - `block_content`
  - `media`
  - `file`

