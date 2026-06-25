<?php

namespace Drupal\uoeee_rest\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Yaml\Yaml;

final class DbApiSettingsForm extends ConfigFormBase {

  public function getFormId(): string {
    return 'uoeee_rest_db_api_settings';
  }

  protected function getEditableConfigNames(): array {
    return ['uoeee_rest.uoeee_db_api'];
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $cfg = $this->config('uoeee_rest.uoeee_db_api');
    $defaults = $cfg->get('defaults') ?? ['columns' => '*', 'writable' => '*', 'deny_writable' => []];
    $tables   = $cfg->get('tables')   ?? [];
    $patterns = $cfg->get('patterns') ?? [];

    $form['help'] = [
      '#markup' => '<p>Configure which DB tables the REST resource exposes. ' .
        '<strong>defaults</strong> apply to all entries unless overridden. ' .
        '<strong>tables</strong> are exact table names. ' .
        '<strong>patterns</strong> use fnmatch() (e.g. <code>CE_%</code>).</p>',
    ];

    $form['defaults_yaml'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Defaults (YAML)'),
      '#default_value' => Yaml::dump($defaults, 4, 2),
      '#description' => $this->t("Keys: columns ('*' or list), writable ('*' or list), deny_writable (list)."),
      '#rows' => 6,
    ];

    $form['tables_yaml'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Tables (YAML)'),
      '#default_value' => Yaml::dump($tables, 6, 2),
      '#description' => $this->t('Map of table => settings. Example: <code>CourseEvalSchedule: {}</code>'),
      '#rows' => 12,
    ];

    $form['patterns_yaml'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Patterns (YAML)'),
      '#default_value' => Yaml::dump($patterns, 6, 2),
      '#description' => $this->t('fnmatch() patterns. Example: <code>"CE_%": { deny_writable: ["modifiedby","modifieddate"] }</code>'),
      '#rows' => 8,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    foreach (['defaults_yaml','tables_yaml','patterns_yaml'] as $key) {
      try {
        // Empty textareas should map to empty arrays.
        $text = trim((string) $form_state->getValue($key));
        if ($text === '') {
          $form_state->setValue($key, []);
          continue;
        }
        $parsed = Yaml::parse($text);
        if (!is_array($parsed)) {
          throw new \RuntimeException('YAML must parse to a mapping/sequence.');
        }
        $form_state->setValue($key, $parsed);
      }
      catch (\Throwable $e) {
        $form_state->setErrorByName($key, $this->t('Invalid YAML: @m', ['@m' => $e->getMessage()]));
      }
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->configFactory->getEditable('uoeee_rest.uoeee_db_api')
      ->set('defaults', $form_state->getValue('defaults_yaml'))
      ->set('tables',   $form_state->getValue('tables_yaml'))
      ->set('patterns', $form_state->getValue('patterns_yaml'))
      ->save();
    parent::submitForm($form, $form_state);
    $this->messenger()->addStatus($this->t('DB API settings saved.'));
  }
}
