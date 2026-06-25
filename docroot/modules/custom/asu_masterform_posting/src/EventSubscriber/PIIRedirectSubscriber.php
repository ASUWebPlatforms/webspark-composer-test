<?php

namespace Drupal\asu_masterform_posting\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Drupal\Component\Utility\Crypt;

/**
 * Captures PII query params once, stores them in session, and redirects
 * to a clean URL without PII.
 */
class PIIRedirectSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onKernelRequest', 0],
    ];
  }

  /**
   * Handles the request and strips PII from the query string.
   */
  public function onKernelRequest(RequestEvent $event) {
    // Only act on the main/master request.
    if (!$event->isMainRequest()) {
      return;
    }

    $request = $event->getRequest();

    // Only care about GET requests.
    if ($request->getMethod() !== 'GET') {
      return;
    }

    $path = $request->getPathInfo();

		 // Load allowed paths from config.
		 $allowed_paths = \Drupal::config('asu_masterform_posting.settings')->get('allowed_paths') ?? [];

    // Fallback defaults if config is empty.
    if (empty($allowed_paths)) {
      $allowed_paths = [
        '/road-to-asu',
        '/masterform-registration',
        '/BEET-event',
        '/beet-registration',
      ];
    }

    if (!in_array($path, $allowed_paths, TRUE)) {
      return;
    }

    $query = $request->query->all();
    // \Drupal::logger('cstest')->notice('query:<pre>' . print_r($query, TRUE) . '</pre>');


    // If token is already present, do nothing.
    if (!empty($query['token'])) {
      return;
    }

    // PII keys we want to capture & strip.
    $pii_keys = [
      'fname',
      'lname',
      'email',
      'zip',
      'asurite',
      'phone',
    ];

    $pii = [];
    foreach ($pii_keys as $key) {
      if (isset($query[$key]) && $query[$key] !== '') {
        $pii[$key] = $query[$key];
      }
    }

    // If there is no PII in the query string, nothing to do.
    if (empty($pii)) {
      return;
    }

    // Normalize phone only when it exists
    if (!empty($pii['phone'])) {
      $phone = trim($pii['phone']);

      // If it's digits only and doesn't start with +, prefix +
      if ($phone !== '' && $phone[0] !== '+') {
        $phone = '+' . $phone;
      }

      $pii['phone'] = $phone;
    }

    // Generate a token and store PII server-side in cache (anonymous-safe)
    $token = Crypt::randomBytesBase64(24);

    // Store for 15 minutes.
    $cid = 'asu_masterform_posting_pii:' . $token;
    \Drupal::cache('data')->set($cid, $pii, time() + 900);


    // Build a clean query string with token without the PII.
    foreach ($pii_keys as $key) {
      unset($query[$key]);
    }
		$query['token'] = $token;

    // If query now empty, no "?".
    $clean_url = $request->getSchemeAndHttpHost() . $path;

    if (!empty($query)) {
      $clean_url .= '?' . http_build_query($query);
    }

    // Redirect once to the clean URL.
    $response = new RedirectResponse($clean_url, 302);
    $event->setResponse($response);
  }
}