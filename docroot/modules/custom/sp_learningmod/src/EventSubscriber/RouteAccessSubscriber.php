<?php

namespace Drupal\sp_learningmod\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountProxyInterface;

class RouteAccessSubscriber implements EventSubscriberInterface
{
  protected $database;
  protected $currentUser;
  protected $messenger;

  public function __construct(Connection $database, AccountProxyInterface $currentUser, MessengerInterface $messenger)
  {
    $this->database = $database;
    $this->currentUser = $currentUser;
    $this->messenger = $messenger;
  }

  public function checkUserAccess(RequestEvent $event)
  {
    $request = $event->getRequest();
    $path = $request->getPathInfo();
    $uid = $this->currentUser->id();

    if (in_array($path, ['/learning/prostitution/plan/buildmyplan', '/learning/prostitution/plan/reviewmyplan'])) {
      $query = $this->database->select('sp_learningmod_council_progress', 'p')
        ->fields('p', ['passed'])
        ->condition('p.uid', $uid)
        ->execute()
        ->fetchField();

      if (!$query) {
        $this->messenger->addError('You must pass the City Council Meeting before proceeding.');
        $response = new RedirectResponse(Url::fromUri('internal:/learning/prostitution/plan/city-council-meeting')->toString());
        $event->setResponse($response);
        return;
      }
    }

    $allowed_roles = ['administrator', 'sp_learningmod_user'];
    $user_roles = $this->currentUser->getRoles();

    if (!empty(array_intersect($allowed_roles, $user_roles))) {
      return;
    }

    if (strpos($path, '/learning/prostitution/') === 0) {
      $this->messenger->addError('You do not have permission to access this section.');
      $response = new RedirectResponse(Url::fromUri('internal:/')->toString());
      $event->setResponse($response);
      return;
    }
  }

  public static function getSubscribedEvents()
  {
    return [
      KernelEvents::REQUEST => ['checkUserAccess', 40],
    ];
  }
}
