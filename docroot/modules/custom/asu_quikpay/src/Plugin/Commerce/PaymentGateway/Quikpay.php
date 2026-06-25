<?php

namespace Drupal\asu_quikpay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_price\Price;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Attribute\CommercePaymentGateway as CommercePaymentGatewayAttribute;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides the QuikPay (Nelnet) offsite payment gateway.
 *
 * Payment completion is handled through two complementary channels:
 * - Receipt redirect (browser GET back to /commerce_nelnet/rtpn), handled by
 *   AsuQuikpayOffSite controller. Best-effort, depends on the user's browser.
 * - Real Time Payment Notification (RTPN), a server-to-server POST from
 *   Nelnet handled by ::onNotify() on the route Commerce provides at
 *   /payment/notify/{gateway_machine_name}. This is the reliable channel.
 *
 * Both channels funnel into ::processSuccessfulTransaction(), which is
 * idempotent: whichever arrives first completes the order, the other one
 * becomes a no-op.
 *
 * The plugin is dual-decorated for cross-version compatibility: the Doctrine
 * annotation below is read by Commerce 2.x (Drupal 9/10), while the PHP
 * attribute on the class is read by Commerce 3.x (Drupal 10.3+/11), which
 * converted plugin discovery to attributes. Keep BOTH definitions in sync.
 * Do not mention annotation names prefixed with the at-sign in this prose:
 * Doctrine attempts to parse them and discovery breaks.
 *
 * @CommercePaymentGateway(
 *  id = "quikpay_redirect_checkout",
 *  label = @Translation("Redirect to QuikPay (Nelnet)."),
 *  display_label = @Translation("QuikPay Secure Payment Server"),
 *  forms = {
 *    "offsite-payment" = "Drupal\asu_quikpay\PluginForm\QuikpayRedirectForm",
 *  },
 *  payment_method_types = {"credit_card"},
 *  credit_card_types = {"mastercard", "visa",},
 * )
 */
#[CommercePaymentGatewayAttribute(
  id: 'quikpay_redirect_checkout',
  label: new TranslatableMarkup('Redirect to QuikPay (Nelnet).'),
  display_label: new TranslatableMarkup('QuikPay Secure Payment Server'),
  forms: [
    'offsite-payment' => 'Drupal\asu_quikpay\PluginForm\QuikpayRedirectForm',
  ],
  payment_method_types: ['credit_card'],
  credit_card_types: ['mastercard', 'visa'],
)]
class Quikpay extends OffsitePaymentGatewayBase {

  /**
   * Max allowed clock difference for incoming timestamps (5 min, in ms).
   *
   * Per Nelnet docs: "The institution should disregard any requests with
   * timestamps older than 5 minutes."
   */
  const TIMESTAMP_TOLERANCE_MS = 300000;

  /**
   * Parameters requested via redirectUrlParameters, in request order.
   *
   * The receipt-redirect hash is the concatenation of these values, in this
   * exact order, followed by the timestamp and the shared secret. This list
   * MUST stay in sync between the outgoing request (QuikpayRedirectForm) and
   * the incoming validation (AsuQuikpayOffSite controller).
   *
   * Note: 'transactionAcountType' (sic) is Nelnet's actual parameter name in
   * the receipt redirect spec. Do not "fix" the spelling.
   */
  const REDIRECT_RETURN_PARAMS = [
    'transactionType',
    'transactionStatus',
    'transactionId',
    'originalTransactionId',
    'transactionTotalAmount',
    'transactionDate',
    'transactionAcountType',
    'transactionEffectiveDate',
    'transactionDescription',
    'transactionResultDate',
    'transactionResultEffectiveDate',
    'transactionResultCode',
    'transactionResultMessage',
    'orderNumber',
    'orderType',
    'orderName',
    'orderDescription',
    'orderAmount',
    'orderFee',
    'orderAmountDue',
    'orderDueDate',
    'orderBalance',
    'orderCurrentStatusBalance',
    'orderCurrentStatusAmountDue',
  ];

  /**
   * Canonical RTPN attribute hash order, per Nelnet's RTPN technical spec.
   *
   * Nelnet sends the attributes as name/value pairs in random order, but the
   * hash is the concatenation of the values in THIS order (only for the
   * attributes the institution elected to receive), with the timestamp always
   * last, followed by the shared secret.
   *
   * Verify the exact attribute set configured for the ASU payment order with
   * Nelnet (Transaction_Notification_Delimited spec). Attributes not present
   * in the request are simply skipped, so this list can be a superset.
   */
  const RTPN_HASH_ORDER = [
    'transactionType',
    'transactionStatus',
    'transactionSource',
    'transactionSourceReference',
    'transactionId',
    'originalTransactionId',
    'transactionTotalAmount',
    'transactionDate',
    'transactionAccountType',
    // Some Nelnet endpoints use the misspelled variant; only one of the two
    // will ever be present in a given configuration.
    'transactionAcountType',
    'transactionEffectiveDate',
    'transactionDescription',
    'transactionResultDate',
    'transactionResultEffectiveDate',
    'transactionResultCode',
    'transactionResultMessage',
    'orderNumber',
    'orderType',
    'orderName',
    'orderDescription',
    'orderAmount',
    'orderFee',
    'orderDueDate',
    'orderAmountDue',
    'orderBalance',
    'orderCurrentBalanceStatus',
    // Spelling variant used in the receipt-redirect spec.
    'orderCurrentStatusBalance',
    'orderCurrentStatusAmountDue',
    'payerType',
    'payerIdentifier',
    'payerFullName',
    'actualPayerType',
    'actualPayerIdentifier',
    'actualPayerFullName',
    'accountHolderName',
    'streetOne',
    'streetTwo',
    'city',
    'state',
    'zip',
    'country',
    'daytimePhone',
    'eveningPhone',
    'email',
    'userChoice1',
    'userChoice2',
    'userChoice3',
    'userChoice4',
    'userChoice5',
    'userChoice6',
    'userChoice7',
    'userChoice8',
    'userChoice9',
    'userChoice10',
    'userChoice11',
    'userChoice12',
    'userChoice13',
    'userChoice14',
    'userChoice15',
    'userChoice16',
    'userChoice17',
    'userChoice18',
    'userChoice19',
    'userChoice20',
    'userChoice21',
    'userChoice22',
    'userChoice23',
    'userChoice24',
    'userChoice25',
  ];

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'order_type' => "",
      // Per-site kill switch for everything this branch added (RTPN +
      // rewritten redirect handling). Default OFF so that on shared Site
      // Factory code a merge to develop does NOT change behavior on any site
      // until that site's admin explicitly opts in. OFF = exact legacy
      // (develop) behavior; ON = the new flow.
      'enable_rtpn_flow' => FALSE,
      'quikpay_redirect' => 'url',
      'quikpay_redirect_url' => '/commerce_nelnet/rtpn',
      'quikpay_test_pt_key' => 'key',
      'quikpay_test_rtpn_key' => 'key',
      'quikpay_prod_pt_key' => 'key',
      'quikpay_prod_rtpn_key' => 'key',
      'quikpay_test_url' => "https://uatquikpayasp.com/asu/commerce_manager/payer.do",
      'quikpay_prod_url' => "https://quikpayasp.com/asu/commerce_manager/payer.do",
      'quikpay_success_text' => 'Thank you for your payment. You may now view your orders by clicking on the link below.',
      'quikpay_checkout_text' => 'IMPORTANT! In order to make an online payment a new window will open to receive and process your payment details. You may return to this site once your payment is complete by closing that window. You will receive an email receipt as well as an additional email containing course registration details.',
      'quikpay_checkout_red' => 'You will be directed to a payment page that will process your payment details. Once the payment is complete, you will automatically return to this site and will be able to view your completed order. You will receive an email receipt as well as an additional email containing course registration details.',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['enable_rtpn_flow'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable new RTPN payment flow (beta)'),
      '#default_value' => $this->configuration['enable_rtpn_flow'],
      '#description' => t('<strong>Leave OFF unless this site has been tested with the new flow.</strong> When OFF, this gateway uses the legacy redirect-only behavior exactly as before. When ON, it uses the new flow: hardened redirect handling plus server-to-server Real Time Payment Notification (RTPN). Turn this on only after Nelnet has been configured for this site and you have verified payments end to end. This is a per-site setting.'),
    ];
    $form['order_type'] = [
      '#type' => 'textfield',
      '#title' => t('Nelnet order type'),
      '#default_value' => $this->configuration['order_type'],
    ];
    $form['quikpay_redirect'] = [
      '#type' => 'select',
      '#title' => t('Receipt method'),
      '#default_value' => $this->configuration['quikpay_redirect'],
      '#options' => [
        'url' => t('Receipt redirect URL (user returns to this site)'),
        'rtpn' => t('Nelnet-hosted receipt (RTPN only, no redirect)'),
      ],
      '#description' => t('Pick the flow that matches what the Nelnet deployment team configured for this Order. Nelnet provides ONE or the OTHER, not both. <strong>Receipt redirect URL:</strong> Nelnet redirects the payer back to this site; the order is completed at <code>/commerce_nelnet/rtpn</code> and validated with the pass-through (PT) key. <strong>Nelnet-hosted receipt:</strong> the payer stays on Nelnet and the order is completed only by the server-to-server RTPN POST, validated with the RTPN key. The RTPN endpoint below remains live as a safety net but is only used if Nelnet actually posts to it.'),
    ];
    $form['quikpay_redirect_url'] = [
      '#type' => 'textfield',
      '#title' => t('URL used for redirect if redirect method is selected above.'),
      '#default_value' => $this->configuration['quikpay_redirect_url'],
      '#description' => t('<strong>Make sure there is no trailing / in the URL as it matters in being authenticated by Nelnet!</strong> This URL (with the site domain) must be added to the Allowed Redirects list in Commerce Manager.'),
      '#disabled' => TRUE,
    ];
    $form['quikpay_rtpn_info'] = [
      '#type' => 'item',
      '#title' => t('RTPN notification URL'),
      '#markup' => t('Provide Nelnet this URL for Real Time Payment Notifications: <code>@url</code> (replace <em>GATEWAY_ID</em> with this gateway machine name). Configure Nelnet to expect the word <code>success</code> in the response body, or rely on the HTTP 200 response code.', [
        '@url' => \Drupal::request()->getSchemeAndHttpHost() . '/payment/notify/GATEWAY_ID',
      ]),
    ];
    $form['quikpay_test_pt_key'] = [
      '#type' => 'textfield',
      '#title' => t('Nelnet pass through authentication test key'),
      '#default_value' => $this->configuration['quikpay_test_pt_key'],
    ];
    $form['quikpay_test_rtpn_key'] = [
      '#type' => 'textfield',
      '#title' => t('Nelnet real time payment notification test key'),
      '#default_value' => $this->configuration['quikpay_test_rtpn_key'],
    ];
    $form['quikpay_prod_pt_key'] = [
      '#type' => 'textfield',
      '#title' => t('Nelnet pass through authentication production key'),
      '#default_value' => $this->configuration['quikpay_prod_pt_key'],
    ];
    $form['quikpay_prod_rtpn_key'] = [
      '#type' => 'textfield',
      '#title' => t('Nelnet real time payment notification production key'),
      '#default_value' => $this->configuration['quikpay_prod_rtpn_key'],
    ];
    $form['quikpay_test_url'] = [
      '#type' => 'textfield',
      '#title' => t('Nelnet test URL'),
      '#default_value' => $this->configuration['quikpay_test_url'],
    ];
    $form['quikpay_prod_url'] = [
      '#type' => 'textfield',
      '#title' => t('Nelnet production URL'),
      '#default_value' => $this->configuration['quikpay_prod_url'],
    ];
    $form['quikpay_success_text'] = [
      '#type' => 'textarea',
      '#title' => t('Success Message'),
      '#default_value' => $this->configuration['quikpay_success_text'],
      '#description' => t('Text to display upon successful completion of payment. Also shown on the fallback receipt page when the user session cannot be restored after returning from Nelnet.'),
      '#required' => TRUE,
    ];
    $form['quikpay_checkout_text'] = [
      '#type' => 'textarea',
      '#title' => t('RTPN Checkout instructions'),
      '#default_value' => $this->configuration['quikpay_checkout_text'],
      '#description' => t('Instructional text to display below the proceed to checkout link.'),
      '#required' => TRUE,
    ];
    $form['quikpay_checkout_red'] = [
      '#type' => 'textarea',
      '#title' => t('Redirect Method Checkout instructions'),
      '#default_value' => $this->configuration['quikpay_checkout_red'],
      '#description' => t('Instructional text to display below the proceed to checkout link.'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->getValue($form['#parents']);
    foreach ([
      'order_type',
      'enable_rtpn_flow',
      'quikpay_redirect',
      'quikpay_redirect_url',
      'quikpay_test_pt_key',
      'quikpay_test_rtpn_key',
      'quikpay_prod_pt_key',
      'quikpay_prod_rtpn_key',
      'quikpay_test_url',
      'quikpay_prod_url',
      'quikpay_success_text',
      'quikpay_checkout_text',
      'quikpay_checkout_red',
    ] as $config_key) {
      $this->configuration[$config_key] = $values[$config_key];
    }
  }

  /**
   * {@inheritdoc}
   *
   * Safety net only. With the current architecture the AsuQuikpayOffSite
   * controller validates the Nelnet redirect and completes the order before
   * the user ever reaches the Commerce return route, so by the time this
   * runs the order is normally already paid and this is a no-op. If the
   * order is NOT paid when this runs, something went wrong upstream and we
   * must throw so Commerce does NOT silently complete an unpaid order.
   */
  public function onReturn(OrderInterface $order, Request $request) {
    // Legacy fallback: when the new flow is disabled for this gateway, behave
    // exactly as the develop version did.
    if (!$this->isNewFlowEnabled()) {
      $this->onReturnLegacy($order, $request);
      return;
    }

    if ($this->orderHasCompletedPayment($order)) {
      // Already processed by the redirect controller or by RTPN.
      return;
    }

    // Re-validate the forwarded Nelnet parameters directly. The controller
    // forwards the full original query string when redirecting here.
    $params = $request->query->all();
    if (!$this->validateRedirectHash($params)) {
      throw new PaymentGatewayException('QuikPay return could not be validated.');
    }
    if (!$this->validateTimestamp($params['timestamp'] ?? '')) {
      throw new PaymentGatewayException('QuikPay return timestamp expired.');
    }
    if (!$this->isSuccessfulTransaction($params['transactionType'] ?? '', $params['transactionStatus'] ?? '')) {
      throw new PaymentGatewayException('QuikPay payment was not successful.');
    }
    if (!$this->validateAmount($order, $params)) {
      throw new PaymentGatewayException('Charged amount does not cover the order amount.');
    }

    $this->processSuccessfulTransaction($order, $params);
  }

  /**
   * {@inheritdoc}
   *
   * Handles Real Time Payment Notifications (RTPN) POSTed by Nelnet to
   * /payment/notify/{gateway_machine_name}. This is the server-to-server
   * safety net: it runs with no user session at all, so orders complete even
   * when the payer never returns to the site (closed window, lost session,
   * anonymous users, etc.).
   */
  public function onNotify(Request $request) {
    // Legacy fallback: when the new flow is disabled, RTPN is not processed at
    // all (the develop version had no onNotify). Defer to the base no-op so the
    // endpoint stays harmless until the site opts in.
    if (!$this->isNewFlowEnabled()) {
      return parent::onNotify($request);
    }

    $logger = \Drupal::logger('asu_quikpay');

    // RTPN is always a POST per Nelnet documentation. Fall back to query
    // parameters to ease manual testing in UAT.
    $params = $request->request->all();
    if (empty($params)) {
      $params = $request->query->all();
    }

    if (empty($params['timestamp']) || empty($params['hash'])) {
      $logger->warning('RTPN received without timestamp/hash. Params: @params', ['@params' => json_encode(array_keys($params))]);
      return new Response('failure: missing parameters', 400);
    }

    if (!$this->validateRtpnHash($params)) {
      $logger->warning('RTPN hash validation failed for orderNumber @order. Params received: @params', [
        '@order' => $params['orderNumber'] ?? 'unknown',
        '@params' => json_encode(array_keys($params)),
      ]);
      return new Response('failure: invalid hash', 403);
    }

    if (!$this->validateTimestamp($params['timestamp'])) {
      $logger->warning('RTPN timestamp outside the 5 minute window for orderNumber @order.', [
        '@order' => $params['orderNumber'] ?? 'unknown',
      ]);
      return new Response('failure: stale timestamp', 403);
    }

    $type = (string) ($params['transactionType'] ?? '');
    $status = (string) ($params['transactionStatus'] ?? '');

    // Credit card refunds: mark the original payment as refunded.
    if ($type === '2') {
      $this->processRefundNotification($params);
      return new Response('success', 200);
    }

    // Returned eChecks (status 7) and other failures: log for staff review.
    // We still answer 200/success because the notification itself arrived
    // fine; we just don't complete the order.
    if (!$this->isSuccessfulTransaction($type, $status)) {
      $logger->critical('RTPN reports a non-successful transaction for orderNumber @order (type @type, status @status, result: @message). Manual review may be required.', [
        '@order' => $params['orderNumber'] ?? 'unknown',
        '@type' => $type,
        '@status' => $status,
        '@message' => $params['transactionResultMessage'] ?? '',
      ]);
      return new Response('success', 200);
    }

    $order = $this->loadOrderFromParams($params);
    if (!$order) {
      $logger->error('RTPN received for unknown orderNumber @order.', ['@order' => $params['orderNumber'] ?? 'NULL']);
      return new Response('failure: unknown order', 404);
    }

    if (!$this->validateAmount($order, $params)) {
      $logger->error('RTPN amount mismatch for order @order: transactionTotalAmount=@paid orderFee=@fee, order total=@total.', [
        '@order' => $order->id(),
        '@paid' => $params['transactionTotalAmount'] ?? 'NULL',
        '@fee' => $params['orderFee'] ?? '0',
        '@total' => $order->getTotalPrice()->getNumber(),
      ]);
      return new Response('failure: amount mismatch', 409);
    }

    $this->processSuccessfulTransaction($order, $params);
    $logger->info('RTPN processed successfully for order @order (transactionId @tid).', [
      '@order' => $order->id(),
      '@tid' => $params['transactionId'] ?? '',
    ]);

    return new Response('success', 200);
  }

  /**
   * Validates the hash of a receipt-redirect request (browser GET).
   *
   * Hash = SHA256(concat(values in REDIRECT_RETURN_PARAMS order) . timestamp
   * . pass-through key).
   */
  public function validateRedirectHash(array $params): bool {
    if (empty($params['hash'])) {
      return FALSE;
    }
    $key = $this->getPassThroughKey();
    $hash_string = '';
    foreach (static::REDIRECT_RETURN_PARAMS as $name) {
      $hash_string .= $params[$name] ?? '';
    }
    $hash_string .= $params['timestamp'] ?? '';
    $computed = hash('sha256', $hash_string . $key);
    return hash_equals($computed, (string) $params['hash']);
  }

  /**
   * Validates the hash of an RTPN request (server-to-server POST).
   *
   * Hash = SHA256(concat(values of received attributes in canonical
   * RTPN_HASH_ORDER) . timestamp . RTPN key). Attributes arrive in random
   * order but hash in the canonical order; attributes not received are
   * skipped.
   */
  public function validateRtpnHash(array $params): bool {
    if (empty($params['hash'])) {
      return FALSE;
    }
    $key = $this->getRtpnKey();
    $hash_string = '';
    foreach (static::RTPN_HASH_ORDER as $name) {
      if (array_key_exists($name, $params)) {
        $hash_string .= $params[$name];
      }
    }
    $hash_string .= $params['timestamp'] ?? '';
    $computed = hash('sha256', $hash_string . $key);
    return hash_equals($computed, (string) $params['hash']);
  }

  /**
   * Validates that an incoming epoch-milliseconds timestamp is fresh.
   *
   * Per Nelnet docs, requests with timestamps older than 5 minutes must be
   * disregarded (replay protection).
   */
  public function validateTimestamp($timestamp): bool {
    $timestamp = (float) $timestamp;
    if ($timestamp <= 0) {
      return FALSE;
    }
    $now_ms = \Drupal::time()->getCurrentTime() * 1000;
    return abs($now_ms - $timestamp) <= static::TIMESTAMP_TOLERANCE_MS;
  }

  /**
   * Determines whether a transaction type/status pair represents success.
   *
   * Per Nelnet spec:
   * - transactionType 1 (credit card payment): status 1 = accepted.
   * - transactionType 3 (eCheck payment): 5 accepted, 6 posted, 8 NOC are
   *   successful; 7 = returned (failed).
   * - transactionType 2 (credit card refund) is never a checkout success.
   */
  public function isSuccessfulTransaction(string $type, string $status): bool {
    if ($type === '1') {
      return $status === '1';
    }
    if ($type === '3') {
      return in_array($status, ['5', '6', '8'], TRUE);
    }
    return FALSE;
  }

  /**
   * Validates that the amount charged covers the order total.
   *
   * TransactionTotalAmount = orderAmount + orderFee (in cents). We compare
   * integer minor units (never floats) and require the net amount to cover
   * the order total, tolerating an add-on convenience fee.
   */
  public function validateAmount(OrderInterface $order, array $params): bool {
    $expected = (int) round(((float) $order->getTotalPrice()->getNumber()) * 100);
    $paid_total = (int) ($params['transactionTotalAmount'] ?? 0);
    $fee = (int) ($params['orderFee'] ?? 0);
    return ($paid_total - $fee) >= $expected;
  }

  /**
   * Loads the commerce order referenced by the Nelnet orderNumber parameter.
   */
  public function loadOrderFromParams(array $params): ?OrderInterface {
    $order_id = $params['orderNumber'] ?? NULL;
    if (!$order_id || !is_numeric($order_id)) {
      return NULL;
    }
    /** @var \Drupal\commerce_order\Entity\OrderInterface|null $order */
    $order = $this->entityTypeManager->getStorage('commerce_order')->load($order_id);
    return $order;
  }

  /**
   * Checks whether the order already has a completed payment recorded.
   */
  public function orderHasCompletedPayment(OrderInterface $order): bool {
    $payments = $this->entityTypeManager->getStorage('commerce_payment')->loadByProperties([
      'order_id' => $order->id(),
      'state' => 'completed',
    ]);
    return !empty($payments);
  }

  /**
   * Records the payment and completes the order. Idempotent.
   *
   * This is intentionally session-independent so it can run from the
   * receipt-redirect controller (with or without a user session) and from
   * the RTPN notification (no session at all). Whichever channel arrives
   * first wins; subsequent calls are no-ops.
   */
  public function processSuccessfulTransaction(OrderInterface $order, array $params): void {
    $logger = \Drupal::logger('asu_quikpay');
    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
    $remote_id = (string) ($params['transactionId'] ?? '');

    // Idempotency guard 1: same Nelnet transaction already recorded.
    if ($remote_id !== '') {
      $existing = $payment_storage->loadByProperties([
        'order_id' => $order->id(),
        'remote_id' => $remote_id,
      ]);
      if (!empty($existing)) {
        $logger->info('Skipping duplicate payment recording for order @order, transactionId @tid.', [
          '@order' => $order->id(),
          '@tid' => $remote_id,
        ]);
        $this->placeOrderIfNeeded($order);
        return;
      }
    }

    // Idempotency guard 2: order already has a completed payment.
    if ($this->orderHasCompletedPayment($order)) {
      $this->placeOrderIfNeeded($order);
      return;
    }

    $payment = $payment_storage->create([
      'state' => 'completed',
      'amount' => $order->getTotalPrice(),
      'payment_gateway' => $this->parentEntity->id(),
      'order_id' => $order->id(),
      'remote_id' => $remote_id,
      'remote_state' => (string) ($params['transactionStatus'] ?? ''),
    ]);
    $payment->save();
    $logger->info('Payment recorded for order @order (transactionId @tid, amount @amount).', [
      '@order' => $order->id(),
      '@tid' => $remote_id,
      '@amount' => $order->getTotalPrice()->getNumber(),
    ]);

    $this->placeOrderIfNeeded($order);
  }

  /**
   * Places the order if it is still a draft/cart.
   *
   * This fixes the "stuck in active carts" failure mode: completion no
   * longer depends on the user's browser making it back through the
   * Commerce checkout return route. Applying the 'place' transition fires
   * the standard order events (receipt email, cart finalization, stock,
   * etc.).
   */
  protected function placeOrderIfNeeded(OrderInterface $order): void {
    if ($order->getState()->getId() !== 'draft') {
      return;
    }
    $order->getState()->applyTransitionById('place');
    $order->set('checkout_step', 'complete');
    if ($order->hasField('cart')) {
      // Defensive: commerce_cart finalizes this on the place transition, but
      // make sure the order can never linger as an active cart.
      $order->set('cart', FALSE);
    }
    // The order was locked when the offsite payment process started.
    $order->unlock();
    $order->save();
    \Drupal::logger('asu_quikpay')->info('Order @order placed via QuikPay gateway.', ['@order' => $order->id()]);
  }

  /**
   * Handles an RTPN credit card refund notification (transactionType 2).
   */
  protected function processRefundNotification(array $params): void {
    $logger = \Drupal::logger('asu_quikpay');
    $original_id = (string) ($params['originalTransactionId'] ?? '');
    if ($original_id === '') {
      $logger->warning('RTPN refund received without originalTransactionId.');
      return;
    }
    $payments = $this->entityTypeManager->getStorage('commerce_payment')->loadByProperties([
      'remote_id' => $original_id,
    ]);
    if (empty($payments)) {
      $logger->warning('RTPN refund received for unknown original transaction @tid.', ['@tid' => $original_id]);
      return;
    }
    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = reset($payments);
    try {
      $refund_cents = (int) ($params['transactionTotalAmount'] ?? 0) - (int) ($params['orderFee'] ?? 0);
      $refund_amount = new Price((string) ($refund_cents / 100), $payment->getAmount()->getCurrencyCode());
      $payment->setRefundedAmount($refund_amount);
      $transition = $refund_amount->lessThan($payment->getAmount()) ? 'partially_refund' : 'refund';
      if ($payment->getState()->isTransitionAllowed($transition)) {
        $payment->getState()->applyTransitionById($transition);
      }
      $payment->save();
      $logger->info('RTPN refund recorded for payment @pid (order @order).', [
        '@pid' => $payment->id(),
        '@order' => $payment->getOrderId(),
      ]);
    }
    catch (\Exception $e) {
      $logger->error('Failed to record RTPN refund for transaction @tid: @message', [
        '@tid' => $original_id,
        '@message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Whether the new RTPN/redirect flow is enabled for this gateway.
   *
   * Per-site kill switch. When FALSE the gateway runs the legacy (develop)
   * code paths so shared code can ship to production without changing
   * behavior on sites that have not opted in.
   */
  public function isNewFlowEnabled(): bool {
    return !empty($this->configuration['enable_rtpn_flow']);
  }

  /**
   * Legacy onReturn, preserved verbatim from the develop version.
   *
   * Used only when the new flow is disabled. The redirect controller signs an
   * internal "paid" digest and Commerce routes the payer here on return; this
   * re-validates that digest and records the payment.
   *
   * @deprecated Remove once the new flow is fully rolled out to all sites.
   */
  protected function onReturnLegacy(OrderInterface $order, Request $request): void {
    $logger = \Drupal::logger('asu_quikpay');
    // We load order id and key.
    $order_id = $order->get('order_id')->first()->getValue()["value"];
    $key = $this->configuration['mode'] === "live" ? $this->configuration['quikpay_prod_pt_key'] : $this->configuration['quikpay_test_pt_key'];

    // Get the success parameter from the URL.
    $success = boolval($request->query->get('success'));

    // Create the hash string to be compared with the paid parameter value.
    $hash_string = '';
    $hash_string .= $order_id;
    $hash_string .= $success;
    $hash = hash('SHA256', $hash_string . $key);

    $validated = $hash === $request->query->get('paid') ? TRUE : FALSE;
    // If the validation is correct we proceed with the order.
    if ($validated) {
      $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');

      $payment = $payment_storage->create([
        'state' => 'completed',
        'amount' => $order->getTotalPrice(),
        'payment_gateway' => $this->parentEntity->id(),
        'order_id' => $order->id(),
      ]);

      $logger->info('Saving Payment information');

      $payment->save();
    }
  }

  /**
   * Returns the pass-through shared secret for the current mode.
   */
  public function getPassThroughKey(): string {
    return $this->configuration['mode'] === 'live'
      ? $this->configuration['quikpay_prod_pt_key']
      : $this->configuration['quikpay_test_pt_key'];
  }

  /**
   * Returns the RTPN shared secret for the current mode.
   */
  public function getRtpnKey(): string {
    return $this->configuration['mode'] === 'live'
      ? $this->configuration['quikpay_prod_rtpn_key']
      : $this->configuration['quikpay_test_rtpn_key'];
  }

}
