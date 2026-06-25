<?php

namespace Drupal\wpc_rfi_forms\EventSubscriber;

use Drupal\sfweb2lead_webform\Event\Sfweb2leadWebformEvent;
use Drupal\wpc_rfi_forms\Plugin\WpcFormsWebFormHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SalesforceSubscriber
 *
 */

class SalesforceSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    $events[Sfweb2leadWebformEvent::SUBMIT][] = ['passUpdates',];
    return $events;
  }

  public function passUpdates(Sfweb2leadWebformEvent $event) {


    $logger = \Drupal::logger('webform custom rfi');
    $logger->warning('Form data updated and resent to SF Module');

    $salesforce_data = $event->getData();
    $webform = $event->getHandler()->getWebform();
    $webform_submission = $event->getSubmission();

    foreach ($salesforce_data as $key => $value) {
      $salesforce_data[$key] = $value;
    }
    $event->setData($salesforce_data);
  }

}


