<?php

/**
 * @file
 * Preprocessors and helper functions to make theming easier.
 */

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\views\Views;
use Drupal\node\Entity\Node;
use Drupal\field_collection;
use Drupal\field_collection\Entity;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\views\Plugin\views\row\RssPluginBase;
use Drupal\node\NodeInterface;
use Drupal\Core\Link;
use Drupal\node\NodeViewBuilder;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContent;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\File\FileUrlGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;



/**
 * Prepares variables for RSS feed templates.
 *
 * Default template: views-view-rss.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_view_collegebox(&$variables) {
  $view = $variables['view'];
  $items = $variables['rows'];
  $style = $view->style_plugin;
//	\Drupal::logger('style')->notice('fields: <pre>@fields</pre>', ['@fields' => print_r($style->channel_elements, TRUE)]);
  $view_field = $view->field;
  
  $config = \Drupal::config('system.site');

  // The RSS 2.0 "spec" doesn't indicate HTML can be used in the description.
  // We strip all HTML tags, but need to prevent double encoding from properly
  // escaped source data (such as &amp becoming &amp;amp;).
  //$variables['description'] = Html::decodeEntities(strip_tags($style->getDescription()));

  if ($view->display_handler->getOption('sitename_title')) {
    $title = $config->get('name');
    if ($slogan = $config->get('slogan')) {
      $title .= ' - ' . $slogan;
    }
  }
  else {
    $title = $view->getTitle();
  }
  $variables['title'] = $title;

  

  $variables['langcode'] = \Drupal::languageManager()->getCurrentLanguage()->getId();
  if(!empty($style->namespaces)){
	  $variables['namespaces'] = new Attribute($style->namespaces);
  }	
  else{
	  $variables['namespaces'] = '';
  } 	
  
  $variables['items'] = $items;
  //$variables['channel_elements'] = !empty($style->channel_elements)?\Drupal::service('renderer')->render($style->channel_elements):'';
  if (!empty($style->channel_elements) && is_array($style->channel_elements)) {
    $variables['channel_elements'] = \Drupal::service('renderer')->render($style->channel_elements);
  } 
  else {
    $variables['channel_elements'] = '';
  }

  $empty_text = isset($variables['view']->display['default']->display_options['empty']) ? $variables['view']->display['default']->display_options['empty'] : '';
  
  // During live preview we don't want to output the header since the contents
  // of the feed are being displayed inside a normal HTML page.
  if (empty($variables['view']->live_preview)) {
    $variables['view']->getResponse()->headers->set('Content-Type', 'application/rss+xml; charset=utf-8');
  }
  
  
  
  // Generate the list of tabs
  global $myasu_cb_archive_gid;
  $tabview = Views::getView('myasu_cb_tabs_by_gid');
  
  if (is_object($tabview)) {
        $tabview->setDisplay('default');
        if (is_array($myasu_cb_archive_gid)) {
          $tabview->setArguments(array($myasu_cb_archive_gid));
        }
        $tabview->execute();

        // Render the view
        $tab_view_render = $tabview->render();
        $result = \Drupal::service('renderer')->render($tab_view_render);
 
  }
  
  foreach ($tabview->result as $key => $value) {
	$grouped_rows[$value->nid] = array();
  }
  //\Drupal::logger('grouprowsin')->notice(print_r($grouped_rows, TRUE));
  // Set a variable we can use in the view display to render the archive link
  $variables['archive_gid'] = $myasu_cb_archive_gid;
  //\Drupal::logger('myasu_cb_archive_gid')->notice(print_r($myasu_cb_archive_gid, TRUE));
  $group = Group::load(intval($myasu_cb_archive_gid));
 //\Drupal::logger('group')->notice('Group loaded: <pre>@group</pre>', ['@group' => print_r($group->toArray(), TRUE)]);
  //ksm($myasu_cb_archive_gid);
  
 
	if(!empty($group->field_myasu_cb_default_message[0])){
   		//$footer = !empty($group->field_myasu_cb_footer[0])?$group->myasu_cb_footer[0]->value:'';//$group_footer_block->get('field_myasu_cb_footer')->value;
      	$footer = (!empty($group->field_myasu_cb_footer) && isset($group->field_myasu_cb_footer[0]->value)) ? $group->field_myasu_cb_footer[0]->value : '';
		$group_footer = $footer;
        $default_message = isset($group->field_myasu_cb_default_message)?$group->field_myasu_cb_default_message[0]->value:'No posts found for this group.';
     
  }
  else{
      $default_message = '<p>'. t('No posts found for this group.') .'</p>';
      //$footer = "<a href="
      $group_footer = Link::fromTextAndUrl(t('View archive'), Url::fromUri("internal:/myasu-cb-archive/$myasu_cb_archive_gid", ['absolute' => TRUE]))->toString();
    
  }
  //ksm($variables->rows);
	//\Drupal::logger('viewresults')->notice(print_r($variables['rows'], TRUE));
  foreach ($variables['rows'] as $rowId => $row) {
   //ksm($rowId);
    foreach ($view->field as $fieldId => $field) {
     //ksm($fieldId);
      if($fieldId == "nid"){
       $field_output_value = $view->style_plugin->getFieldValue($rowId, $fieldId);
       
       $field_output_array[] =  $field_output_value ;
       
      }
    }
  }
 //\Drupal::logger('$field_output_arrayr')->notice('Year Value: @award', ['@award' => print_r($field_output_array, TRUE)]);
      
  if(!empty($field_output_array)){
  $field_output_unique = array_unique($field_output_array);
  //\Drupal::logger('$field_output_unique')->notice('Year Value: @award', ['@award' => print_r($field_output_unique, TRUE)]);
  foreach($field_output_unique as $key => $field_output){
      
      if(!empty($field_output)){
        $field_output_type_change = intval($field_output);
        $node =  Node::load($field_output_type_change);
		$htp = 'https://';
		$myasu_cb_image = '';
        if(!empty($node->field_myasu_cb_image)){
		 // if(!empty($node->get('field_myasu_cb_image')->target_id)){	
			$image_fc_id = $node->get('field_myasu_cb_image')->target_id;
			$file = File::load(intval($image_fc_id));
			$absolute_image_url = isset($file)?$file->getFileUri():'';
			//$sites_file_path = file_url_transform_relative(file_create_url($image_fc_block->field_myasu_cb_image->entity->getFileUri()));
			$sites_file_path_ori = \Drupal::service('file_url_generator')->transformRelative($absolute_image_url);
			$sites_file_path = str_replace('public://','/sites/default/files/',$sites_file_path_ori); 
            $full_file_path = $htp.$_SERVER['HTTP_HOST'].$sites_file_path;
			$database = \Drupal::database();
			$query = $database->select('node__field_myasu_cb_image', 'fci')->fields('fci', array('field_myasu_cb_image_alt'));
			//$query = db_query('select * from {asu_academic_plans}');
			$query->condition('field_myasu_cb_image_target_id',$image_fc_id,'=');
    		$alt_query = $query->execute()->fetchAll();
            //\Drupal::logger('$alt_query')->notice('alt_query: @award', ['@award' => print_r($alt_query[0], TRUE)]);
            if(!empty($alt_query)){
              $alt_value = $alt_query[0]->field_myasu_cb_image_alt;
            }
            else{
              $alt_value = '';
            }
            
            //$myasu_cb_image =  "<img src='$full_file_path' alt='$alt_value' title='' width='140' height='136' style='float:right; margin:0 0 10px 10px;'/>";
		    if(!empty($image_fc_id)){
				$myasu_cb_image = "<img src='$full_file_path' alt='$alt_value' title='' width='140' height='136' style='float:right; margin:0 0 10px 10px;'/>" ;
			}
			else{
				$myasu_cb_image = '';
			}

        }
        else{
            $myasu_cb_image = '';
          
        }
         $status = $node->isPublished();
         //ksm($myasu_cb_image);
		  
          //Tracking code
          //$tracking_code = isset($published_from_block->field_myasu_cb_tracking[0])?$published_from_block->field_myasu_cb_tracking[0]->value:''; -- not using
		  $tracking_code = isset($node->field_myasu_cb_tracking[0])?$node->field_myasu_cb_tracking[0]->value:'';
          $tracking_code_nid = $tracking_code.'-'.$field_output;
          
		  //Published from date
          //$published_from_time = strtotime($published_from_block->field_myasu_cb_publish_from[0]->value); --not using
          $published_from_time = strtotime($node->field_myasu_cb_publish_from->value);
		  
          //Published to date
          //$published_to_time = strtotime($published_from_block->field_myasu_cb_publish_to[0]->value);
		  $published_to_time = strtotime($node->field_myasu_cb_publish_to->value);
          
          //get current time
          $current_time = time();
          
		  //\Drupal::logger('print')->notice('printing 1');
          //Return values if published from date is less than now and published to time greater than now
          //if(($published_from_time <= $current_time) && ($published_to_time >= $current_time )){
          
		  if($published_to_time >= $current_time ){
         
            //Get tab content nid from the view
            //$tab_nid = isset($fcArray['field_myasu_cb_tab'])?$fcArray['field_myasu_cb_tab']:''; -- not using
            $tab_nid = $node->field_myasu_cb_tab[0]->target_id;
           
            
            //\Drupal::logger('tabnid')->notice(print_r($tab_nid, TRUE));
            if(!empty($tab_nid)){
             
              $variables['tabs'][$tab_nid] = Node::load($tab_nid);
              $tab_footer = Node::load($tab_nid)->get('field_myasu_cb_tab_footer')->value;
              $tab_weight = Node::load($tab_nid)->get('field_weight')->value;
              $tab_title = Node::load($tab_nid)->get('title')->value;
              $new_tab_array[$tab_weight][$tab_title] = $tab_nid;
              
              if(isset($tab_footer)){
                $footer_value = " <div style='clear:both;'></div><div class='myasu-cb-tab-footer' style='background-color:whiteSmoke; padding: 0px 4px 4px 4px; text-align: left; border-top:2px solid #fff;'>$tab_footer</div>";
              }
              else{
                $footer_value = $group_footer;
              }
              $title = $node->get('title')->value;
              //Get post content node body value of matching tab 
              if(!empty($node->get('body')->value)){
				  //ksm($myasu_cb_image);
                  $node_before_fixing = $node->get('body')->value;
                  $node_body_after_fixing = asu_cb_views_rewrite_links($node_before_fixing,$tracking_code_nid);
                 
                  $body_value = "<div class='myasu-cb-content' style='max-height:155px; overflow:auto; padding:5px 8px;'><div class='myasu-cb'><div class='myasu-cb-inner'><h2 class='myasu-cb-title'>$title</h2><div class='myasu-cb-content'>".$myasu_cb_image.$node_body_after_fixing."</div></div></div></div>";
				  
              }
              else{
                 $body_value = '';
              }
              $grouped_rows[$tab_nid][$field_output] = $body_value;
              
            }
          }
        
      }
     
    }
    
  }  

  
  //Unset empty grouped_row variables
  if(!empty($grouped_rows)){
    foreach ($grouped_rows as $key => $rows) {
      //dpm($rows);
      if (!count($rows) && $empty_text) {
        $grouped_rows[$key][] = '<div>'. $empty_text .'</div>';
      }
      else if (!count($rows)) {
        unset($grouped_rows[$key]);
      }
    }
  }
  //dpm($grouped_rows);
 if(!empty($new_tab_array)){
   ksort($new_tab_array);
   $variables['sorted_tabs'] = $new_tab_array;
 }
  
  //\Drupal::logger('grouprows 1')->notice(print_r($grouped_rows, TRUE)); 
 
  $variables['grouped_rows'] = isset($grouped_rows)?$grouped_rows:'';
  
  $variables['myasu_cb_default_message'] = $default_message;
  $variables['myasu_cb_footer'] = "<div class='myasu-cb-tab-footer' style='background-color:whiteSmoke; padding: 4px 4px 4px 8px; text-align: left; border-top:2px solid #fff;'>$group_footer</div>";
  $variables['tab_footer_div_begin'] = "<div class='myasu-cb-tab-footer' style='background-color:whiteSmoke; padding: 4px 4px 4px 8px; text-align: left; border-top:2px solid #fff;'>";
  
  $variables['tab_footer_div_end'] = "</div>";
}

/**
 * Copy of http://drupal.org/files/issues/drupal_force_absolute_urls_2.patch
 * from http://drupal.org/node/88183
 */
function asu_cb_views_rewrite_links($html, $node = NULL) {
  global $base_url, $myasu_cb_views_curr_node;
  $myasu_cb_views_curr_node = $node;
  $base_document = $base_url;
  $base_document_root = (preg_match('%(\w+://.*?)/%', $base_document, $matches)>0) ? $matches[1] : trim($base_document, '/');
  $base_document = substr($base_document, 0, strrpos($base_document,'/'));
  
  // Make all URLs absolute
  $src = array(
    '/(\<[^\>]*? href| src)="(?!\w+:\/\/)([^\/][^"]*)"/i', 
    '/(\<[^\>]*? href| src)="(?!\w+:\/\/)\/?([^"]*)"/i'
  );
  $dst = array(
    '$1="'. $base_document .'/$2"',
    '$1="'. $base_document_root .'/$2"'
  );
  $html = preg_replace($src, $dst, $html);
  
  // From elf.module
  // find all <a> tags and match their href so we can test if the link is external or not
  $html = preg_replace_callback('!<a.*?href="([^"]+)".*?>!', 'asu_cb_views_rewrite_links_op', $html);
  return $html;
}


/**
 * Replace and add class and rel attributes.
 * This is mostly taken from elf.module
 */
function asu_cb_views_rewrite_links_op($match) {
  global $myasu_cb_views_curr_node;
  $node_tracking_code = $myasu_cb_views_curr_node;
  //dpm($match);
  $link = $match[1];
  
  // Add the class attribute if it doesn't exist
  if (strpos($link, 'class="') === FALSE) {
    //$link = substr($link, 0, -1);
    
    $link .= ' *class="tracking"';
  }
  else {
    // append tracking class if this <a> already has a class
    $link = preg_replace('!class"([^"]+)"!', 'class="${1} tracking"', $link);
  }
 // dpm($link, 'link1');
  // Add the rel attribute
  
  $code_nid = explode('-',$node_tracking_code);
  $code = $code_nid[0];
  $nid = $code_nid[1];
  
  $rel = $code ? preg_replace('/[^a-zA-Z0-9-]+/', '-', $code) : 'node-'. $nid;
  
  if (strpos($match[1], 'rel="') === FALSE) {
    
    //$link = substr($link, 0, -1);
    $link .= ' rel="'. $rel .'">';
  }
  else {
    // append tracking class if this <a> already has a class
    $link = preg_replace('!class"([^"]+)"!', 'class="${1} '. $rel .'"', $link);
  }
 // dpm($link, 'link2');
  $link_append = $link;
  $extract_link = explode(" *", $link_append);
  $first_part = $extract_link[0];
  $second_part = $extract_link[1];
  //$updated_link = "a href='".$first_part."'".$second_part;
  $updated_link = 'a href="'.$first_part.'" '.$second_part;
  return "<".$updated_link;
}