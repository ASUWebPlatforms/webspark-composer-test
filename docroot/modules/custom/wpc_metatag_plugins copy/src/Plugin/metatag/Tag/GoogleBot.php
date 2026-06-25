<?php

namespace Drupal\wpc_metatag_plugins\Plugin\metatag\Tag;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\metatag\Plugin\metatag\Tag\MetaNameBase;

/**
 * "Googlebot" meta tag.
 *
 * @MetatagTag(
 *   id = "googlebot",
 *   label = @Translation("GoogleBot"),
 *   description = @Translation("Provides only Google with specific directions for what to do when this page is indexed. Potentially useful when you want a site indexed in a non-Google index but not show up in a public Google search, see https://developers.google.com/search/docs/advanced/crawling/block-indexing"),
 *   name = "googlebot",
 *   group = "advanced",
 *   weight = 1,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class GoogleBot extends MetaNameBase {

  use StringTranslationTrait;

  /**
   * Sets the value of this tag.
   *
   * @param string|array $value
   *   The value to set to this tag.
   *   It can be an array if it comes from a form submission or from field
   *   defaults, in which case
   *   we transform it to a comma-separated string.
   */
  public function setValue($value): void {
    if (is_array($value)) {
      $value = array_filter($value);
      $value = implode(', ', array_keys($value));
    }
    $this->value = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $element = []): array {
    // Prepare the default value as it is stored as a string.
    $default_value = [];
    if (!empty($this->value)) {
      $default_value = explode(', ', $this->value);
    }

    $form = [
      '#type' => 'checkboxes',
      '#title' => $this->label(),
      '#description' => $this->description(),
      '#options' => [
        'index' => $this->t('index - Allow Google to index this page (assumed).'),
        'follow' => $this->t('follow - Allow Google to follow links on this page (assumed).'),
        'noindex' => $this->t('noindex - Prevents Google from indexing this page.'),
        'nofollow' => $this->t('nofollow - Prevents Google from following links on this page.'),
        'noarchive' => $this->t('noarchive - Prevents cached copies of this page from appearing in Google search results.'),
        'nosnippet' => $this->t('nosnippet - Prevents descriptions from appearing in Google search results, and prevents page caching.'),
        'noodp' => $this->t('noodp - Blocks the <a href=":opendirectory">Open Directory Project</a> description from appearing in Google search results.', [':opendirectory' => 'http://www.dmoz.org/']),
        'noimageindex' => $this->t('noimageindex - Prevent Google from indexing images on this page.'),
        'notranslate' => $this->t('notranslate - Prevent Google from offering to translate this page in search results.'),
      ],
      'index' => [
        '#states' => [
          'disabled' => [
            [':input[name="googlebot[noindex]"]' => ['checked' => TRUE]],
            'or',
            [':input[name*="[googlebot][noindex]"]' => ['checked' => TRUE]],
          ],
        ],
      ],
      'noindex' => [
        '#states' => [
          'disabled' => [
            [':input[name="googlebot[index]"]' => ['checked' => TRUE]],
            'or',
            [':input[name*="[googlebot][index]"]' => ['checked' => TRUE]],
          ],
        ],
      ],
      'follow' => [
        '#states' => [
          'disabled' => [
            [':input[name="googlebot[nofollow]"]' => ['checked' => TRUE]],
            'or',
            [':input[name*="[googlebot][nofollow]"]' => ['checked' => TRUE]],
          ],
        ],
      ],
      'nofollow' => [
        '#states' => [
          'disabled' => [
            [':input[name="googlebot[follow]"]' => ['checked' => TRUE]],
            'or',
            [':input[name*="[googlebot][follow]"]' => ['checked' => TRUE]],
          ],
        ],
      ],
      '#default_value' => $default_value,
      '#required' => isset($element['#required']) ? $element['#required'] : FALSE,
      '#element_validate' => [[get_class($this), 'validateTag']],
    ];

    return $form;
  }

}
