<?php

namespace Drupal\gp_dataquiz_handler\Plugin\WebformHandler;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\taxonomy\Entity\Term;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use function PHPUnit\Framework\assertIsArray;
use function PHPUnit\Framework\isNan;


/**
 * GP Data Quiz handler.
 *
 * @WebformHandler(
 *   id = "gpdataquiz",
 *   label = @Translation("GP Data Quiz"),
 *   category = @Translation("Custom"),
 *   description = @Translation("A webform submission handler for a data quiz on Get Protected."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_IGNORED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class GpDataQuizHandler extends WebformHandlerBase {

  /**
   * The token manager.
   *
   * @var \Drupal\webform\WebformTokenManagerInterface
   */
  protected $tokenManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->tokenManager = $container->get('webform.token_manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'message' => 'This is a custom message.',
      'debug' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Development.
    $form['development'] = [
      '#type' => 'details',
      '#title' => $this->t('Development settings'),
    ];
    $form['development']['debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable debugging'),
      '#description' => $this->t('If checked, every handler method invoked will be displayed onscreen to all users.'),
      '#return_value' => TRUE,
      '#default_value' => $this->configuration['debug'],
    ];

    return $this->setSettingsParents($form);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['debug'] = (bool) $form_state->getValue('debug');
  }

  /**
   * {@inheritdoc}
   */
  public function alterElements(array &$elements, WebformInterface $webform) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function overrideSettings(array &$settings, WebformSubmissionInterface $webform_submission) {
    $original_confirmation_message =  $settings['confirmation_message'];
    $data = $webform_submission->getData();
    $webform = $webform_submission->getWebform();
    $elements = $webform->getElementsDecodedAndFlattened();
    $classification_array = [];
    foreach($data as $key => $value){
      $element = $elements[$key];
      if ($element['#type'] == 'radios'){
        if ($value == 'Yes'){
          if(isset($element['#end_state']) && is_numeric($element['#end_state'])){
            $class_term_array = [];
            $class_term = \Drupal\taxonomy\Entity\Term::load($element['#end_state']);
            $class_term_name = $class_term->name->value;
            $class_term_description = $class_term->getDescription();
            $class_term_weight = $class_term->getWeight();
            $class_term_array['weight'] = $class_term_weight;
            $class_term_array['name'] = $class_term_name;
            $class_term_array['description'] = $class_term_description;
            $classification_array[] = $class_term_array;
          };
        } elseif ($value == 'No'){
          if(isset($element['#end_state_negative']) && is_numeric($element['#end_state_negative'])){
            $class_term = \Drupal\taxonomy\Entity\Term::load($element['#end_state_negative']);
            $class_term_name = $class_term->name->value;
            $class_term_description = $class_term->getDescription();
            $class_term_weight = $class_term->getWeight();
            $class_term_array['weight'] = $class_term_weight;
            $class_term_array['name'] = $class_term_name;
            $class_term_array['description'] = $class_term_description;
            $classification_array[] = $class_term_array;
          };
        }
      } elseif ($element['#type'] == 'webform_entity_radios'){
        if (is_numeric($value)){
          $this_term = Term::load($value);
          $this_term_tags = $this_term->get('field_transaction_tags');
          if(isset($this_term_tags)){
            foreach ($this_term_tags as $key => $category){
              // $class_term_number = $category->get('target_id'); //not needed?
              $category_number = $category->get('target_id')->getValue();
              $class_term = \Drupal\taxonomy\Entity\Term::load($category_number);
              $class_term_name = $class_term->name->value;
              $class_term_description = $class_term->getDescription();
              $class_term_weight = $class_term->getWeight();
              $class_term_array['weight'] = $class_term_weight;
              $class_term_array['name'] = $class_term_name;
              $class_term_array['description'] = $class_term_description;
              $classification_array[] = $class_term_array;
              $classification_array[] = $class_term_array;
            }
          }
        }
      } 
    }

    $priority_classification = _record_sort($classification_array, "weight");
    $classification_result_array = _classification_sort($priority_classification);
    $combined_results = (array)$classification_result_array;
    $confirmation_message = implode($combined_results);
    $programattic_confirmation_message = $confirmation_message;
    $combined_confirmation_message = $original_confirmation_message . $programattic_confirmation_message;
    $settings['confirmation_message'] = $combined_confirmation_message;
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function alterForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
    // add error here if no classification is set
    if ($value = $form_state->getValue('element')) {
      $form_state->setErrorByName('element', $this->t('The element must be empty. You entered %value.', ['%value' => $value]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function confirmForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function preCreate(array &$values) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function postCreate(WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function postLoad(WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function preDelete(WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function postDelete(WebformSubmissionInterface $webform_submission) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $this->debug(__FUNCTION__, $update ? 'update' : 'insert');
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessConfirmation(array &$variables) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function createHandler() {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function updateHandler() {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteHandler() {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function createElement($key, array $element) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function updateElement($key, array $element, array $original_element) {
    $this->debug(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteElement($key, array $element) {
    $this->debug(__FUNCTION__);
  }

  /**
   * Display the invoked plugin method to end user.
   *
   * @param string $method_name
   *   The invoked method name.
   * @param string $context1
   *   Additional parameter passed to the invoked method name.
   */
  protected function debug($method_name, $context1 = NULL) {
    if (!empty($this->configuration['debug'])) {
      $t_args = [
        '@id' => $this->getHandlerId(),
        '@class_name' => get_class($this),
        '@method_name' => $method_name,
        '@context1' => $context1,
      ];
      $this->messenger()->addWarning($this->t('Invoked @id: @class_name:@method_name @context1', $t_args), TRUE);
    }
  }

}

function _record_sort($records, $field, $reverse=false)
{
  $hash = array();
  foreach($records as $record)
  {
      $hash[$record[$field]] = $record;
  }
  ($reverse)? krsort($hash) : ksort($hash);
  $records = array();
  foreach($hash as $record)
  {
      $records []= $record;
  }
  return $records;
}

function _classification_sort($priority_classification){
  $first_classification = null;
  $classification_result_array = [];
  if (!empty($priority_classification)){
    $first_classification = current($priority_classification);
    $classification_name = $first_classification['name'];
    $classification_description = $first_classification['description'];
  
    $classification_heading = array(
      '#type' => 'html_tag',
      '#tag' => 'h2',
      '#value' => $classification_name,
    );

    $classification_result_array[] = \Drupal::service('renderer')->render($classification_heading);
    $classification_result_array[] = $classification_description;
  }

  return $classification_result_array;
}
