<?php

namespace Drupal\asuaec_asulocal\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\asuaec_asulocal\Form\DegreesFilterForm;
use Drupal\asuaec_asulocal\Service\DegreesCardBuilder;

/**
 * ASU Local Degrees block (filters + cards).
 *
 * @Block(
 *   id = "asuaec_asulocal_block",
 *   admin_label = @Translation("ASU Local Degrees"),
 * )
 */
class LocalDegreesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected DegreesCardBuilder $cardBuilder,
    protected FormBuilderInterface $formBuilder,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('asuaec_asulocal.degrees_card_builder'),
      $container->get('form_builder'),
    );
  }

  public function build(): array {
    $form = $this->formBuilder->getForm(DegreesFilterForm::class);

    $request = \Drupal::request();
    $q = (string) $request->query->get('q', '');
    $interest = (string) $request->query->get('interest', '');


    $count = $this->cardBuilder->getFilteredCount($q, $interest);

    $count_markup = [
      '#type' => 'container',
      '#attributes' => ['class' => ['asu-degrees-count', 'mt-4', 'mb-3']],
      'text' => [
        '#markup' => '<strong>' . (int) $count . '</strong> programs available',
      ],
    ];


    $cards = $this->cardBuilder->buildCards(NULL, $q, $interest);

    // Wrap BOTH count + cards so AJAX replaces them together.
    $results = [
      '#type' => 'container',
      '#attributes' => ['id' => 'degrees-results-wrapper'],
      'count' => $count_markup,
      'cards' => $cards,
    ];

    return [
      'filters' => $form,
      'results' => $results,
      '#cache' => [
        'max-age' => 0,
      ],
    ];


  } // END OF public function build()

} // END OF class LocalDegreesBlock