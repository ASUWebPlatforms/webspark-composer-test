<?php

namespace Drupal\asu_format_advising\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\asu_format_advising\NodeQueryService;
use Drupal\asu_format_advising\FieldDetailsService;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\asu_document_creation\LatexDocumentCreation;
use Drupal\asu_document_creation\WordDocumentCreation;
use Drupal\asu_document_creation\DocumentSaveService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


/**
 * Provides a form for format advising.
 */
class FormatAdvisingForm extends FormBase {

  /**
   * The field details service.
   *
   * @var \Drupal\asu_format_advising\FieldDetailsService
   */
  protected $fieldDetailsService;

  /**
   * The node query service.
   *
   * @var \Drupal\asu_format_advising\NodeQueryService
   */
  protected $nodeQueryService;

  /**
   * The document creation service.
   *
   * @var \Drupal\asu_document_creation\Service\DocumentSaveService
   */
  protected $documentSaveService;

  /**
   * Constructs a new FormatAdvisingForm.
   *
   * @param \Drupal\asu_format_advising\FieldDetailsService $fieldDetailsService
   *   The field details service.
   * @param \Drupal\asu_format_advising\NodeQueryService $nodeQueryService
   *   The node query service.
   */
  public function __construct(
    FieldDetailsService $fieldDetailsService,
    NodeQueryService $nodeQueryService,
    DocumentSaveService $documentSaveService
  ) {
    $this->fieldDetailsService = $fieldDetailsService;
    $this->nodeQueryService = $nodeQueryService;
    $this->documentSaveService = $documentSaveService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('asu_format_advising.field_details'),
      $container->get('asu_format_advising.node_query'),
      $container->get('asu_document_creation.document_save_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'asu_format_advising_multistep_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Loads or create nodes
    $node = $this->nodeQueryService->getUserFormatAdvising();
    $formstateValues = $form_state->getValues();

    $config = $this->config('asu_format_advising.settings');

    $form['#theme'] = 'form_asu_format_advising_multistep_form';
    $form['#attached']['library'][] = 'asu_format_advising/form_js_behaviors';

    $step = 1;
    // Check if there's an existing cookie with form data
    if (isset($_COOKIE['FormatAdvisingData']) && !$form_state->has('step')) {
      $cookieData = json_decode($_COOKIE['FormatAdvisingData'], true);
      $step = $cookieData['step'] ?? $step;
      $form_state->set('step', $step);
    } else {
      // Initialize the step if it's not already set.
      if (!$form_state->has('step')) {
        $form_state->set('step', $step);
      }
    }

    $datetime = new DrupalDateTime();

    if (!isset($formstateValues['field_clear_old_data']) || $formstateValues['field_clear_old_data'][1] != "1") {
      $default_values = isset($_COOKIE['FormatAdvisingData']) ? json_decode($_COOKIE['FormatAdvisingData'], true)['data'] : NULL;
      if ($default_values == NULL) {
        if ($node) {
          $this->nodeQueryService->saveCookie();
        }
      }
    }

    $step = $form_state->get('step');
    $save_continue = $form_state->get('save_continue');

    // Define the titles for each step.
    $step_titles = [
      1 => $this->t(''),
      2 => $this->t('Step 1 of 8: Tell Us About Yourself'),
      3 => $this->t('Step 2 of 8: Document Information'),
      4 => $this->t('Step 3 of 8: Table of Contents'),
      5 => $this->t('Step 4 of 8: Abstract Information'),
      6 => $this->t('Step 5 of 8: Additional Sections'),
      7 => $this->t('Step 6 of 8: Graduate Supervisory Committee'),
      8 => $this->t('Step 7 of 8: Review Your Paper'),
      9 => $this->t('Step 8 of 8: Create Document'),
    ];

    // Set the current step title.
    $form['#title'] = $step_titles[$step];

    $form['#tree'] = TRUE;

    $form['aside_text'] = array(
      '#type' => 'hidden',
      '#value' => $config->get('aside_text.value'),
    );

    $form['step'] = [
      '#type' => 'hidden',
      '#value' => $step,
    ];

    // For skip steps when checking summary
    $form['save_continue'] = [
      '#type' => 'hidden',
      '#value' => $save_continue,
    ];

    // Use a switch structure to add form elements for the current step.
    switch ($step) {
      case 1:
        $form['logsheet_introduction'] = array(
          '#type' => 'markup',
          '#markup' => $config->get('introduction.value'),
        );
        if (
          !empty($node->field_first_title)
        ) {
          $field = $this->fieldDetailsService->getFieldDetails('field_clear_old_data');
          $form['field_clear_old_data'] = array(
            '#type' => $field['type'],
            '#title' => $field['question'],
            '#options' => $field['options'],
            '#description' => $field['description'],
          );
        }
        break;
      case 2:
        include DRUPAL_ROOT . '/modules/custom/asu_format_advising/includes/02-about-yourself.inc';
        break;
      case 3:
        include DRUPAL_ROOT . '/modules/custom/asu_format_advising/includes/03-document-information.inc';
        break;
      case 4:
        include DRUPAL_ROOT . '/modules/custom/asu_format_advising/includes/04-chapters.inc';
        break;
      case 5:
        include DRUPAL_ROOT . '/modules/custom/asu_format_advising/includes/05-abstract.inc';
        break;
      case 6:
        include DRUPAL_ROOT . '/modules/custom/asu_format_advising/includes/06-documents.inc';
        break;
      case 7:
        include DRUPAL_ROOT . '/modules/custom/asu_format_advising/includes/07-committee-chairs.inc';
        break;
      case 8:

        $form['summary'] = [
          '#type' => 'element_summary',
          '#data' => $this->getEditCards($step_titles, $node),
        ];

        // Index for navigation
        $form['index'] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['flex']],
        ];

        for ($i = 2; $i <= 7; $i++) {
          $form['index']['step_' . $i] = [
            '#type' => 'submit',
            '#value' => $this->t('Step @num', ['@num' => ($i - 1)]),
            '#submit' => ['::jumpToStep'],
            '#name' => 'jump_' . $i,
            '#limit_validation_errors' => [],
            '#attributes' => ['class' => ['btn-sm bg-gold text-dark border-0']],
          ];
        }
        break;
      case 9:
        $form['create_document'] = [
          '#type' => 'markup',
          '#markup' => $default_values['field_template_name'] == '1' ? $config->get('create_document.value') : $config->get('create_document_latex.value'),
        ];

        break;
    }

    if (!$save_continue) {

      // Add navigation buttons.
      if ($step > 1) {
        $form['actions']['previous'] = [
          '#type' => 'submit',
          '#value' => $this->t('Previous'),
          '#submit' => ['::previousStep'],
          '#limit_validation_errors' => [],
        ];
      }
      if ($step < 9) {
        $form['actions']['next'] = [
          '#type' => 'submit',
          '#value' => $this->t('Next'),
          '#submit' => ['::nextStep']
        ];
        if ($step == 2) {
          $form['actions']['next']['#attached'] = ['library' => ['asu_format_advising/first_next_message']];
        }
      } else {
        $form['actions']['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Download Document')
        ];

        $form['actions']['new_document'] = [
          '#type' => 'submit',
          '#value' => $this->t('Start Over'),
          '#submit' => ['::clearCookieStep'],
          '#btnname' => 'new_doc',
          '#attributes' => ['class' => ['btn bg-gold text-dark border-0'], 'style' => ['float:right;']],
        ];
      }
    } else {
      $form['actions']['save'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save and Continue'),
        '#submit' => ['::saveContinue']
      ];
    }

    return $form;
  }

  public function clearCookieStep(array &$form, FormStateInterface $form_state) {
    $form_state->set('step', 1);
    $this->saveDataAndRebuildForm(1, $form_state);
    setcookie('FormatAdvisingData', '', time() - 3600, "/");
  }

  /**
   * Previous step submission handler.
   */
  public function previousStep(array &$form, FormStateInterface $form_state) {
    $step = $form_state->get('step');
    if ($step > 1) {
      $step--;
      $form_state->set('step', $step);
    }
    $this->saveDataAndRebuildForm($step, $form_state);
  }

  /**
   * Next step submission handler.
   */
  public function nextStep(array &$form, FormStateInterface $form_state) {
    $step = $form_state->get('step');
    $formState = $form_state->getValues();
    if ($step == 1) {
      $this->clearData($step, $form_state);
    }
    if ($step < 9) {
      $step++;
      $form_state->set('step', $step);
    }
    if ($step != 1) {
      $clear = isset($formState['field_clear_old_data']) ? $formState['field_clear_old_data'] : [1 => 0];
      if ($clear[1] != '1') {
        $this->saveDataAndRebuildForm($step, $form_state);
      } else {
        $form_state->set('step', $step);
        $form_state->setRebuild(TRUE);
      }
    }
  }

  // Go to Summary page again
  public function saveContinue(array &$form, FormStateInterface $form_state) {
    $this->saveDataAndRebuildForm(8, $form_state);
    $form_state->set('save_continue', FALSE);
    $form_state->set('step', 8);
    $form_state->setRebuild(TRUE);
    return $form;
  }

  public function jumpToStep(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $form_state->set('save_continue', true);
    if (preg_match('/jump_(\d+)/', $trigger['#name'], $matches)) {
      $form_state->set('step', $matches[1]);
    }
    $form_state->setRebuild(TRUE);
  }

  protected function clearData($step, FormStateInterface $form_state) {
    $value = $form_state->getValue('field_clear_old_data')[1];
    if ((int)$value) {
      $this->nodeQueryService->clearNodeData();
      setcookie('FormatAdvisingData', '', time() - 3600, "/");
    }
  }

  protected function saveDataAndRebuildForm($step, FormStateInterface $form_state) {
    // Update cookie on next step to persist form
    $allValues = $form_state->getValues();

    $data = array_filter($allValues, function ($key) {
      return !in_array($key, ['chapter_count', 'member_count', 'actions', 'step', 'submit', 'next', 'previous', 'form_build_id', 'form_token', 'form_id', 'op', 'aside_text', 'index', 'summary', 'save_continue', 'field_clear_old_data'], true);
    }, ARRAY_FILTER_USE_KEY);

    foreach ([
        'field_defense_date',
        'field_graduation_date'
      ] as $date_field) {
      if (array_key_exists($date_field, $data) && !is_null($data[$date_field])) {
        $date_value = $form_state->getValue($date_field);
        $data[$date_field] = $date_value->format('Y-m-d');
      }
    }

    foreach ([
        'chapters',
        'members'
      ] as $multi) {
      if (array_key_exists($multi, $data)) {
        $data[$multi] = array_filter($data[$multi], function ($key) {
          return strpos($key, '_one') === false;
        }, ARRAY_FILTER_USE_KEY);
      }
    }

    if (array_key_exists('field_document_sections', $data)) {
      $data['field_document_sections'] = array_keys(array_filter($data['field_document_sections'], function ($value) {
        return $value !== 0;
      }));
    }

    if (isset($_COOKIE['FormatAdvisingData'])) {
      $cookie_data = json_decode($_COOKIE['FormatAdvisingData'], true);
      $data = array_merge($cookie_data['data'], $data);
    }

    $formData = [
      'step' => $step,
      'data' => $data
    ];

    $cookieData = json_encode($formData);
    setcookie('FormatAdvisingData', $cookieData, time() + (86400 * 30), "/");

    $this->nodeQueryService->saveStep($data);

    $form_state->setRebuild(true);
  }

  private function getYearRange() {
    return (date("Y")) . ':' . (date("Y") + 3);
  }

  private function isMember($string) {
    return strpos($string, 'member') !== false;
  }

  public function fieldsetAjaxCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    return $this->isMember($trigger['#name']) ? $form['members'] : $form['chapters'];
  }

  public function addOne(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $count_name = $this->isMember($trigger['#name']) ? 'member_count' : 'chapter_count';

    $count = $form_state->getValue($count_name) ?? 1; // If $count_name is not set, default to 1
    $form_state->set($count_name, $count + 1);
    $form_state->setRebuild();
  }

  public function removeOne(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $count_name = $this->isMember($trigger['#name']) ? 'member_count' : 'chapter_count';

    $count = $form_state->getValue($count_name) ?? 1;
    if ($count > 1) {
      $form_state->set($count_name, $count - 1);
    }
    $form_state->setRebuild();
  }

  private function isWord($values) {
    return (int)$values['field_template_name'] == 1;
  }

  private function formatDate($stringDate) {
    if (!is_null($stringDate)) {
      $date = new \DateTime($stringDate);
      return $date->format('F Y');
    } else {
      return NULL;
    }
  }

  private function getEditCards($steps, $node) {
    $output = [];
    foreach ($steps as $i => $step) {
      if ($i > 1 && $i < 8) {
        $output[$i] = [
          'step' => $i,
          'title' => preg_replace('/^Step \d+ of \d+: /', '', $step),
          'fields' => $this->getStepFields($i, $node)
        ];
      }
    }
    return $output;
  }

  private function getAllowedLabel($node, $field_name) {
    return $this->nodeQueryService->getLabel($field_name, $node->get($field_name)->value);
  }

  private function getStepFields($stepIndex, $node) {

    $fields = [];

    switch ($stepIndex) {
      case 2:
        $fields = [
          'Student\'s Full Legal Name' => $node->get('field_full_name')->value,
          'Degree' => $this->getAllowedLabel($node, 'field_degree'),
          'Defense Date' => $this->formatDate($node->get('field_defense_date')->value),
          'Graduation Date' => $this->formatDate($node->get('field_graduation_date')->value)
        ];
        break;
      case 3:
        $fields = [
          'Template Type' => $this->getAllowedLabel($node, 'field_template_name'),
          'Style Guide' => $this->getAllowedLabel($node, 'field_style_guide'),
          'Document Type' => $this->getAllowedLabel($node, 'field_document_type'),
          'Font Type' => $this->getAllowedLabel($node, 'field_approved_font'),
          'Font Size' => $node->get('field_font_size')->value . 'pt',
          'Title line 1' => $node->get('field_first_title')->value,
          'Title line 2' => $node->get('field_second_title')->value,
          'Title line 3' => $node->get('field_third_title')->value
        ];
        break;
      case 4:
        foreach ($node->get('field_chapter_title')->getValue() as $key => $chapter) {
          if (!empty($chapter)) {
            $fields['Chapter ' . ($key + 1)] = $chapter['value'];
          }
        }
        break;
      case 5:
        $fields['Abstract'] = $node->get('field_abstract')->value;
        break;
      case 6:
        $specialCases = [
          'figures' => 'List of Figures',
          'tables' => 'List of Tables',
          'symbols' => 'List of Symbols',
          'biographical' => 'Biographical Sketch',
        ];

        foreach ($node->get('field_document_sections')->getValue() as $document) {         
        
        $value = strtolower($document['value']);
    
        // Check if value exists in the special cases array
        if (isset($specialCases[$value])) {
            $fields[$specialCases[$value]] = 'Yes';
        } else {
            $fields[ucfirst($document['value'])] = 'Yes';
        }

        }
        break;
      case 7:
        if(!$node->get('field_committee_mem_first')->isEmpty()){
          $fields = [
            'Co-Chair' => $node->get('field_committee_chair_name')->value,
            'Co-Chair ' => $node->get('field_committee_mem_first')->value
          ];
        }else{
          $fields['Committee Chair'] = $node->get('field_committee_chair_name')->value;
        }
        foreach ($node->get('field_your_committee')->getValue() as $key => $member) {
          $fields['Committee member ' . ($key + 1)] = $member['value'];
        }
        break;
    }

    return $fields;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $node = $this->nodeQueryService->getUserFormatAdvising();

    $values = isset($_COOKIE['FormatAdvisingData']) ? json_decode($_COOKIE['FormatAdvisingData'], true)['data'] : NULL;
    $values = $this->nodeQueryService->getLabelValues($values);

    if ($values) {
      $document = $this->documentSaveService->createDocument($values);
      if ($this->isWord($values)) {
        $response = new BinaryFileResponse($document);
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->setContentDisposition(
          ResponseHeaderBag::DISPOSITION_ATTACHMENT,
          basename($document)
        );
        $response->send();
        exit;
      } else {
        $response = new BinaryFileResponse($document);
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->setContentDisposition(
          ResponseHeaderBag::DISPOSITION_ATTACHMENT,
          basename($document)
        );
        $response->send();
        exit;
      }
    }
  }
}
