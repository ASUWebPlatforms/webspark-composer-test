<?php

namespace Drupal\wpc_rfi_forms_programs\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ProgramsSettingsForm.
 *
 * @ingroup wpc_rfi_forms_programs
 */
class ProgramSettingsForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'wpc_rfi_forms_programs_settings';
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
    $form['program_settings']['#markup'] = 'Settings form for Programs. Manage field settings here.';
    return $form;
  }

}
