<?php

namespace Drupal\asuaec_asulocal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\asuaec_asulocal\Service\DegreesCardBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

class DegreesFilterForm extends FormBase {

  protected DegreesCardBuilder $cardBuilder;
  protected RequestStack $requestStackService;

  public function __construct(
    DegreesCardBuilder $cardBuilder,
    RequestStack $requestStackService,
  ) {
    $this->cardBuilder = $cardBuilder;
    $this->requestStackService = $requestStackService;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('asuaec_asulocal.degrees_card_builder'),
      $container->get('request_stack'),
    );
  }

  public function getFormId(): string {
    return 'asuaec_asulocal_degrees_filter_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $request = $this->requestStackService->getCurrentRequest();
    $defaults_q = (string) $request->query->get('q', '');
    $defaults_interest = (string) $request->query->get('interest', '');

    $form['#method'] = 'get';

    $form['filters'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row', 'g-3', 'align-items-end', 'mb-4']],
    ];

    $interest_options = $this->cardBuilder->getInterestOptions();

    // URL uses the label (e.g. "Education") but our select keys are slugs (e.g. "education-degrees").
    // Map label -> key so the dropdown preselects.
    if ($defaults_interest !== '' && !isset($interest_options[$defaults_interest])) {
      $needle = mb_strtolower($defaults_interest);

      foreach ($interest_options as $key => $label) {
        if (mb_strtolower((string) $label) === $needle) {
          $defaults_interest = (string) $key;
          break;
        }
      }
    }

    $form['filters']['interest'] = [
      '#type' => 'select',
      '#default_value' => $defaults_interest,
      '#empty_option' => $this->t('All Areas of Interest'),
      '#options' => $interest_options,
      '#prefix' => '<div class="col-12 col-md-6 col-lg-4">',
      '#suffix' => '</div>',
    ];


    $form['filters']['q'] = [
      '#type' => 'textfield',
      '#default_value' => $defaults_q,
      '#attributes' => [
        'placeholder' => $this->t('Search...'),
      ],
      '#prefix' => '<div class="col-12 col-md-6 col-lg-4">',
      '#suffix' => '</div>',
    ];
    

    $form['filters']['actions'] = [
      '#type' => 'container',
      '#prefix' => '<div class="col-12 col-lg-4">',
      '#suffix' => '</div>',
    ];

    $form['filters']['actions']['apply'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#attributes' => ['class' => ['btn btn-maroon btn-md']],
      '#ajax' => [
        'callback' => '::ajaxUpdateResults',
        'wrapper' => 'degrees-results-wrapper',
        'disable-refocus' => TRUE,
      ],
    ];

    $form['filters']['actions']['reset'] = [
      '#type' => 'link',
      '#title' => $this->t('Reset'),
      '#url' => \Drupal\Core\Url::fromUserInput('/degrees'),
      '#attributes' => ['class' => ['btn', 'btn-link', 'ms-2']],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Do nothing. (GET + AJAX)
  }

  public function ajaxUpdateResults(array &$form, FormStateInterface $form_state): array {
    $q = (string) $form_state->getValue('q');
    $interest = (string) $form_state->getValue('interest');

    $count = $this->cardBuilder->getFilteredCount($q, $interest);
    $cards = $this->cardBuilder->buildCards(NULL, $q, $interest);

    return [
      '#type' => 'container',
      '#attributes' => ['id' => 'degrees-results-wrapper'],
      'count' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['asu-degrees-count', 'mt-4', 'mb-3']],
        'text' => ['#markup' => '<strong>' . (int) $count . '</strong> programs available'],
      ],
      'cards' => $cards,
    ];
  }

}