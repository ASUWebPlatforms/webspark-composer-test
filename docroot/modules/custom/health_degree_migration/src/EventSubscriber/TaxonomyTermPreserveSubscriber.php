<?php

namespace Drupal\health_degree_migration\EventSubscriber;

use Drupal\node\Entity\Node;
use Drupal\migrate\Event\MigratePrepareRowEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TaxonomyTermPreserveSubscriber implements EventSubscriberInterface
{

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents()
  {
    return [
      MigratePrepareRowEvent::class => 'onPrepareRow',
    ];
  }

  /**
   * Prepare the row before it's saved.
   *
   * @param \Drupal\migrate\Event\MigratePrepareRowEvent $event
   *   The event object.
   */
  public function onPrepareRow(MigratePrepareRowEvent $event)
  {
    $row = $event->getRow();
    $migration = $event->getMigration();

    // Apply only to the specified migration ID.
    if ($migration->id() !== 'migrate_degree_to_detail') {
      return;
    }

    // Get the node's existing taxonomy terms.
    $nid = $row->getSourceProperty('nid'); // Adjust based on your source if necessary.
    if ($nid) {
      $node = Node::load($nid);
      if ($node) {
        $existing_term_ids = array_column($node->get('degree_tags')->getValue(), 'target_id');

        // Get degreeType and degreeDescriptionShort from the source.
        $degreeType = $row->getSourceProperty('degreeType');
        $degreeDescriptionShort = $row->getSourceProperty('degreeDescriptionShort');

        // Map new terms based on degreeType and degreeDescriptionShort conditions.
        $new_term_ids = $this->mapDegreeTypeToTaxonomy($degreeType, $degreeDescriptionShort);

        // Merge existing terms with new ones.
        $merged_term_ids = array_unique(array_merge($existing_term_ids, $new_term_ids));

        // Set the merged term IDs back to the row.
        $row->setSourceProperty('degree_tags', $merged_term_ids);
      }
    }
  }

  /**
   * Map degreeType and degreeDescriptionShort values to taxonomy term IDs.
   *
   * @param string|null $degreeType
   *   The degreeType value from the source.
   * @param string|null $degreeDescriptionShort
   *   The degreeDescriptionShort value from the source.
   *
   * @return array
   *   An array of mapped taxonomy term IDs.
   */
  protected function mapDegreeTypeToTaxonomy($degreeType, $degreeDescriptionShort)
  {
    // Check for the CERT/GR condition.
    if ($degreeDescriptionShort === 'CERT' && $degreeType === 'GR') {
      return [704];  // Use TID 704 instead of 702.
    }

    // Default mapping based on degreeType.
    $map = [
      'UG' => 701,
//      'GR' => 702,
      'UGCM' => 706,
    ];

    return isset($map[$degreeType]) ? [$map[$degreeType]] : [];
  }
}
