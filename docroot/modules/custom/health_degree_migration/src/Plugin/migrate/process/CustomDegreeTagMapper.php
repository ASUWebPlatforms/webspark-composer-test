<?php

namespace Drupal\health_degree_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\ProcessPluginBase;
use Drupal\Core\Database\Database;
use Drupal\node\Entity\Node;

/**
 * Provides a 'CustomDegreeTagMapper' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *   id = "custom_degree_tag_mapper"
 * )
 */
class CustomDegreeTagMapper extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    drush_print('prepareRow is called for NID: ' . $row->getSourceProperty('nid'));

    // Fetch existing taxonomy terms from the node.
    $result = Database::getConnection()->query(
      'SELECT GROUP_CONCAT(field_degree_tags.target_id) as tids
       FROM {field_data_field_degree_tags} field_degree_tags
       WHERE field_degree_tags.entity_id = :nid',
      [':nid' => $row->getSourceProperty('nid')]
    )->fetchField();

    if (!is_null($result)) {
      $existing_terms = explode(',', $result);
      $row->setSourceProperty('existing_terms', $existing_terms);

      \Drupal::logger('custom_degree_tag_mapper')
        ->info('Node ID: ' . $row->getSourceProperty('nid') .
          ' - Existing TIDs: <pre>' . print_r($existing_terms, TRUE) . '</pre>');
    }

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $degreeType = strtoupper(trim($row->getSourceProperty('degreeType')));
    $degreeDescriptionShort = $row->getSourceProperty('degreeDescriptionShort');
    if ($degreeDescriptionShort !== null) {
      $degreeDescriptionShort = strtoupper(trim($degreeDescriptionShort));
    }
    $acadPlanType = strtoupper(trim($row->getSourceProperty('acadPlanType')));
    $stemOptText = $row->getSourceProperty('stemOptText');
    $existingTerms = $row->getSourceProperty('existing_terms') ?? [];
    $planKeywords = $row->getSourceProperty('planKeywords');
    $keywordOneYear = "ASU Health - 1 year";
    $keywordClinical = "ASU Health - Clinical";
    $newTermIds = [];

    // map presence of plankeyword to term id
    if (in_array($keywordOneYear, $planKeywords)) {
      $newTermIds[] = 760;
    }
    if (in_array($keywordClinical, $planKeywords)) {
      $newTermIds[] = 761;
    }

    // Map based on degree type and description.
    if ($degreeDescriptionShort === 'CERT') {
      if ($degreeType === 'GR' || $degreeType === 'UGCM') {
        $newTermIds = [704];
      }
      if ($degreeType === 'UGCM') {
        $newTermIds = [706];
      }
    } elseif ($degreeType === 'UG') {
      $newTermIds = [701];
    }

    // Additional mappings.
    if ($acadPlanType === 'MIN') {
      $newTermIds[] = 705;
    }

    if (in_array($degreeDescriptionShort, ['PHD', 'DNP', 'DPP', 'DBH', 'AUD', 'MD'])) {
      $newTermIds[] = 703;
    }

    if (in_array($degreeDescriptionShort, ['MS', 'MA', 'MAS', 'MGM', 'MIHM', 'LL.M.', 'MC', 'MHSM', 'MHI', 'MSW', 'MLS'])) {
      $newTermIds[] = 702;
    }

    if (!empty($stemOptText)) {
      $newTermIds[] = 707;
    }

    if (empty($campusesOffered) && $degreeDescriptionShort === 'CERT' && !empty($asuOnlineAcadPlanUrl)) {
      $newTermIds[] = 751;
    } elseif (!empty($campusesOffered)) {
      foreach ($campusesOffered as $campus) {
        $campusCode = strtoupper(trim($campus['campusCode']));

        if ($campusCode === 'SYNC') {
          $newTermIds[] = 750;
        } elseif ($campusCode === 'ONLNE') {
          $newTermIds[] = 751;
        } else {
          $newTermIds[] = 749;
        }
      }
    }

    // Merge new term IDs with existing terms.
    $mergedTermIds = array_unique(array_merge($existingTerms, $newTermIds));

    // Validate the term IDs by loading valid terms from storage.
    $validTerms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($mergedTermIds);
    $validTermIds = array_keys($validTerms);

    // Log the final term mapping for debugging.
    \Drupal::logger('custom_degree_tag_mapper')
      ->info('Plan Keywords: ' . implode(', ', $planKeywords));

    // Return valid term IDs formatted for entity reference fields.
    return array_map(function ($tid) {
      return ['target_id' => $tid];
    }, $validTermIds);
  }
}
