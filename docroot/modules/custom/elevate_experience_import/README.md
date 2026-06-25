# Elevate Experience Import

Imports the client's two monthly CSV files into the **Experience** content type
through the [Feeds](https://www.drupal.org/project/feeds) module.

## What this module provisions

On install (`hook_install`) it creates, idempotently:

* **Vocabularies** – `subitems`, `college` (terms auto-created on import).
* **experience_type terms** – the five reclassified types
  (Jobs, Work & Career Development · Internships and Field Experience · Research ·
  Community, Service and Leadership · Global Experiences), alongside the existing
  ones.
* **Fields on `node.experience`** – `field_credits` (plain text – values such as
  "1-12" / "Varies" are not numeric), `field_contact_name`, `field_contact_email`,
  `field_subitems`, `field_college`; and widens `field_location` to unlimited.
* **Two Feeds importers** – `experience_general` (CSV A) and
  `experience_certificate` (CSV B), each with the appropriate mappings and a
  Feeds Tamper *explode* plugin on every multi-value source.

## Data cleaning

The CSVs are messy (section-header rows, "CSV-within-CSV" multi-value cells with
mixed quoting, typos, a `Learn more - ` link prefix, schemeless URLs). The
parser cannot handle this reliably, so `ExperienceParseSubscriber` runs right
after parsing (before Feeds Tamper) and:

* drops section/header/title rows;
* rewrites the classification columns to clean, pipe-delimited **canonical**
  taxonomy names (matched against the lists in `ExperienceValueNormalizer`),
  which Tamper then explodes on `|`;
* strips the `Learn more - ` prefix and adds a missing `https://` scheme.

`experience_type`, `collection` and `location` are matched to existing terms (no
typo duplicates); `college` and `subitems` values are auto-created.

## Monthly workflow for editors

The client provides a new CSV each month. To import:

1. Save the file as **CSV UTF-8** (Excel → *Save As* → *CSV UTF-8*). The parser
   expects UTF-8 and a `;` delimiter, exactly as the client's exports use.
2. Go to **Content → Feeds** (`/admin/content/feed`).
3. Open the standing feed **"Experiences – General"** or
   **"Experiences – Certificate Courses"** (or add a new feed of that type).
4. Upload the CSV and click **Import**.

Imports **upsert by title**: existing Experiences are updated, new ones created,
and Experiences absent from the file are kept. Running the same file twice does
not create duplicates.

> The importing user must be able to use the `basic_html` text format and to
> reference the Experience taxonomies (any editor/admin role).

## Experience Finder (front-end)

This module also ships the public **Experience Finder** listing.

* **View:** `experience_finder` (config in `config/install`), page at
  **`/find-experiences`** — a card grid (image, title, `[location]`, trimmed
  description, *Get Started* CTA, favourites placeholder), full numeric pager and
  AJAX-refreshed results.
* **Exposed filters** (Better Exposed Filters checkbox dropdowns + a keyword
  search and location select):
  * *Experience type* → the five reclassified `experience_type` terms.
  * *Career interests* → the `collection` terms (the long STEM term is shown as
    "STEM").
  * *Program specific options* → a custom Views filter
    (`ProgramSpecificOptions`) offering *Work+Life Design Certificate courses*
    (field_program), *All experiences for credit* and *Non-credit experiences*
    (field_for_credit).
* **Data model:** `field_program` (Program taxonomy) and `field_for_credit`
  power the program filter; they are derived during import by
  `ExperiencePresaveSubscriber` (certificate feed ⇒ certificate program + for
  credit; general feed ⇒ for credit when the credits value is meaningful) and
  were backfilled onto existing nodes by `hook_update_9001`.
* **Theme:** templates live in `templates/` (registered via `hook_theme`, so the
  contrib renovation theme is untouched); styling/behaviour in
  `css/experience-finder.css` + `js/experience-finder.js` (the
  `elevate_experience_import/experience_finder` library), attached from the view
  template.
* **Exposed-form tweaks** (option set/order, STEM relabel, "Get Started" CTA
  text) live in `elevate_experience_import.module`.

## Favourites

Each card has a heart that uses the **Flag** module's `favorite` flag
(per-user, node:experience):

* **Anonymous users** get a small tooltip prompting them to log in (linking to
  `/user/login` with a destination back to the finder).
* **Authenticated users** toggle the favourite via AJAX. On load, the page asks
  `/find-experiences/favorites/list` for the user's flagged nids and fills in the
  matching hearts; clicking a heart POSTs to
  `/find-experiences/favorite/{node}/toggle` (CSRF-protected, login required).

Storage/permissions are provisioned by `hook_update_9002` /
`_elevate_experience_import_provision_favorite_flag()`; the endpoints live in
`Controller\FavoriteController`; the behaviour is in `js/experience-finder.js`.
Because Flag provides Views integration, a "My Favorites" listing can later be
built on the same flag.
