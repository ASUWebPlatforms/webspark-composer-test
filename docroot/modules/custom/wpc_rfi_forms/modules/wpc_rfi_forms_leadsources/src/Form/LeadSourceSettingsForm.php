<?php

namespace Drupal\wpc_rfi_forms_sources\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class LeadSourceSettingsForm.
 *
 * @ingroup wpc_rfi_forms_sources
 */
class LeadSourceSettingsForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'wpc_rfi_forms_sources_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty implementation of the abstract submit class.
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['leadsources_settings']['#markup'] = 'Settings form for Lead Sources. Manage field settings here.';
    return $form;
  }

}
