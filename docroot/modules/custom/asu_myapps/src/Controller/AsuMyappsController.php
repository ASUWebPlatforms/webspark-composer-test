<?php

namespace Drupal\asu_myapps\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AsuMyappsController extends ControllerBase
{
  /**
   * Begin a Software download.
   *
   * This method will first check if the user is authenticated. If so, it will
   * check if the user has access to the software. If the user has access, it
   * will create a signed URL and begin the download. If the user does not have
   * access, it will display an error message.
   *
   * @param NodeInterface $node
   * @return RedirectResponse|array
   */
  public static function download(NodeInterface $node): RedirectResponse|array
  {
    $user = Drupal::currentUser();
    $referer = Drupal::request()->headers->get('referer');
    $response = $referer ? new RedirectResponse($referer) : new RedirectResponse(
      Url::fromRoute('<front>')->toString()
    );

    if ($user->isAuthenticated()) {
      $node = Node::load($node->id());

      if (!empty($node) && $node->bundle() == 'application') {
        $status = false;
        $file = '';

        if (!empty($node->field_file_name->value)) {
          $file = strip_tags($node->field_file_name->value);
        }

        // Check user access with EDNA, return true/false, assign to $status
        // Added the additional string 'en' to the following check because the language is returning English and not Undefined now.
        if ($node->field_edna_group->getLangcode() == ('und'||'en')) {
          $data = AsuMyappsEdnaController::getUserData($user, $node->field_edna_group->getValue());

          try {
            $status = AsuMyappsEdnaController::getUserAccess($data['asurite'], $data['access_groups']);
          } catch (RequestException $e) {
            Drupal::logger('asu_myapps')->error($e->getMessage());
            Drupal::messenger()->addError('There was a request error. Please try again later.');
          }
        }

        // If user has access, create signed URL, begin download, redirect to previous page with message
        if ($status) {
          if (!empty($file)) {
            $signed_url = AsuMyappsAwsController::createSignedUrl($file);

            return [
              '#type' => 'inline_template',
              '#title' => 'Software Download',
              '#context' => ['signed_url' => $signed_url],
              '#template' => '<div class="container"><h1>Software Download</h1><p>Please ensure your browser allows popups from this website.<br>If the download does not start automatically, please click <a href="{{ signed_url|raw }}" target="_blank">here</a>.</p><p><a class="btn btn-maroon" href="/my-apps-search">Find more software</a></p></div><script>window.open("{{ signed_url|raw }}", "_blank")</script>',
            ];
          } else {
            Drupal::messenger()->addError('This download is currently unavailable. Please try again later.');
          }
        } else {
          Drupal::messenger()->addError(
            'The download you have selected is restricted use and is not available to all University affiliations. If you believe this is in error, please contact the ASU Experience Center 24/7 at +1-855-278-5080 or by live-chat from your My ASU "Service" tab.'
          );
        }
      } else {
        Drupal::messenger()->addError('Please choose a valid software option.');
      }
    } else {
      Drupal::messenger()->addError('You must be logged in to download this software.');
    }

    return $response;
  }

  /**
   * Module settings page.
   *
   * As secrets are now managed via the Secrets Manager Plugin, this method is no longer
   * used. It is left here for reference.
   *
   * @return array
   */
  public function settings(): array
  {
    return Drupal::formBuilder()->getForm('Drupal\asu_myapps\Form\AsuMyappsSettingsForm');
  }
}
