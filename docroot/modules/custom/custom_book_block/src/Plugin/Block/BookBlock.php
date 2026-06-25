<?php

namespace Drupal\custom_book_block\Plugin\Block;

use Drupal\book\BookManagerInterface;
use Drupal\book\Plugin\Block\BookNavigationBlock;
// use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Provides a custom 'Book navigation' block.
 *
 * @Block(
 *   id = "book_chapters",
 *   admin_label = @Translation("Book with chapters"),
 *   category = @Translation("Menus")
 * )
 */
class BookBlock extends BookNavigationBlock {

 
  /**
   * {@inheritdoc}
   */
  public function build() {
    
        $node = \Drupal::routeMatch()->getParameter('node');
        if ($node instanceof \Drupal\node\NodeInterface) {
            // You can get nid and anything else you need from the node object.
            $bid =$nid = $node->id();
            // $books = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'book']); 


            $database = \Drupal::database();
            $query = $database->select('book', 'u');            
            // Add extra detail to this query object: a condition, fields and a range.
            $query->condition('u.nid', $nid);
            //$query->condition('u.depth', $nid);
            $query->fields('u', ['bid']);
            $query->orderBy('u.weight');
            $result = $query->execute()->fetchAssoc();
            if($result){
                $bid = $result['bid'];
            }
            
          
            $query = $database->select('book', 'u');            
            // Add extra detail to this query object: a condition, fields and a range.
            $query->condition('u.bid', $bid);
            //$query->condition('u.depth', $nid);
            $query->fields('u', ['bid', 'nid', 'pid', 'has_children', 'depth','weight']);
            $query->orderBy('u.weight');
            $result = $query->execute();
           
            $node_storage = \Drupal::entityTypeManager()->getStorage('node');

           // var_dump($result);
            $bookmenu =[];
            $bookmenu_child =[];
            foreach ($result as $record) {
                if($record->pid != 0){      
                    $class = "";   
                    if($record->nid == $nid){
                        $class = "is-active"; 
                    }                 
                    if($record->depth == 2){
                        $bookmenu[$record->nid] = [
                            'nid' =>   $record->nid,
                            'title'  =>  $node_storage->load($record->nid)->get('title')->value, 
                            'link' => \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $record->nid, 'und'),
                            'class' => $class,         
                        ];
                       
                    }
                    elseif($record->depth == 3) {                       
                        $bookmenu_child[$record->nid] = [
                            'pid' =>   $record->pid,
                            'title'  =>  $node_storage->load($record->nid)->get('title')->value,  
                            'link' => \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $record->nid, 'und'),
                            'class' => $class,         

                        ];

                       
                    }

                }
            
            }
        }
        return [
            '#theme' => 'ws_custom_block',
            '#books' => $bookmenu,
            '#child' => $bookmenu_child,
          ];
       
  }

}
