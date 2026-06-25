<?php

namespace Drupal\asu_quikpay\Controller;

use Drupal\commerce_cart\CartSession;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles the QuikPay receipt redirect (browser GET back from Nelnet).
 *
 * RTPN (Quikpay::onNotify) remains the safety net for the cases where this
 * redirect never happens at all (closed window, network drop, etc.).
 */
class AsuQuikpayOffSite extends ControllerBase {

  /**
   * Controller callback for /commerce_nelnet/rtpn.
   */
  public function content(Request $request) {
    $logger = $this->getLogger('asu_quikpay');
    $params = $request->query->all();

    // 1. Load the order referenced by Nelnet.
    $order_id = $params['orderNumber'] ?? NULL;
    $order = ($order_id && is_numeric($order_id))
      ? $this->entityTypeManager()->getStorage('commerce_order')->load($order_id)
      : NULL;
    if (!$order instanceof OrderInterface) {
      $logger->error('QuikPay redirect received for unknown orderNumber @order.', ['@order' => $order_id ?? 'NULL']);
      return $this->errorPage($this->t('We could not locate your order. If you completed a payment, please contact support with your receipt.'));
    }

    // 2. Resolve the gateway plugin FROM THE ORDER (no hardcoded machine
    // name) so hash keys and configuration always match the gateway that
    // actually sent the user to Nelnet.
    /** @var \Drupal\commerce_payment\Entity\PaymentGatewayInterface|null $gateway */
    $gateway = $order->get('payment_gateway')->entity;
    /** @var \Drupal\asu_quikpay\Plugin\Commerce\PaymentGateway\Quikpay|null $plugin */
    $plugin = $gateway ? $gateway->getPlugin() : NULL;
    if (!$plugin || !method_exists($plugin, 'validateRedirectHash')) {
      $logger->error('QuikPay redirect for order @order: order has no QuikPay payment gateway.', ['@order' => $order->id()]);
      return $this->errorPage($this->t('Your payment could not be verified. Please contact support.'));
    }

    // Legacy fallback: this site has not opted into the new flow, so reproduce
    // the develop receipt-redirect behavior exactly.
    if (method_exists($plugin, 'isNewFlowEnabled') && !$plugin->isNewFlowEnabled()) {
      return $this->contentLegacy($request, $order, $plugin);
    }

    // 3. Authenticate the request: hash + timestamp freshness.
    if (!$plugin->validateRedirectHash($params)) {
      $logger->warning('QuikPay redirect hash validation FAILED for order @order. Query: @query', [
        '@order' => $order->id(),
        '@query' => json_encode(array_keys($params)),
      ]);
      return $this->errorPage($this->t('Your payment could not be verified. If you were charged, please contact support.'));
    }
    if (!$plugin->validateTimestamp($params['timestamp'] ?? '')) {
      $logger->warning('QuikPay redirect timestamp outside the 5 minute window for order @order.', ['@order' => $order->id()]);
      // The link is stale (e.g. user refreshed an old receipt URL). If the
      // order is already completed just send them along; otherwise error.
      if ($order->getState()->getId() !== 'draft') {
        return $this->redirectToCheckoutReturn($order, $request);
      }
      return $this->errorPage($this->t('This payment link has expired. If you were charged, your order will complete automatically; please check your email.'));
    }

    // 4. Evaluate the transaction result.
    $type = (string) ($params['transactionType'] ?? '');
    $status = (string) ($params['transactionStatus'] ?? '');
    if (!$plugin->isSuccessfulTransaction($type, $status)) {
      $logger->notice('QuikPay reported an unsuccessful transaction for order @order (type @type, status @status, message: @message).', [
        '@order' => $order->id(),
        '@type' => $type,
        '@status' => $status,
        '@message' => $params['transactionResultMessage'] ?? '',
      ]);
      // Send the user back into checkout so they can retry, instead of
      // leaving them stranded on a blank "Validating Payment" page.
      if ($this->userCanAccessCheckout($order)) {
        $this->messenger()->addError($this->t('Your payment was not completed. Please try again or use a different payment method.'));
        return new RedirectResponse(Url::fromRoute('commerce_checkout.form', ['commerce_order' => $order->id()])->toString());
      }
      return $this->errorPage($this->t('Your payment was not completed. Please return to the site and try again.'));
    }

    if (!$plugin->validateAmount($order, $params)) {
      $logger->error('QuikPay redirect amount mismatch for order @order: transactionTotalAmount=@paid orderFee=@fee vs order total @total.', [
        '@order' => $order->id(),
        '@paid' => $params['transactionTotalAmount'] ?? 'NULL',
        '@fee' => $params['orderFee'] ?? '0',
        '@total' => $order->getTotalPrice()->getNumber(),
      ]);
      return $this->errorPage($this->t('There was a problem verifying your payment amount. Please contact support.'));
    }

    // 5. Complete the order server-side, regardless of session state. This
    // is idempotent, so it coexists safely with RTPN.
    $plugin->processSuccessfulTransaction($order, $params);

    // 6. Hand the user back to the normal Commerce flow when possible.
    if ($this->userCanAccessCheckout($order)) {
      return $this->redirectToCheckoutReturn($order, $request);
    }

    // 7. Session-independent fallback receipt. No order details are exposed
    // because we cannot verify this browser belongs to the order owner.
    $logger->notice('QuikPay redirect for order @order completed without a valid user session; fallback receipt page shown.', ['@order' => $order->id()]);
    $config = $plugin->getConfiguration();
    return [
      '#title' => $this->t('Payment received'),
      '#markup' => '<p>' . nl2br(htmlspecialchars($config['quikpay_success_text'] ?? '', ENT_QUOTES)) . '</p>'
      . '<p>' . $this->t('A receipt has been sent to your email address.') . '</p>',
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Redirects the user into the Commerce checkout return route.
   *
   * The full Nelnet query string is forwarded so Quikpay::onReturn() can
   * re-validate independently if it still needs to act. Since the order is
   * normally already placed at this point, Commerce will simply route the
   * user to the checkout completion page.
   */
  protected function redirectToCheckoutReturn(OrderInterface $order, Request $request): RedirectResponse {
    $url = Url::fromRoute('commerce_payment.checkout.return', [
      'commerce_order' => $order->id(),
      'step' => 'payment',
    ], [
      'query' => $request->query->all(),
    ]);
    return new RedirectResponse($url->toString());
  }

  /**
   * Checks whether the current browser session may access this checkout.
   *
   * Mirrors the access logic Commerce applies on checkout routes: order
   * ownership for authenticated users, cart session (active or completed)
   * for anonymous users.
   */
  protected function userCanAccessCheckout(OrderInterface $order): bool {
    $current_user = $this->currentUser();
    if ($current_user->isAuthenticated()) {
      return (int) $order->getCustomerId() === (int) $current_user->id();
    }
    /** @var \Drupal\commerce_cart\CartSessionInterface $cart_session */
    $cart_session = \Drupal::service('commerce_cart.cart_session');
    return $cart_session->hasCartId($order->id(), CartSession::ACTIVE)
      || $cart_session->hasCartId($order->id(), CartSession::COMPLETED);
  }

  /**
   * Legacy receipt-redirect handler, preserved from the develop version.
   *
   * Validates the Nelnet hash with the pass-through key, and on a successful
   * payment redirects into Commerce's checkout return route with the internal
   * "paid" digest that Quikpay::onReturnLegacy() re-validates. Behavior is kept
   * byte-for-byte compatible with develop so disabling the new flow is a true
   * rollback.
   *
   * @deprecated Remove once the new flow is fully rolled out to all sites.
   */
  protected function contentLegacy(Request $request, OrderInterface $order, $plugin) {
    $config = $plugin->getConfiguration();
    $key = $config['mode'] === 'live' ? $config['quikpay_prod_pt_key'] : $config['quikpay_test_pt_key'];

    // The variables we sent in redirectUrlParameters to Quikpay.
    $red_variables = "transactionType,transactionStatus,transactionId,originalTransactionId,transactionTotalAmount,transactionDate,transactionAcountType,transactionEffectiveDate,transactionDescription,transactionResultDate,transactionResultEffectiveDate,transactionResultCode,transactionResultMessage,orderNumber,orderType,orderName,orderDescription,orderAmount,orderFee,orderAmountDue,orderDueDate,orderBalance,orderCurrentStatusBalance,orderCurrentStatusAmountDue";
    $hash_string = "";
    foreach (explode(',', $red_variables) as $k) {
      $hash_string .= $request->query->get($k);
    }
    $incoming_timestamp = $request->query->get('timestamp');
    $hash_string .= $incoming_timestamp;
    $hash_string .= $key;
    $hash = hash('SHA256', $hash_string);

    // We make sure the hash is the same we generate.
    if ($request->query->get('hash') === $hash) {
      $transaction_status = $request->query->get('transactionStatus');
      $transaction_type = $request->query->get('transactionType');
      $success_codes = $transaction_type === "3" ? ["5", "6", "8"] : $transaction_status;

      $successful_payment = FALSE;
      if (($transaction_type === "3" && in_array($transaction_status, $success_codes)) || (in_array($transaction_type, ["1", "2"]) && $success_codes === "1")) {
        $order_total_amount = floatval($order->getTotalPrice()->getNumber());
        $total_amount_paid = ($request->query->get('transactionTotalAmount') / 100);
        if ($total_amount_paid == $order_total_amount) {
          $successful_payment = TRUE;
        }
        else {
          throw new PaymentGatewayException('Charged amount not equal to order amount.');
        }
      }

      if ($successful_payment) {
        $order_id = $order->id();
        $successful_string = '';
        $successful_string .= $order_id;
        $successful_string .= $successful_payment;
        $successful_value = hash('SHA256', $successful_string . $key);
        // If the payment was successful we redirect to the complete order.
        $url = Url::fromUri('internal:/checkout/' . $order_id . '/payment/return');
        $url->setOptions([
          'query' => [
            'paid' => $successful_value,
            'success' => $successful_payment,
          ],
        ]);
        return new RedirectResponse($url->toString());
      }
    }

    return [
      '#markup' => $this->t('Validating Payment.'),
    ];
  }

  /**
   * Builds a simple, cache-disabled error page render array.
   */
  protected function errorPage($message): array {
    return [
      '#title' => $this->t('Payment verification'),
      '#markup' => '<p>' . $message . '</p>',
      '#cache' => ['max-age' => 0],
    ];
  }

}
