<?php
/**
* @file
* Theme and preprocess functions for forms
*/

/**
* Implements hook_form_FORM_ID_alter()
*/

function lib_subwebspark_form_views_exposed_form_alter(&$form, &$form_state, $form_id): void 
{
    /*This adds classes including bootstrap container to exposed filter*/
    $form['#attributes']['class'][] = 'site-search';
    $form['#attributes']['class'][] = 'container';
}

/**
* Implements template_preprocess_form_element()
*/

function lib_subwebspark_preprocess_form_element(&$variables): void
{
    /*Adds column classes to both exposed filter forms on home page and search results page. They have different element ids because the exposed filter is in a content block.*/
        if (isset($variables['element']['#id'])) {
    $element_id = $variables['element']['#id'];
    if ($element_id == 'edit-terms--2' || 'edit-terms') {
      $variables['attributes']['class'][] = 'col-md-5';
    }
}
}

/**
* @param $variables
* @return void
* Implements template_preprocess_container()
*/
/*This function is throwing an error. Commenting it out- the above function DOES NOT throw an error even though they are almost identical. Functionality on  both home and search pages is unaffected except that the col isn't added and the search button wraps to a new line on desktop.*/
function lib_subwebspark_preprocess_container(&$variables): void
{
    /*if isset is required to mitigate unedfined array 'id' error on backend even though the code works on the front end without it*/
    if (isset($variables['element']['#id'])) {
    $element = $variables['element']['#id'];
    if ($element == 'edit-actions--2' || 'edit-actions') {
        $variables['attributes']['class'][] = 'col';
    }
  }
}