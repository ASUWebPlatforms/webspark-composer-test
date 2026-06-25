<?php

namespace Drupal\asu_campus_fit\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;

/**
 * Form submission handler.
 *
 * @WebformHandler(
 *   id = "campus_fit_webform_handler",
 *   label = @Translation("Update campus fit soce value after submission"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Update campus fit soce value after submission"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 **/
class campusFitWebformHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {

    $values = $webform_submission->getData();
    if (empty($values['result_nid'])) {
      $submissin_settings = \Drupal::service('getJsSettings')->getJsSettings($values);
      $values['score'] = $submissin_settings['score_value'];
      $webform_submission->setData($values);
      $webform_submission->save();
      $values1 = $webform_submission->getData();

    }
  }

}
