<?php
namespace Drupal\eventticker\EventSubscriber;

use Drupal\feeds\Event\EntityEvent;
use Drupal\feeds\Event\FeedsEvents;
use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Datetime\DrupalDateTime;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Reacts on feed imports.
 */
class FeedsSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      FeedsEvents::PROCESS_ENTITY_PRESAVE => ['preSave'],
    ];
  }

  /**
   * Acts on presaving an entity.
   */
  public function preSave(EntityEvent $event) {
    $feed_type_id = $event->getFeed()->getType()->id();
    if ($feed_type_id == 'sun_devil_athletics_event_feed') {
      // Do not save the entity if it's passed, or 20 days in the future
      $today = new DateTimePlus('now');
      $start = new DateTimePlus($event->getEntity()->field_event_date_and_time->end_value);
      $diff = $today->diff($start);
      if ($diff->days > 10 || $diff->invert == 1 ) {
        throw new EmptyFeedException();
      }
    }
  }

}
