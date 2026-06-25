# An implementation of the asu_edu subtheme for the California site

This theme was copied over from asucms. The style customizations for the Cali site live exclusively in cali-overrides.css. There is also one custom template, asu_edu/templates/block/block--large-image-banner-with-overlay.html.twig. As long as these two files are preserved, this theme can be updated in the future by copying all of the theme from asucms.

# ASU_EDU Subtheme

Version number 1.0.0 starting with WS2.11.1

## Local Development

- update line 10 in `webpack.mix.js` to use your local ddev domain
- `cd web/themes/custom/asu_edu`
- run `yarn install` to add node_modules and @asu unity-bootstrap-theme to compile w/ variables
- run `yarn watch` to actively compile changes to assets/css/asu_edu.style.css and assets/js/asu_edu.script.js

## Components

ASU_EDU Components used in the site (as of Jan 24, 2024):

Block template | component name
- section_with_cta_buttons | image-parallax
- content_image_overlap | compound
- links_only_cards | cards and template file
- sidebar_navigation | navigation
- for_the_media | cards and template file
- image_and_text | compound
- components_find_my_degree | asu_edu_components module
- animated_quote | quotes
- anim_content_buttons | animated-content-sections
- stories_of_excellence | stories-excellence
- image_carousel_buttons | carousels
- overlay_parallax | image-parallax

Original list of components:
- animated-content-sections
- cards
- carousels
- charts (removed for 1.0.0 in favor of WS2.11.1)
- compound
- futute-students
- headings (removed for 1.0.0 in favor of WS2.11.1)
- heroes (removed for 1.0.0 in favor of WS2.11.1)
- image-parallax
- links
- navigation
- quotes
- stories-excellence
