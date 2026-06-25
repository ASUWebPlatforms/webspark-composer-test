<?php

namespace Drupal\elevate_experience_import\Plugin\views\filter;

use Drupal\elevate_experience_import\ExperienceValueNormalizer as N;
use Drupal\views\Attribute\ViewsFilter;
use Drupal\views\Plugin\views\filter\InOperator;

/**
 * "Program specific options" filter for the Experience Finder.
 *
 * A single exposed control offering: Work+Life Design Certificate courses,
 * all experiences for credit, and non-credit experiences. Each option resolves
 * to a different underlying condition (field_program / field_for_credit), so it
 * cannot be a plain field filter. Matching node ids are resolved with an entity
 * query and applied as an IN condition on the view.
 */
#[ViewsFilter("experience_program_specific_options")]
class ProgramSpecificOptions extends InOperator {

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    if (!isset($this->valueOptions)) {
      $this->valueOptions = [
        'certificate' => $this->t('Work+Life Design Certificate courses'),
        'for_credit' => $this->t('All experiences for credit'),
        'non_credit' => $this->t('Non-credit experiences'),
      ];
    }
    return $this->valueOptions;
  }

  /**
   * {@inheritdoc}
   */
  public function acceptExposedInput($input) {
    $identifier = $this->options['expose']['identifier'] ?? '';
    if ($identifier !== '' && !isset($input[$identifier])) {
      return FALSE;
    }
    return parent::acceptExposedInput($input);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $values = array_filter((array) $this->value, function ($v) {
      return $v !== '' && $v !== 'All';
    });
    if (empty($values)) {
      return;
    }

    $nids = [];
    foreach ($values as $value) {
      $query = \Drupal::entityQuery('node')
        ->condition('type', 'experience')
        ->accessCheck(FALSE);
      switch ($value) {
        case 'certificate':
          $query->condition('field_program.entity.name', N::CERTIFICATE_PROGRAM);
          break;

        case 'for_credit':
          $query->condition('field_for_credit', 1);
          break;

        case 'non_credit':
          $query->condition('field_for_credit', 0);
          break;

        default:
          continue 2;
      }
      $nids += $query->execute();
    }

    // Force an empty result set when selections match nothing.
    $nids = $nids ?: [0];
    $this->query->addWhere($this->options['group'], 'node_field_data.nid', $nids, 'IN');
  }

}
