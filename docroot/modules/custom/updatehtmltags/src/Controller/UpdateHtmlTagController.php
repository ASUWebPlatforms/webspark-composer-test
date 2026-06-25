<?php

namespace Drupal\updatehtmltags\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\layout_builder\Section;

/**
 * Defines HelloController class.
 */
class UpdateHtmlTagController extends ControllerBase
{

    /**
     * Display the markup.
     *
     * @return array
     *   Return markup array.
     */
    public function content()
    {
        $connection = \Drupal::service('database');
        $query = $connection->query("update node__body set body_value = replace(body_value, '<table' , '<div class=uds-table><table')");
        $result = $query->execute();

        $connection = \Drupal::service('database');
        $query = $connection->query("update node__body set body_value = replace(body_value, '</table>' , '</table></div>')");
        $result = $query->execute();

        if (!is_numeric($result)) {
            \Drupal::logger('updatehtmltags')->error('Error INSERT failed: ');
        }
        return [
            '#type' => 'markup',
            '#markup' => $this->t('Hello, World!'),
        ];
    }


    /**
 * Custom function to delete a block from the layout of a node.
 *
 * @param \Drupal\Core\Entity\EntityInterface $node
 *   The node entity from which the block needs to be deleted.
 * @param string $blockPluginId
 *   The ID of the block plugin to be deleted.
 */
public function custom_delete_block_from_node_layout() {

        // $node = \Drupal\node\Entity\Node::load(5840);

        // dump($node);
        // exit;


        $entityTypeManager = \Drupal::service('entity_type.manager');

        // Get the node storage.
        $nodeStorage = $entityTypeManager->getStorage('node');
      
      //   Use EntityQuery to load all nodes of the specified content type.
        $query = $nodeStorage->getQuery()->accessCheck(FALSE);
        $nids = $query->execute();
      
      //   Load nodes.
        $nodes = $nodeStorage->loadMultiple($nids);


    // Specify the ID of the block plugin to be deleted.
    $blockPluginId = 'local_tasks_block';

        // Load the layout builder manager.
        // Load the layout of the node.

    foreach($nodes as $node){
      $layout = $node->getType();
      // dump($node);
      // exit;
      if($node->hasField('layout_builder__layout')){
    $layout = $node->get('layout_builder__layout')->getValue();
    // Check if the node has a layout.
    if (!empty($layout)) {
      // Iterate through each section of the layout.
      foreach ($layout as $section) {
        // Check if the section contains components.
        $comp=$section['section']->getComponents();
        if (!empty($comp)) {
          // Iterate through each component in the section.
          foreach ($comp as $k=>$key) {
            // Check if the component is a block and matches the provided block plugin ID.
             if (!empty($key->get('configuration')) && $key->get('configuration')['id'] == $blockPluginId) {
            //    Remove the component (block) from the layout.
            $section['section']->removeComponent($k);
            }
          }
        
        }
      }
    }

     // Save the updated layout to the node.
     $node->set('layout_builder__layout', $layout);
     $node->save();
  }
}
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Hello, World!'),
  ];
    

}
}