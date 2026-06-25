<?php

namespace Drupal\unibuddy_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Unibuddy Popcard' block.
 *
 * @Block(
 *   id = "unibuddy_block",
 *   admin_label = @Translation("Unibuddy popcard"),
 *   category = @Translation("Custom")
 * )
 */
class UnibuddyBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'text' => '',
      'uni_id' => 'global-launch',
      'colour' => '8C1D40',
      'domain' => 'https://popcard.unibuddy.co/',
      'title' => 'Unibuddy Popcard',
      'align' => 'right',
      'ubLang' => 'en-US',
      'ubCookieConsent' => 'necessary',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    // Collect settings from block configuration (merge with defaults).
    $config = $this->configuration + $this->defaultConfiguration();

    // Prepare settings for the inline window.unibuddySettings script.
    $settings = [
      'uni_id' => $config['uni_id'],
      'colour' => $config['colour'],
      'domain' => $config['domain'],
      'title' => $config['title'],
      'align' => $config['align'],
      'ubLang' => $config['ubLang'],
      'ubCookieConsent' => $config['ubCookieConsent'],
      // include the configurable text field:
      'text' => $config['text'],
    ];

    // Inline JS using json_encode for safe output.
    $inline_js = 'window.unibuddySettings = ' . json_encode($settings) . ';';


    // Attach the external Unibuddy Popcard library declared in the module libraries.yml.
    $build['#attached']['library'][] = 'unibuddy_block/unibuddy-popcard';

    // Render settings + placeholder in the block output (footer region).
    $safe_json = json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $build['content'] = [
      '#type' => 'markup',
      '#markup' => implode("\n", [
        '<script type="text/javascript">',
        '  // Unibuddy settings rendered in footer',
        '  window.unibuddySettings = ' . $safe_json . ';',
        '</script>',
        '<div class="unibuddy-popcard-placeholder" aria-hidden="true"></div>',
      ]),
      '#allowed_tags' => ['script', 'div'],
    ];


    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->configuration + $this->defaultConfiguration();

    $form['text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Text'),
      '#description' => $this->t('Optional text value that will be passed to window.unibuddySettings.text and can be used by the Unibuddy script.'),
      '#default_value' => $config['text'],
      '#rows' => 3,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['text'] = $form_state->getValue('text');
    // Save any other configurable values you add in blockForm().
  }

}
