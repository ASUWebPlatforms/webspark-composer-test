<?php

namespace Drupal\wpc_rfi_forms_programs\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for wpc_rfi_forms_programs entity.
 *
 * @ingroup wpc_rfi_forms_programs
 */
class ProgramListBuilder extends EntityListBuilder {

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('url_generator')
    );
  }

  /**
   * Constructs a new ProgramListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, UrlGeneratorInterface $url_generator) {
    parent::__construct($entity_type, $storage);
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   *
   * We override ::render() so that we can add our own content above the table.
   * parent::render() is where EntityListBuilder creates the table using our
   * buildHeader() and buildRow() implementations.
   */
  public function render() {
    $build['description'] = [
      '#markup' => $this->t('Creates a Program Entity as fieldable entities. You can manage the fields on the <a href="@adminlink">Programs admin page</a>.', [
        '@adminlink' => $this->urlGenerator->generateFromRoute('wpc_rfi_forms_programs.program_settings'),
      ]),
    ];
    $build['table'] = parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the program list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header['id'] = $this->t('ProgramID');
    $header['name'] = $this->t('Displayed Plan'); //replces ps_acad_plan_descr_display from legacy
    $header['ps_acad_plan_descr_key'] = $this->t('System Plan');
    $header['ps_acad_plan_key'] = $this->t('Plan Key');
    $header['campus_key'] = $this->t('Campus Key');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\wpc_rfi_forms_programs\Entity\Program */
    $row['id'] = $entity->id();
    $row['name'] = $entity->toLink()->toString();
    $row['ps_acad_plan_descr_key'] = $entity->ps_acad_plan_descr_key->value;
    $row['ps_acad_plan_key'] = $entity->ps_acad_plan_key->value;
    $row['campus_key'] = $entity->campus_key->value;
    return $row + parent::buildRow($entity);
  }

}
