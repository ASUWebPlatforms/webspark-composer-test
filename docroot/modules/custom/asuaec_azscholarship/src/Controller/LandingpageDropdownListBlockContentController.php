<?php
/**
 * @file
 * Contains \Drupal\asuaec_transferoption\Controller\TransferOptionNodeCreationController.
 */
namespace Drupal\asuaec_azscholarship\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;


class LandingpageDropdownListBlockContentController extends ControllerBase
{
    /**
     * Called from Block plugin (municipalityBlock.php).
     * Build content of the municipality block.
     */
    public function process(Request $request = null) { // The node/104 is Default node.

        // DB access
        $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
        $ids = $nodeStorage->getQuery()
            ->condition('type', 'az_scholarship') // type = bundle id (machine name)
            ->condition('field_public_private', 'public', '=')
            ->accessCheck(TRUE) // Explicitly check access for Drupal 10
            ->execute();
        $az_scholarship_nodes = $nodeStorage->loadMultiple($ids);

        // Iterate all AZ scholarhip nodes. Get municipality display name. Build option html for <select>
        $az_scholarship_node_array = [];
        foreach ($az_scholarship_nodes as $nid => $node) {
            $field_city_display_name = $node->get('field_city_display_name')->getValue()[0]['value'];
            $az_scholarship_node_array[$nid] = $field_city_display_name;
        }
        asort($az_scholarship_node_array);
        $option_html = '';
        foreach($az_scholarship_node_array as $nid => $city_display_name) {
            $option_html .= '<option value="' . $nid . '">' . $az_scholarship_node_array[$nid] . '</option>';
        }


        $output = <<<EOD
<div class="container">
  <div class="row">
    <div id="html-root" class="col-12">
      <div>
        <div class="uds-card-arrangement">
          <div class="uds-card-arrangement-card-container">
            <div class="card null card-horizontal">
              <img class="card-img-top" src="/sites/default/files/2023-10/find_out_more_500px.jpg" alt="Find out more" width="600" height="337" loading="lazy" decoding="async" fetchpriority="high">
              <div class="card-content-wrapper">
                <div class="card-header">
                  <h3 class="card-title"><a href="#">Find out more</a></h3>
                </div>
                <div class="card-body">
                  <p class="card-text">Select your employer to view details about the Public Employee Scholarship scholarship opportunity available to you.</p>
                  <div id="div-cities-2pages">
                    <select id="cities-2pages" class="form-control" >
                      <option value="" selected="selected">- Select Municipality -</option>
                      {$option_html}
                    </select>
                  </div> <!-- #div-cities-2pages -->
                </div>
              </div>
            </div>
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

} // END OF class LandingpageDropdownListBlockContentController


