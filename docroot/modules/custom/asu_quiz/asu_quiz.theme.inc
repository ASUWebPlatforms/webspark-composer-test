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
use Drupal\Core\Entity\EntityStorageInterface;




/**
 * template node
 */
/*function asu_quiz_preprocess_template(&$variable) {
    \Drupal::logger('hello');
    if ($variable['node']->getType() === 'blog_posts') {
        $variable['my_title'] = $variable['node']->field_my_title->value;
       // \Drupal::logger('fcid')->notice(print_r($variable['my_title'], TRUE));


    }

}*/