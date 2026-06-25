<?php

namespace Drupal\wpc_rfi_forms_sources\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Language\Language;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the wpc_rfi_forms_sources entity edit forms.
 *
 * @ingroup wpc_rfi_forms_sources
 */
class LeadSourceForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\wpc_rfi_forms_sources\Entity\LeadSource */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    $form['langcode'] = [
      '#title' => $this->t('Language'),
      '#type' => 'language_select',
      '#default_value' => $entity->getUntranslated()->language()->getId(),
      '#languages' => Language::STATE_ALL,
    ];

    $form['#attached'] =[
      'library' => array('wpc_rfi_forms_sources/formstyles'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.wpc_rfi_forms_sources_leadsource.collection');
    $entity = $this->getEntity();
    $entity->save();
  }

}
