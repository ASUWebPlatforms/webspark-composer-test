<?php

namespace Drupal\elevate_experience_import\EventSubscriber;

use Drupal\elevate_experience_import\ExperienceValueNormalizer as N;
use Drupal\feeds\Event\EntityEvent;
use Drupal\feeds\Event\FeedsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Derives field_program and field_for_credit during the Experience imports.
 *
 * These two fields are not present as CSV columns; they are derived from the
 * source feed (certificate vs general) and the credits value, powering the
 * "Program specific options" filter on the Experience Finder.
 */
class ExperiencePresaveSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [FeedsEvents::PROCESS_ENTITY_PRESAVE => 'onPresave'];
  }

  /**
   * Sets the derived program/credit fields before the node is saved.
   */
  public function onPresave(EntityEvent $event) {
    $feed_type = $event->getFeed()->getType()->id();
    if (!in_array($feed_type, ['experience_general', 'experience_certificate'], TRUE)) {
      return;
    }

    $entity = $event->getEntity();
    if (!$entity->hasField('field_for_credit')) {
      return;
    }

    $is_certificate = $feed_type === 'experience_certificate';
    $credits = $entity->hasField('field_credits') ? $entity->get('field_credits')->value : '';
    $entity->set('field_for_credit', $is_certificate || N::indicatesCredit($credits));

    if ($is_certificate && $entity->hasField('field_program') && $entity->get('field_program')->isEmpty()) {
      $entity->set('field_program', [['target_id' => $this->certificateProgramId()]]);
    }
  }

  /**
   * Returns the term id of the certificate program, creating it if needed.
   */
  protected function certificateProgramId() {
    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $existing = $storage->loadByProperties(['vid' => 'program', 'name' => N::CERTIFICATE_PROGRAM]);
    if ($existing) {
      return reset($existing)->id();
    }
    $term = $storage->create(['vid' => 'program', 'name' => N::CERTIFICATE_PROGRAM]);
    $term->save();
    return $term->id();
  }

}
