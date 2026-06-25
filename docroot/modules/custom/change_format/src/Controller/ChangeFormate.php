<?php

namespace Drupal\change_format\Controller;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Controller function to change field value of all nodes.
 */
class ChangeFormate extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ChangeFormate object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Change field value of all nodes.
   */
  public function changeFieldValue() {
    // Load all published node IDs.
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    $query = $nodeStorage->getQuery()->accessCheck(FALSE);
    $nids = $query->execute();
    // dump($nids);
    // exit;
    // Load and update each node.
    foreach ($nids as $nid) {
      /** @var \Drupal\node\Entity\Node $node */
      $node = $this->entityTypeManager->getStorage('node')->load($nid);
      
      if ($node) {
      $body=$node->get('body')->getValue();
      $body[0]["format"]="full_html";
      $node->set('body', $body, FALSE);
      $node->save();
        
      }
    }

    // Optionally return a response.
    return [
      '#markup' => $this->t('Field value updated for all nodes.'),
    ];
  }


  public function changeBlockFieldValue(){
    $nodeStorage = $this->entityTypeManager->getStorage('block_content');
    $query = $nodeStorage->getQuery()->accessCheck(FALSE);
    $nids = $query->execute();
    // dump($nids);
    // exit;
    // Load and update each node.
    foreach ($nids as $nid) {
      /** @var \Drupal\node\Entity\Block $Block */
      $block = $this->entityTypeManager->getStorage('block_content')->load($nid);
      // dump($block);
      // exit;

      if($block->hasField('body')){
        $body=$block->get('body')->getValue();
        $body[0]["format"]="full_html";
        $block->set('body', $body, FALSE);
      }
      if($block->hasField('field_formatted_text')){
        $data=$block->get('field_formatted_text')->getValue();
        $data[0]["format"]="full_html";
        $block->set('field_formatted_text', $data, FALSE);
      }
      // dump($block);
     $block->save();
    //  dump($this->entityTypeManager->getStorage('block_content')->load(891));
    }

    return [
      '#markup' => $this->t('Block Field value updated for all nodes.'),
    ];
  }

}
