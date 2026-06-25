<?php

namespace Drupal\asu_newcollege_custom_tokens\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form for adding and editing Custom Token config entities.
 */
class CustomTokenForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\asu_newcollege_custom_tokens\Entity\CustomToken $token */
    $token = $this->entity;
    $is_new = $token->isNew();

    // ── Basic fields ────────────────────────────────────────────────────────

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#description' => $this->t('Name for this token (e.g. "Application Deadline").'),
      '#maxlength' => 255,
      '#default_value' => $token->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#description' => $this->t('Used in the token syntax: <code>[asu_newcollege:<strong>machine-name</strong>]</code>.'),
      '#default_value' => $token->id(),
      '#disabled' => !$is_new,
      '#machine_name' => [
        'exists' => '\Drupal\asu_newcollege_custom_tokens\Entity\CustomToken::load',
        'source' => ['label'],
      ],
    ];

    $form['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#description' => $this->t('Optional. Briefly describe what this token represents.'),
      '#maxlength' => 255,
      '#default_value' => $token->get('description'),
    ];

    $type = $form_state->getValue('type') ?? $token->get('type') ?? 'text';

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Token type'),
      '#options' => [
        'text' => $this->t('Text'),
        'date' => $this->t('Date'),
        'html' => $this->t('HTML'),
      ],
      '#default_value' => $type,
      '#required' => TRUE,
      '#disabled' => !$is_new,
      '#ajax' => [
        'callback' => '::ajaxTypeCallback',
        'wrapper' => 'token-value-wrapper',
        'event' => 'change',
      ],
    ];

    // Wrapper for the dynamic value fields.
    $form['value_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'token-value-wrapper'],
    ];

    $form['value_wrapper']['text_value'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Text value'),
      '#description' => $this->t('The text that will replace the token.'),
      '#default_value' => $token->get('text_value'),
      '#rows' => 4,
      '#required' => $type === 'text',
      '#access' => $type === 'text',
    ];

    $form['value_wrapper']['html_value'] = [
      '#type' => 'textarea',
      '#title' => $this->t('HTML value'),
      '#description' => $this->t('Inline HTML tags such as &lt;strong&gt;, &lt;a&gt;, &lt;span&gt;, &lt;img&gt;, and &lt;br&gt; can be used inside &lt;p&gt;. Block tags such as &lt;div&gt;, &lt;ul&gt;, or &lt;table&gt; should not be placed inside &lt;p&gt;.'),
      '#default_value' => $token->get('html_value'),
      '#rows' => 8,
      '#required' => $type === 'html',
      '#access' => $type === 'html',
    ];

    $form['value_wrapper']['date_value'] = [
      '#type' => 'date',
      '#title' => $this->t('Date'),
      '#description' => $this->t('Pick a date using the calendar widget.'),
      '#default_value' => $token->get('date_value'),
      '#attributes' => ['type' => 'date'],
      '#required' => $type === 'date',
      '#access' => $type === 'date',
    ];

    $form['value_wrapper']['date_format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PHP date format'),
      '#description' => $this->t(
        'Enter any valid <a href="https://www.php.net/manual/en/datetime.format.php" target="_blank">PHP date format</a> string.
        Examples: <code>F j, Y</code> → January 1, 2025 | <code>d/m/Y</code> → 01/01/2025 | <code>M Y</code> → Jan 2025'
      ),
      '#default_value' => $token->get('date_format') ?: 'F j, Y',
      '#maxlength' => 64,
      '#required' => $type === 'date',
      '#access' => $type === 'date',
    ];

    // Live preview of the formatted date.
    $preview = '';
    if ($type === 'date' && $token->get('date_value')) {
      $timestamp = strtotime($token->get('date_value'));
      $fmt = $token->get('date_format') ?: 'F j, Y';
      if ($timestamp !== FALSE) {
        $preview = date($fmt, $timestamp);
      }
    }

    $form['value_wrapper']['date_preview'] = [
      '#type' => 'item',
      '#title' => $this->t('Preview'),
      '#markup' => $preview ? '<strong>' . htmlspecialchars($preview) . '</strong>' : '<em>' . $this->t('Save to see a preview.') . '</em>',
      '#access' => $type === 'date',
    ];

    if (!$is_new) {
      $form['usage'] = [
        '#type' => 'item',
        '#title' => $this->t('Token usage'),
        '#markup' => '<code>[asu_newcollege:' . $token->id() . ']</code>',
        '#description' => $this->t('Copy and paste this token into any rich text field that has the "Replace tokens" filter enabled.'),
        '#weight' => 100,
      ];
    }

    return $form;
  }

  /**
   * AJAX callback: rebuild the value wrapper when token type changes.
   */
  public function ajaxTypeCallback(array &$form, FormStateInterface $form_state): array {
    return $form['value_wrapper'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);

    $type = $form_state->getValue('type');

    if ($type === 'text' && empty(trim($form_state->getValue('text_value')))) {
      $form_state->setErrorByName('text_value', $this->t('Text value is required for text tokens.'));
    }

    if ($type === 'html') {
      $html_content = $form_state->getValue('html_value') ?? '';
      if (empty(trim(strip_tags($html_content)))) {
        $form_state->setErrorByName('html_value', $this->t('HTML value is required for HTML tokens.'));
      }
    }

    if ($type === 'date') {
      if (empty($form_state->getValue('date_value'))) {
        $form_state->setErrorByName('date_value', $this->t('Date is required for date tokens.'));
      }
      if (empty($form_state->getValue('date_format'))) {
        $form_state->setErrorByName('date_format', $this->t('PHP date format is required for date tokens.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    /** @var \Drupal\asu_newcollege_custom_tokens\Entity\CustomToken $token */
    $token = $this->entity;

    if ($token->get('type') === 'text') {
      $token->set('date_value', '');
      $token->set('date_format', '');
      $token->set('html_value', '');
    }
    elseif ($token->get('type') === 'date') {
      $token->set('text_value', '');
      $token->set('html_value', '');
    }
    else {
      $token->set('text_value', '');
      $token->set('date_value', '');
      $token->set('date_format', '');
    }

    $status = parent::save($form, $form_state);

    $label = $token->label();
    if ($status === SAVED_NEW) {
      $this->messenger()->addStatus($this->t('Token <strong>%label</strong> created. Use it as <code>[asu_newcollege:%id]</code>.', [
        '%label' => $label,
        '%id' => $token->id(),
      ]));
    }
    else {
      $this->messenger()->addStatus($this->t('Token <strong>%label</strong> updated.', ['%label' => $label]));
    }

    $form_state->setRedirectUrl(Url::fromRoute('entity.asu_newcollege_custom_token.collection'));
    return $status;
  }

}
