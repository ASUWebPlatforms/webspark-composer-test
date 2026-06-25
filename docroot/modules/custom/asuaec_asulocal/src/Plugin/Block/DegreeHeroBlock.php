<?php

namespace Drupal\asuaec_asulocal\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\asuaec_asulocal\Service\DegreeResolver;

/**
 * Degree Hero block (image + title).
 *
 * @Block(
 *   id = "asuaec_asulocal_degree_hero",
 *   admin_label = @Translation("ASU Local Degree Hero"),
 * )
 */
class DegreeHeroBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected DegreeResolver $degreeResolver,
    protected RouteMatchInterface $routeMatch,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('asuaec_asulocal.degree_resolver'),
      $container->get('current_route_match'),
    );
  }

  public function build(): array {
    $code = (string) $this->routeMatch->getParameter('code');
    if ($code === '') {
      return ['#markup' => $this->t('Missing degree code.')];
    }

    $degree = $this->degreeResolver->getDegreeByPlanCode($code);
    if (!$degree) {
      return ['#markup' => $this->t('Degree not found.')];
    }

    $data = $degree;

    $title = $data['title'] ?? 'Untitled';
    $image = $data['degree_image'] ?? NULL;

    return [
      '#theme' => 'asuaec_asulocal_degree_hero',
      '#title' => $title,
      '#image_url' => $image,
      '#cache' => [
        'max-age' => 3600,
        'contexts' => ['url.path'],
      ],
    ];
  }

}