<?php
namespace Drupal\asu_feeds_customization\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 @file
 * Contains \Drupal\asu_feeds_customization\Plugin\Block\relate_create_nodes_block
 */



/**
 * Provides a links block to create content.
 *
 * @Block(
 *   id = "relate_create_nodes_block",
 *   admin_label = @Translation("Relate and create nodes block"),
 *  
 * )
 */
class relate_create_nodes_block extends BlockBase {

  

  /**
   * {@inheritdoc}
   */
  public function build() {
	  $build = [];
   
	  $current_path = \Drupal::service('path.current')->getPath();
	  $path_args = explode('/', $current_path);
	  $group_id = $path_args[2];	
	 // $create_links = "<p><a class='btn btn-xs btn-default btn-maroon' href='/group/$group_id/node/add'>Relate node</a>&nbsp;&nbsp;<a class='btn btn-xs btn-success' href='/group/$group_id/node/create'>Create node</a><a class='btn btn-xs btn-success' href='/group/$group_id/content/create/group_node%3Amyasu_cb_post'>Create Post node</a><a class='btn btn-xs btn-success' href='/group/$group_id/content/create/group_node%3Amyasu_cb_tab'>Create Tab node</a></p>";
	  $create_links = "<p>&nbsp;</p><p><a class='btn btn-small btn-primary' href='/group/$group_id/content/create/group_node%3Amyasu_cb_post'>Create College box Post</a>&nbsp;&nbsp;&nbsp;<a class='btn btn-small btn-primary' href='/group/$group_id/content/create/group_node%3Amyasu_cb_tab'>Create College box tab</a></p>";
	  $build['links_block'] = [
		   '#markup' => $create_links
	  ];
 	 
      return $build;
 }
	
  public function getCacheMaxAge() {
     return 0;
  }
}