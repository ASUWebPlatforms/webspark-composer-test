<?php

namespace Drupal\wpc_rfi_forms_programs\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Language\Language;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the wpc_rfi_forms_programs entity edit forms.
 *
 * @ingroup wpc_rfi_forms_programs
 */
class ProgramForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\wpc_rfi_forms_programs\Entity\Program */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    $form['langcode'] = [
      '#title' => $this->t('Language'),
      '#type' => 'language_select',
      '#default_value' => $entity->getUntranslated()->language()->getId(),
      '#languages' => Language::STATE_ALL,
    ];

    $form['#attached'] =[
      'library' => array('wpc_rfi_forms_programs/formstyles'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.wpc_rfi_forms_programs_program.collection');
    $entity = $this->getEntity();
    $entity->save();
  }

}
