## INTRODUCTION

The ET Json Image Import module is a custom module used to create media entities from a list of images scraped from non-Drupal websites that are migrating into Stack 1. It was created based upon the Simple JSON Migration example provided by the Migrate Plus module, but adds the ability to upload the json file via the UI so the same migration script can be run on many sites, and so files get imported into the correct files directory for each site on site factory.

The primary use case for this module is to import images into new Drupal sites as a part of manually rebuilding sites that are in Wordpress. This saves time manually entering media,, i.e. uploading media, entering alt text, for sites that are too small to warrant a full programmatic migration.

This module is intended for use by Admin users, i.e. folks internal to ET web team.

## REQUIREMENTS

migrate tools, migrate plus

## INSTALLATION

Install as you would normally install a contributed Drupal module.
See: https://www.drupal.org/node/895232 for further information.

## CONFIGURATION

Visit '/admin/config/system/settings/et_json_image_import' and upload a json file containing the urls and alt text for the images. This json file is created separately by a crawler and a python script.

## USAGE

Use Drush to run the migration scripts. Replace your sitename and environment below.

`ddev drush @yoursite.env migrate:import json_image`
`ddev drush @yoursite.env migrate:import json_media`

Once the media items have been created, this module can be uninstalled from your site.

Once the Wordpress migration is complete, this custom module can be removed from the Stack 1 codebase.
