<?php

namespace Drupal\asu_components\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Function to overwrite the dynamic values in user routes.
 */
class AsuController extends ControllerBase {

  /**
   * newparagraph
   */
 

  public function newparagraph() {

    // $paragraph = Paragraph::create([
    //   'type' => 'paragraph_body',   // paragraph type machine name
    //   'field_paragraph_body' => [   // paragraph's field machine name
    //       'value' => 'test',                  // body field value
    //       'format' => 'wysiwyg',         // body text format
    //   ],
    // ]);
  
    // $paragraph->save();

    // $nodeData = [
    //   'type' => 'page',
    //   'status' => 1,
    //   'title' => 'My new page',
    //   'field_paragraphs' => [  // paragraph field attached to node
    //       [
    //           'target_id' => $paragraph->id(),
    //           'target_revision_id' => $paragraph->getRevisionId(),
    //       ],
    //   ],
    // ];

    // $entity = Node::create($nodeData);
    // $entity->save();

    // print_r($paragraph->id());
    // print_r($entity->id());
    // dsm('hi');
    // dpm('hello');
    

      $img1 = 'https://www.asu.edu/sites/default/files/2022-08/AE-pillar-RichaTeaching.jpg';
      $img11 = 'background: url(https://www.asu.edu/sites/default/files/2022-08/AE-pillar-RichaTeaching.jpg);;';

      $output = '<div class="block block-layout-builder block-inline-blockasu-edu-anim-content-buttons">


      <div class="animated-content-section my-5 text-white">';
        
          $output .='<div class="content-section my-2" style="padding">';
          $output .='<div class="image-holder"></div>
          <div class="content-holder px-4">
            <h2>Experience world-class academics</h2>
            <div class="hidden-details">
              <div class="long-text mt-1 mb-3">
                <p>As a comprehensive public research university, ASU is committed to providing excellence in education
                  through the Academic Enterprise, and enables the success of each unique student and increases access to
                  higher education for all.</p>
              </div>
    
              <div class="link-area mb-3">
              </div>
    
              <div class="tags-area mb-3">
              </div>
    
              <div class="button-area">
                <!-- START INSERT: Button Component -->
                <a href="https://www.asu.edu/academics" class="btn btn-md btn-gold" role="link"
                  data-ga-animated-content-section-section="experience world-class academics"
                  data-ga-animated-content-section="learn more">
                  Learn more
                </a>
    
                <!-- END INSERT: Button Component -->
              </div>
    
            </div>
          </div>
        </div>
        <div class="content-section my-2"
          style="background-image: url($img1)">
          <div class="image-holder"></div>
          <div class="content-holder px-4">
            <h2>Discovery and innovation that serves the public</h2>
            <div class="hidden-details">
              <div class="long-text mt-1 mb-3">
                <p>As ASU focuses on research and discovery of public value, the Knowledge Enterprise advances research,
                  innovation, strategic partnerships, entrepreneurship, technology transfer and international development.
                </p>
              </div>
    
              <div class="link-area mb-3">
              </div>
    
              <div class="tags-area mb-3">
              </div>
    
              <div class="button-area">
                <!-- START INSERT: Button Component -->
                <a href="http://asu.edu/research" class="btn btn-md btn-gold" role="link"
                  data-ga-animated-content-section-section="discovery and innovation that serves the public"
                  data-ga-animated-content-section="learn more">
                  Learn more
                </a>
    
                <!-- END INSERT: Button Component -->
              </div>
    
            </div>
          </div>
        </div>
        <div class="content-section my-2"
          style="background-image: url($img1)">
          <div class="image-holder"></div>
          <div class="content-holder px-4">
            <h2>Serving all learners at every stage of life</h2>
            <div class="hidden-details">
              <div class="long-text mt-1 mb-3">
                <p>Assuming fundamental responsibility for the communities it serves, ASUs Learning Enterprise aims to
                  serve all learners at every stage of life by providing high quality, accessible and affordable learning
                  opportunities to everyone.</p>
              </div>
    
              <div class="link-area mb-3">
              </div>
    
              <div class="tags-area mb-3">
              </div>
    
              <div class="button-area">
                <!-- START INSERT: Button Component -->
                <a href="https://learning.asu.edu/" class="btn btn-md btn-gold" role="link"
                  data-ga-animated-content-section-section="serving all learners at every stage of life"
                  data-ga-animated-content-section="learn more">
                  Learn more
                </a>
    
                <!-- END INSERT: Button Component -->
              </div>
    
            </div>
          </div>
        </div>
      </div>
    </div>';  
  

























      

    return array(
      '#type' => 'markup',
      '#markup' => $output,
    );

  }

  public function overview() {

  }


}



