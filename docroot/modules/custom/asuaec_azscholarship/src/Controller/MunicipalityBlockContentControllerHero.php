<?php
/**
 * @file
 * Contains \Drupal\asuaec_transferoption\Controller\TransferOptionNodeCreationController.
 */
namespace Drupal\asuaec_azscholarship\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;
use Drupal\media\Entity\Media;


class MunicipalityBlockContentControllerHero extends ControllerBase
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


        $h1_title = $city_node->hasField('field_h1_title') &&
                    sizeof($city_node->get('field_h1_title')) > 0 ?
                    $city_node->get('field_h1_title')[0]->getValue()['value'] : '';

        // Get logo
        $domain = 'https://' . $_SERVER['HTTP_HOST'];
        $logo_image_id = $city_node->hasField('field_white_logo_image') &&
                sizeof($city_node->get('field_white_logo_image')) > 0 ?
                $city_node->get('field_white_logo_image')[0]->getValue()['target_id'] : '';

        $path_logo = '';
        $alt_logo = '';
        if($logo_image_id != '') {
            $media_logo = Media::load($logo_image_id);
            $alt_logo = $media_logo->field_media_image->alt;
            $fid_logo = $media_logo->field_media_image->target_id;
            $file_logo = File::load($fid_logo);
            $uri_logo = $file_logo->getFileUri();
            $path_logo = $domain . '/sites/default/files/' . substr($uri_logo, 9); // Remove "public://"
        }

        // Get logo shape
        $logo_shape = $city_node->hasField('field_logo_shape') &&
            sizeof($city_node->get('field_logo_shape')) > 0 ?
            $city_node->get('field_logo_shape')[0]->getValue()['value'] : '';

        // Get Hero image
        $hero_image_id = $city_node->hasField('field_bg_image') &&
        sizeof($city_node->get('field_bg_image')) > 0 ?
            $city_node->get('field_bg_image')[0]->getValue()['target_id'] : '';
        $path_hero = '';
        $alt_hero = '';
        if($hero_image_id != '') {
            $media_hero = Media::load($hero_image_id);
            $alt_hero = $media_hero->field_media_image->alt;
            $fid_hero = $media_hero->field_media_image->target_id;
            $file_hero = File::load($fid_hero);
            $uri_hero = $file_hero->getFileUri();
            $path_hero = $domain . '/sites/default/files/' . substr($uri_hero,9); // Remove "public://"
        }

        $output = <<<EOD
<style>
.municipality-logo {
    margin: 30px;
    position: relative;
}
.municipality-logo.vertical {
    max-height: 150px;
}
.municipality-logo.square {
    max-height: 120px;
}
.municipality-logo.horizontal {
    max-height: 120px;
}
@media screen and (max-width: 767px) {
    .municipality-logo {
        max-width: 270px;
    }
    .municipality-logo.square,
    .municipality-logo.vertical
    {
        max-height: 80px;
    }
}
@media screen and (min-width: 578px) and (max-width: 767px) {
    #story--molecules-heroes-templates--hero-medium h1 {
        font-size: 2.4rem;
    }
}
</style>
<div id="story--molecules-heroes-templates--hero-medium">
  <div>
    <div>
      <div class="row g-0">
        <div id="html-root" class="col uds-full-width">
          <div class="uds-hero-md has-btn-row">
            <div class="hero-overlay">
            </div>
            <img class="hero" src="{$path_hero}" alt="{$alt_hero}" width="2560" height="512" loading="lazy" decoding="async" fetchpriority="high">
            <h1>
                <img class="municipality-logo {$logo_shape}" src="{$path_logo}" alt="{$alt_logo}" /><br />
                <span class="highlight-white">{$h1_title}</span>
            </h1>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
EOD;

        return array(
            '#markup' => \Drupal\Core\Render\Markup::create($output),
            '#cache' => array( // Turn off cache.
                'max-age' => 0,
            ),
        );

    } // END OF public function process()

} // END OF class MunicipalityBlockContentControllerHero


