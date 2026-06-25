<?php

namespace Drupal\svyreport\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Render\Markup;

/**
 * Provides a Surveys Info Block for Surveys main page
 *
 * @Block(
 *   id = "surveys_block",
 *   admin_label = @Translation("Surveys Info Block"),
 *   category = @Translation("Surveys Info Block"),
 * )
 */
class SurveysBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {


    $queries = array(
      'xref' => "SELECT * FROM XREF Order by `Order`;",
    );

    $surveysblock = array(
      'surveys' => file_get_contents("public://reports/siteassets/surveys/Analytics_Surveys.json"),
     ) ;
     
    foreach ($queries as $key => $q) {
      $surveysblock[$key] = \Drupal::database()->query($q)->fetchAll() ;
    };

    $settings = array ( "surveysblock" => $surveysblock ) ;
    return array(
      '#attached' =>
          array(
            'library' => array('svyreport/svyreport-app' ),
            'drupalSettings' =>  $settings
          ),
      '#type' => 'markup',
      '#markup' => Markup::create('<div id="svyreport-wrapper"></div>')
    );
  }
}
