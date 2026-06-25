<div align="center">

# My Apps Theme

[![Dev Site My Apps](https://img.shields.io/badge/site-myapps_ws2-blue.svg)](https://dev-myapps-ws2.ws.asu.edu)

A custom sub-theme of the Webspark Renovation theme, made for the MyApps website.

[Features](#features) •
[Requirements](#requirements) •
[Notes](#notes) •
[Resources](#resources)

</div>
<br>
<br>

# Features

This theme utilizes a large amount of custom functionality provided by Drupal for theming. Instead of documenting everything in this README, you will find documentation within each file of this theme as needed. Files, functions, varables, etc. have all been documented to allow future developers an adequate foundational understanding of the workings of this theme.

<div align="right"><a href="#my-apps-theme">↑ Top</a></div>
<br>
<br>

# Requirements

- Drupal 9+
- Webspark 2

<div align="right"><a href="#my-apps-theme">↑ Top</a></div>
<br>
<br>

# Notes

## A note on the Insructions Box template

In `instructions-box.html.twig`, it receives the current domain because the field for the download link is a Drupal Link field type. As this site needs to pass through different environments for testing, the value would need to be updated in the database for each environment for proper testing. A potential long term solution is change those fields to be Text fields, but that would destroy the current data. For now, we will determine if the download link needs to hit the EDNA service (contains `checkAccess`), and if so, we manually build the proper URL. This way, we ensure the proper functionality regardless of environment.

You will find in `includes/block.inc` a `myapps_preprocess_block` hook. This hook is processing data to pass specifically to an "Application Instructions" custom block via its `block_id`. For proper functionality, this block must always remain in the site.

## A note on Application Cards

You will notice the generic use of the word "Download" found on the Application Card templates and teasers. Although this repeated use may be bad for SEO, as well as a bit misleading to users as it does not immediately trigger a download, it is a legacy convention as the `asu_eventtracking` module is built to specifically track the "Download" link text, as seen in `_asu_eventtracking_fields_list()`. As this website now uses Webspark 2, we may be able to utilize the built in Google Analytics and Data Layer to replace some of this functionality in the future. As of July 2023, however, this legacy convention will remain.

<div align="right"><a href="#my-apps-theme">↑ Top</a></div>
<br>
<br>

# Resources

- [Webspark 2](https://brandguide.asu.edu/execution-guidelines/web/building-sites/webspark)

<div align="right"><a href="#my-apps-theme">↑ Top</a></div>
