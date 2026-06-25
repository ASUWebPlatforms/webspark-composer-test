<?php
/**
 * @file
 * Contains \Drupal\asuaec_transferoption\Controller\TransferOptionNodeCreationController.
 */
namespace Drupal\asuaec_azscholarship\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;


class MunicipalityBlockContentController3Column extends ControllerBase
{
    /**
     * Called from Block plugin (municipalityBlock.php).
     * Build content of the municipality block.
     */
    public function process(Request $request = null, string $citynid = '104') { // The node/104 is Default node.

        if(is_null($citynid)) {
            $output = '';
            return array(
                '#markup' => \Drupal\Core\Render\Markup::create($output),
                '#cache' => array( // Turn off cache.
                    'max-age' => 0,
                ),
            );
        }

        // Load the node and get info such as Block id (3 column), city display name and H1 title.
        $city_node = Node::load($citynid);
        if(is_null($city_node)) {
            $output = '';
            return array(
                '#markup' => \Drupal\Core\Render\Markup::create($output),
                '#cache' => array( // Turn off cache.
                    'max-age' => 0,
                ),
            );
        }

        // getType
        $content_type = $city_node->getType();
        if($content_type != 'az_scholarship') {
            $output = '';
            return array(
                '#markup' => \Drupal\Core\Render\Markup::create($output),
                '#cache' => array( // Turn off cache.
                    'max-age' => 0,
                ),
            );
        }

        $first_block_id = $city_node->hasField('field_1st_block_id') &&
                            sizeof($city_node->get('field_1st_block_id')) > 0 ?
                            $city_node->get('field_1st_block_id')[0]->getValue()['value'] : '';
//        ksm($first_block_id, "first_block_id");
        if($first_block_id != ''){
            $bid = $first_block_id;
            $block = \Drupal\block_content\Entity\BlockContent::load($bid);

// Changed on 8/28/2025 because it was throwing Php warning.
//            $render = \Drupal::entityTypeManager()->
//            getViewBuilder('block_content')->view($block);
////        ksm($render, "render");
//            return $render;

            if ($block) {
              $content = \Drupal::entityTypeManager()
                ->getViewBuilder('block_content')
                ->view($block, 'full');

              return [
                '#type' => 'container',
                '#attributes' => ['class' => ['municipality-3col']],
                'content' => $content,
                '#cache' => [
                  'contexts' => ['url.query_args:citynid'],
                ],
              ];
            }

        } else {
            $output = '';
            return array(
                '#markup' => \Drupal\Core\Render\Markup::create($output),
                '#cache' => array( // Turn off cache.
                    'max-age' => 0,
                ),
            );
        }

    } // END OF public function process()





} // END OF class MunicipalityBlockContentController3Column


