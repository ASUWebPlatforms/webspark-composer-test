<?php
namespace Drupal\asuaec_visit_revamp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class EmailResendController extends ControllerBase {

  public function resendEmail(NodeInterface $node, Request $request) {
    if ($node->bundle() === 'attendee_conf_email') {
      _asuaec_visit_revamp_send_confirmation_email($node);
      $this->messenger()->addMessage('Confirmation email resent.');
    }
    // Redirect back to previous page or node list
    $destination = $request->query->get('destination') ?? '/resend-confirmation-email';

    return new RedirectResponse($destination);
  }

}
