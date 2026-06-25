<?php

namespace Drupal\asu_custom_schema\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\asu_custom_schema\Service\SchemaBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Per-node Schema Settings form (displayed as a node tab).
 *
 * Reads and writes the revision-aware base fields:
 *  - asu_schema_enabled (boolean)
 *  - asu_schema_json    (string_long)
 */
class NodeSchemaForm extends FormBase {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected SchemaBuilder $schemaBuilder,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('asu_custom_schema.builder'),
    );
  }

  public function getFormId(): string {
    return 'asu_custom_schema_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, ?NodeInterface $node = NULL): array {
    $auto      = $this->schemaBuilder->buildAutoSchema($node);
    $auto_json = json_encode($auto, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);

    // Read current values from base fields.
    $schema_enabled = (bool) $node->get('asu_schema_enabled')->value;
    $schema_json    = $node->get('asu_schema_json')->value ?: $auto_json;

    $form['node_id'] = ['#type' => 'value', '#value' => $node->id()];

    // ── Content type context note ────────────────────────────────────────────
    $bundle_label = $node->type->entity?->label() ?? $node->bundle();
    $form['bundle_notice'] = [
      '#type'   => 'markup',
      '#markup' => '<p class="description">'
        . $this->t(
            'Schema is enabled for the <strong>@type</strong> content type. '
            . 'Use the toggle below to enable it for this specific node.',
            ['@type' => $bundle_label]
          )
        . '</p>',
    ];

    // ── Per-node enable toggle ───────────────────────────────────────────────
    $form['schema_enabled'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Enable Schema.org markup for this node'),
      '#description'   => $this->t('Outputs JSON-LD structured data in the page <code>&lt;head&gt;</code>. This setting is saved per revision.'),
      '#default_value' => (int) $schema_enabled,
    ];

    // ── Schema JSON ──────────────────────────────────────────────────────────
    $form['schema_base_json'] = [
      '#type'          => 'textarea',
      '#title'         => $this->t('Schema JSON'),
      '#description'   => $this->t('Auto-generated from node data. Edit as needed. Supports tokens. Use Reset to regenerate from current node data. Saved per revision.'),
      '#default_value' => $schema_json,
      '#rows'          => 20,
      '#attributes'    => ['style' => 'font-family:monospace;font-size:.875em'],
    ];

    // ── Token browser ────────────────────────────────────────────────────────
    $form['tokens'] = [
      '#type'  => 'details',
      '#title' => $this->t('Available Tokens'),
      '#open'  => FALSE,
    ];
    $form['tokens']['tree'] = [
      '#theme'           => 'token_tree_link',
      '#token_types'     => ['node', 'site'],
      '#show_restricted' => FALSE,
      '#global_types'    => TRUE,
    ];

    // ── Actions ──────────────────────────────────────────────────────────────
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type'        => 'submit',
      '#value'       => $this->t('Save'),
      '#button_type' => 'primary',
    ];
    $form['actions']['reset'] = [
      '#type'                    => 'submit',
      '#value'                   => $this->t('Reset to Auto-Generated'),
      '#submit'                  => ['::submitReset'],
      '#limit_validation_errors' => [['node_id']],
      '#attributes'              => ['class' => ['button--danger']],
    ];

    return $form;
  }

  // ---------------------------------------------------------------------------
  // Validation
  // ---------------------------------------------------------------------------

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $value = trim($form_state->getValue('schema_base_json') ?? '');
    if ($value === '') {
      return;
    }
    $sanitized = preg_replace('/\[[a-zA-Z0-9_:\-]+\]/', '"__TOKEN__"', $value);
    json_decode($sanitized);
    if (json_last_error() !== JSON_ERROR_NONE) {
      $form_state->setErrorByName('schema_base_json', $this->t('Invalid JSON: @msg', ['@msg' => json_last_error_msg()]));
    }
  }

  // ---------------------------------------------------------------------------
  // Submit handlers
  // ---------------------------------------------------------------------------

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $node = $this->loadNode($form_state->getValue('node_id'));
    if (!$node) {
      return;
    }

    $node->set('asu_schema_enabled', (bool) $form_state->getValue('schema_enabled'));
    $node->set('asu_schema_json', trim($form_state->getValue('schema_base_json') ?? ''));

    // Update the current revision without creating a new one.
    // setSyncing(TRUE) suppresses revision log message enforcement on
    // content types that require it.
    $node->setNewRevision(FALSE);
    $node->setSyncing(TRUE);
    $node->save();

    Cache::invalidateTags(['node:' . $node->id()]);
    $this->messenger()->addStatus($this->t('Schema settings saved.'));
  }

  /**
   * Clears the saved schema JSON so auto-generation takes over again.
   */
  public function submitReset(array &$form, FormStateInterface $form_state): void {
    $node = $this->loadNode($form_state->getValue('node_id'));
    if (!$node) {
      return;
    }

    $node->set('asu_schema_json', '');
    $node->setNewRevision(FALSE);
    $node->setSyncing(TRUE);
    $node->save();

    Cache::invalidateTags(['node:' . $node->id()]);
    $this->messenger()->addStatus($this->t('Schema reset to auto-generated.'));
  }

  // ---------------------------------------------------------------------------
  // Helpers
  // ---------------------------------------------------------------------------

  protected function loadNode(int|string $nid): ?NodeInterface {
    // Use loadUnchanged() to bypass the static entity cache and always
    // get a fresh object from the database before setting field values.
    $node = $this->entityTypeManager->getStorage('node')->loadUnchanged($nid);
    return $node instanceof NodeInterface ? $node : NULL;
  }

}
