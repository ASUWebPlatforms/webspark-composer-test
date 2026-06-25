<?php
namespace Drupal\asu_feeds_customization\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 @file
 * Contains \Drupal\asu_feeds_customization\Plugin\Block\create_content_links_block
 */



/**
 * Provides a links block to create content.
 *
 * @Block(
 *   id = "create_content_links_block",
 *   admin_label = @Translation("Create content links block"),
 *  
 * )
 */
class create_content_links_block extends BlockBase {

  

  /**
   * {@inheritdoc}
   */
  public function build() {
	  $build = [];
   
	  $current_path = \Drupal::service('path.current')->getPath();
	  $path_args = explode('/', $current_path);
	  $group_id = intval($path_args[2]);
	  $create_links = "<p>&nbsp;</p><p><a class='btn btn-small btn-primary' href='/group/$group_id/content/create/group_node%3Amyasu_cb_tab'>Create College box tab</a>";
	  $create_links .= "&nbsp;&nbsp;&nbsp;<a class='btn btn-small btn-primary' href='/group/$group_id/content/create/group_node%3Amyasu_cb_post'>Create College box post</a></p>";
	  $build['links_block'] = [
		   '#markup' => "<div class='container'><div class='row'>".$create_links."</div></div><p>&nbsp;</p>"
	  ];
 	 
      return $build;
 }
	
  public function getCacheMaxAge() {
     return 0;
  }
}