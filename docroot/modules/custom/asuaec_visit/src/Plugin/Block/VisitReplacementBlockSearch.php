<?php

namespace Drupal\asuaec_visit\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Visit replacement block - Search.
 *
 * @Block(
 *   id = "visit_replacement_block_search",
 *   admin_label = @Translation("Search - Visit Replacement Block")
 * )
 */
class VisitReplacementBlockSearch extends BlockBase {

  /**
   * Build custom block.
   */
  public function build() {

    $html = <<<HTML
<!-- /schedule page -->
<div class="row">
  <div class="col-12">
    <section class="uds-image-background-with-cta" style="background-image: url('/sites/default/files/2023-05/visit-asu.jpeg')">
      <div class="uds-image-background-with-cta-container uds-content-align">
        <div class="container">
          <h1>Come Experience ASU</h1>
          
          <div id="wrapper-dropdowns" class="row"  style="background: rgba(0, 0, 0, 0.3);padding: 16px;">
              
              <!-- Visitor type -->
              <div class="sec1 col col-12 col-lg-4 mb-1 mb-lg-0">
                <select class="colselect custom-select form-select form-control col-12" id="persontype" name="persontype" data-ga-input="select" data-ga-input-name="onclick" data-ga-input-event="select" data-ga-input-action="click" data-ga-input-region="main content">
                  <option selected="selected" value="0">I am a ...</option>
                  <option value="High school freshman">a high school freshman</option>
                  <option value="High school sophomore">a high school sophomore</option>
                  <option value="High school junior">a high school junior</option>
                  <option value="High school senior">a high school senior</option>
                  <option value="College transfer">in college and thinking of transferring to ASU</option>
                  <option value="Graduate student">considering graduate school (Masters, PhD, EdD, DNP, etc.)</option>
                  <option value="Other">a high school counselor, teacher, or community leader</option>
                  <option value="/groupvisit">Looking to schedule a large school group tour</option>
                </select>
              </div>


              <div class="sec2 col col-12 col-lg-4 mb-1 mb-lg-0">
                <select class="colselect custom-select form-select form-control col-12" id="interest" name="interest" data-ga-input="select" data-ga-input-name="onclick" data-ga-input-event="select" data-ga-input-action="click" data-ga-input-region="main content">
                  <option selected="selected" value="">I want to study...</option>
                </select>
              </div>

              <!-- Visit bucket -->
              <div class="sec3 col col-12 col-lg-4 mb-1 mb-lg-0">
                <select class="colselect custom-select form-select form-control col-12" id="interest-ugrad" name="interest-ugrad" data-ga-input="select" data-ga-input-name="onclick" data-ga-input-event="select" data-ga-input-action="click" data-ga-input-region="main content">
                  <option selected="selected" value="0">I want to study...</option>
                  <option value="25">Anthropology, Sociology and Cultural Studies</option>
                  <option value="26">Architecture, Construction and Design</option>
                  <option value="27">Business</option>
                  <option value="28">Communication and Languages</option>
                  <option value="29">Computer Science, Software Engineering and Mathematics</option>
                  <option value="30">Criminology and Forensics</option>
                  <option value="31">Earth, Space and Flight</option>
                  <option value="32">Education and Teaching</option>
                  <option value="33">Engineering</option>
                  <option value="72">Fashion</option>
                  <option value="73">Film and Media</option>
                  <option value="35">Fine Arts and Performance</option>
                  <option value="76">Global Management and Leadership</option>
                  <option value="36">Health and Wellness</option>
                  <option value="37">History, Philosophy and Humanities</option>
                  <option value="38">Journalism</option>
                  <option value="39">Nursing</option>
                  <option value="43">Public Service and Political Science</option>
                  <option value="40">Pre-health</option>
                  <option value="41">Pre-law</option>
                  <option value="42">Psychology</option>
                  <option value="44">Science</option>
                  <option value="45">Sports, Tourism and Recreation</option>
                  <option value="46">Sustainability</option>
                  <option value="34">Undecided/Exploratory/Many Interests</option>
                </select>
              </div>

              <!-- ASU Degree Search category -->
              <div class="sec4 col col-12 col-lg-4 mb-1 mb-lg-0">
                <select class="colselect custom-select form-select form-control col-12" id="interest-grad" name="interest-grad" data-ga-input="select" data-ga-input-name="onclick" data-ga-input-event="select" data-ga-input-action="click" data-ga-input-region="main content">
                  <option selected="selected" value="0">I want to study...</option>
                  <option value="Architecture and Construction">Architecture and Construction</option>
                  <option value="Arts">Arts</option>
                  <option value="Business">Business</option>
                  <option value="Communication and Media">Communication and Media</option>
                  <option value="Computing and Mathematics">Computing and Mathematics</option>
                  <option value="Education and Teaching">Education and Teaching</option>
                  <option value="Engineering and Technology">Engineering and Technology</option>
                  <option value="Entrepreneurship">Entrepreneurships</option>
                  <option value="Health and Wellness">Health and Wellness</option>
                  <option value="Humanities">Humanities</option>
                  <option value="Interdisciplinary Studies">Interdisciplinary Studies</option>
                  <option value="Law, Justice and Public Service">Law, Justice and Public Service</option>
                  <!--<option value="Psychology">Psychology</option>-->
                  <option value="Science">Science</option>
                  <option value="Social and Behavioral Sciences">Social and Behavioral Sciences</option>
                  <option value="Sustainability">Sustainability</option>
                  <option value="STEM">STEM</option>
                </select>
              </div>

              <!-- Month -->
              <div class="sec5 col col-12 col-lg-2 mb-1 mb-lg-0">
                <select class="colselect custom-select form-select form-control col-12" id="month" name="month" data-ga-input="select" data-ga-input-name="onclick" data-ga-input-event="select" data-ga-input-action="click" data-ga-input-region="main content">
                  <option selected="selected" value="0">I want to visit in...</option>
                  <option value="202305">May 2023</option>
                  <option value="202306">June 2023</option>
                </select>
              </div>


              <button class="btn btn-gold" id="search" name="search">Search</button>     
              
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
HTML;

    return [
      '#type' => 'inline_template',
      '#template' => $html,
      '#cache' => ['max-age' => 0],
    ];
  }

}
