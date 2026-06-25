<?php

namespace Drupal\asu_newcollege_custom_tokens\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Render\Markup;

/**
 * Defines the Custom Token config entity.
 *
 * @ConfigEntityType(
 *   id = "asu_newcollege_custom_token",
 *   label = @Translation("Custom Token"),
 *   handlers = {
 *     "list_builder" = "Drupal\asu_newcollege_custom_tokens\CustomTokenListBuilder",
 *     "form" = {
 *       "add" = "Drupal\asu_newcollege_custom_tokens\Form\CustomTokenForm",
 *       "edit" = "Drupal\asu_newcollege_custom_tokens\Form\CustomTokenForm",
 *       "delete" = "Drupal\asu_newcollege_custom_tokens\Form\CustomTokenDeleteForm"
 *     }
 *   },
 *   config_prefix = "token",
 *   admin_permission = "administer asu newcollege custom tokens",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/asu-newcollege/custom-tokens/{asu_newcollege_custom_token}/edit",
 *     "delete-form" = "/admin/config/asu-newcollege/custom-tokens/{asu_newcollege_custom_token}/delete",
 *     "collection" = "/admin/config/asu-newcollege/custom-tokens"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "type",
 *     "text_value",
 *     "date_value",
 *     "date_format",
 *     "html_value"
 *   }
 * )
 */
class CustomToken extends ConfigEntityBase {

  /**
   * The token machine name.
   *
   * @var string
   */
  protected $id;

  /**
   * The token human-readable label.
   *
   * @var string
   */
  protected $label;

  /**
   * A short description of what this token represents.
   *
   * @var string
   */
  protected $description = '';

  /**
   * Token type: 'text', 'date', or 'html'.
   *
   * @var string
   */
  protected $type = 'text';

  /**
   * The text value (used when type is 'text').
   *
   * @var string
   */
  protected $text_value = '';

  /**
   * The date value stored as YYYY-MM-DD (used when type is 'date').
   *
   * @var string
   */
  protected $date_value = '';

  /**
   * PHP date format string, e.g. 'F j, Y' or 'd/m/Y' (used when type is 'date').
   *
   * @var string
   */
  protected $date_format = 'F j, Y';

  /**
   * The raw HTML value (used when type is 'html').
   *
   * @var string
   */
  protected $html_value = '';

  /**
   * Returns the resolved token value ready to be rendered.
   *
   * For HTML tokens this returns a Markup object so that the Token API
   * does not escape the markup on replacement.
   *
   * @return string|\Drupal\Core\Render\Markup
   *   The rendered token value.
   */
  public function getResolvedValue(): string|Markup {
    if ($this->type === 'date') {
      if (!empty($this->date_value)) {
        // Parse the stored date and format it with the user-defined PHP format.
        $timestamp = strtotime($this->date_value);
        if ($timestamp !== FALSE) {
          return date($this->date_format, $timestamp);
        }
      }
      return '';
    }

    if ($this->type === 'html') {
      return Markup::create($this->html_value ?? '');
    }

    // Default: plain text value.
    return $this->text_value ?? '';
  }

}
