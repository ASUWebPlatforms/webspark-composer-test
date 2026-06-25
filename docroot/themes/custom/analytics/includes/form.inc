<?php

/**
 * @file
 * Theme and preprocess functions for forms.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter() for the exposed form.
 */
function analytics_form_views_exposed_form_alter(&$form, FormStateInterface $form_state, $form_id): void
{
  if ($form_id == 'views_exposed_form') {
    if ($form['#id'] == 'views-exposed-form-global-search-page-1') {
      $form['search']['#title_display'] = 'invisible';
      $form['search']['#prefix'] = '<div class="tw-hidden">';
      $form['search']['#suffix'] = '</div>';
      $form['keywords']['#title_display'] = 'invisible';
      $form['group']['#title_display'] = 'invisible';
    }

    if ($form['#id'] == 'views-exposed-form-data-dictionary-view-page-1') {
      $form['search']['#title_display'] = 'invisible';
      $form['search']['#prefix'] = '<div class="tw-hidden">';
      $form['search']['#suffix'] = '</div>';
      $form['field_schema']['#title_display'] = 'invisible';
      $form['field_table']['#title_display'] = 'invisible';
    }

    if ($form['#id'] == 'views-exposed-form-glossary-term-search-page-1') {
      if (isset($form['actions'])) {
        $form['search']['#title_display'] = 'invisible';
        $form['actions']['#attributes']['class'][] = 'col-auto tw-mt-0';
      }
    }

    if ($form['#id'] == 'views-exposed-form-global-search-global-search-block') {
      $form['search']['#title_display'] = 'invisible';
    }

    if (
      $form['#id'] == 'views-exposed-form-group-nodes-page-1' ||
      $form['#id'] == 'views-exposed-form-taxonomy-term-page-1'
    ) {
      $form['title']['#title_display'] = 'invisible';
      $form['keywords']['#title_display'] = 'invisible';
    }
  }
}

/**
 * Implements hook_theme_suggestions_form_alter().
 *
 * @param array $suggestions
 * @param array $variables
 *
 * @return void
 */
function analytics_theme_suggestions_form_alter(array &$suggestions, array $variables): void
{
  if (isset($variables['element']['#id'])) {
    $suggestions[] = 'form__' . $variables['element']['#id'];
  }

  if (
    $variables['element']['#id'] === 'views-exposed-form-group-pages-page-1' ||
    $variables['element']['#id'] === 'views-exposed-form-group-pages-page-2' ||
    $variables['element']['#id'] === 'views-exposed-form-group-pages-page-3'
  ) {
    $suggestions[] = 'form__views_exposed_form__group_pages';
  }

  if ($variables['element']['#id'] === 'views-exposed-form-group-nodes-page-1') {
    $suggestions[] = 'form__views_exposed_form__group_nodes';
  }

  if ($variables['element']['#id'] === 'views-exposed-form-global-search-page-1') {
    $suggestions[] = 'form__views_exposed_form__global_search';
  }

  if ($variables['element']['#id'] === 'views-exposed-form-taxonomy-term-page-1') {
    $suggestions[] = 'form__views_exposed_form__taxonomy_term';
  }

  if ($variables['element']['#id'] === 'views-exposed-form-data-dictionary-view-page-1') {
    $suggestions[] = 'form__views_exposed_form__data_dictionary_view';
  }

  if ($variables['element']['#id'] === 'views-exposed-form-glossary-term-search-page-1') {
    $suggestions[] = 'form__views_exposed_form__glossary_term_search';
  }
}

/**
 * Implements hook_theme_suggestions_form_element_alter().
 *
 * @param array $suggestions
 * @param array $variables
 *
 * @return void
 */
function analytics_theme_suggestions_form_element_alter(array &$suggestions, array $variables): void
{
  if (isset($variables['element']['#context']['#view_id'])) {
    $suggestions[] = 'form_element__' . $variables['element']['#context']['#view_id'];
    $suggestions[] = 'form_element__' . $variables['element']['#type'] . '__' . $variables['element']['#context']['#view_id'];
    $suggestions[] = 'form_element__' . $variables['element']['#type'] . '__' . $variables['element']['#name'] . '__' . $variables['element']['#context']['#view_id'];
    // TODO: Add a new suggestion that also adds the form ID
  }
}

/**
 * Implements hook_theme_suggestions_fieldset_alter().
 *
 * @param array $suggestions
 * @param array $variables
 *
 * @return void
 */
function analytics_theme_suggestions_fieldset_alter(array &$suggestions, array $variables): void
{
  $suggestions[] = 'form_element__fieldset';
  $suggestions[] = 'form_element__fieldset__' . $variables['element']['#context']['#view_id'];
}
