<?php

namespace Drupal\gp_dataquiz_handler\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GpDataQuizSettingsForm extends ConfigFormBase {

    protected $entityTypeManager;

    public function __construct(EntityTypeManagerInterface $entity_type_manager) {
        $this->entityTypeManager = $entity_type_manager;
    }

    public static function create(ContainerInterface $container) {
        return new static(
        $container->get('entity_type.manager')
        );
    }


    protected function getEditableConfigNames() {
    return ['gp_dataquiz_handler.settings'];
    }

    public function getFormId() {
    return 'gp_dataquiz_handler_settings_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('gp_dataquiz_handler.settings');
    
    $vocabularies = $this->entityTypeManager->getStorage('taxonomy_vocabulary')->loadMultiple();
    $options = [];
    foreach ($vocabularies as $vocabulary) {
        $options[$vocabulary->id()] = $vocabulary->label();
    }

    $form['end_states_taxonomy'] = [
        '#type' => 'select',
        '#title' => $this->t('End States Taxonomy'),
        '#default_value' => $this->config('gp_dataquiz_handler.settings')->get('end_states_taxonomy'),
        '#required' => TRUE,
        '#options' => $options,
        '#description' => $this->t('Choose a vocabulary to use for End States.'),
    ];

    $form['transaction_type_taxonomy'] = [
        '#type' => 'select',
        '#title' => $this->t('Transaction Type'),
        '#default_value' => $this->config('gp_dataquiz_handler.settings')->get('transaction_type_taxonomy'),
        '#required' => TRUE,
        '#options' => $options,
        '#description' => $this->t('Choose a vocabulary to use for Transaction Type.'),
    ];

    return parent::buildForm($form, $form_state);
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $this->config('gp_dataquiz_handler.settings')
        ->set('end_states_taxonomy', $form_state->getValue('end_states_taxonomy'))
        ->set('transaction_type_taxonomy', $form_state->getValue('transaction_type_taxonomy'))
        ->save();

    parent::submitForm($form, $form_state);
    }
}