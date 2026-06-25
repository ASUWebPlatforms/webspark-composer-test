<?php
namespace Drupal\wpc_metatag_plugins\MyEventSubscriber;
/**
 * @file
 * Contains \Drupal\wpc_metatag_plugins\EventSubscriber\MyEventSubscriber
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * MyEventSubscriber
 */

class MyEventSubscriber implements EventSubscriberInterface {

  /**
   * Code that should be triggered on event specified 
   */
  public function onRespond(FilterResponseEvent $event) {
    // The RESPONSE event occurs once a response was created for replying to a request.
    // For example you could override or add extra HTTP headers in here
    $response = $event->getResponse();
    $response->headers->set('X-Robot-Tag', 'nofollow');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // For this example I am using KernelEvents constants (see below a full list).
    $events[KernelEvents::RESPONSE][] = ['onRespond'];
    return $events;
  }


}