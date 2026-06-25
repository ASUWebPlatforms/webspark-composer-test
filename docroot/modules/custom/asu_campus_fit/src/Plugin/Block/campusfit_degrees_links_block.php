<?php

namespace Drupal\asu_campus_fit\Plugin\Block;

use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Entity\Webform;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Render\Markup;
use Drupal\taxonomy\Entity\Term;

/**
 * @file
 * Contains \Drupal\asu_campus_fit\Plugin\Block\campusfit_degrees_links_block.
 */

/**
 * Provides a campus results block.
 *
 * @Block(
 *   id = "campusfit_degrees_links_block",
 *   admin_label = @Translation("Campusfit degree links block on the results from the module"),
 *
 * )
 */
class campusfit_degrees_links_block extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Return $account->hasPermission('search content');.
    if (AccessResult::allowedIfHasPermission($account, 'access content')) {
      return AccessResult::allowedIfHasPermission($account, 'access content');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /*$sid_val = !empty(\Drupal::request()->query->get('sid'))?\Drupal::request()->query->get('sid'):'';
    $controller_variable = new CampusFitOtherCampuses;
    $rendering_in_block = $controller_variable->other_campuses_confirm_page($sid_val);
    return $rendering_in_block; */
    $healthLaw = FALSE;
    $request = \Drupal::service('request_stack')->getCurrentRequest();
    $sid_val = $request->query->get('sid') ?? ($_SESSION['confSid'] ?? '');
    $degreeType = '';
    $degreeDescr = '';
    if (!empty($sid_val)) {
      $webform = Webform::load('campus_fit');
      if ($webform->hasSubmissions()) {
        $query = \Drupal::entityQuery('webform_submission')
          ->condition('webform_id', 'campus_fit')
          ->condition('sid', intval($sid_val))
          ->accessCheck(FALSE);
        $result = $query->execute();
        $submission_data = [];
        foreach ($result as $item) {
          $submission = WebformSubmission::load($item);
          $submission_data = $submission->getData();
        }
      }
      // \Drupal::logger('submission data')->info('<pre>' . print_r($submission_data, TRUE) . '</pre>');
      $online = $submission_data['do_you_or_will_you_live_in_any_of_these_cities_'] ? 'ONLINE' : '';
      // \Drupal::logger('online data')->info('<pre>' . print_r($online, TRUE) . '</pre>');
      $get_nid_data = \Drupal::service('getJsSettings')->getJsSettings($submission_data);
      // Interest data.
      if (!empty($online)) {
        $campus = htmlspecialchars($online, ENT_QUOTES, 'UTF-8');
      }
      else {
        $campus = $_SESSION['ajaxCampus'] ?? $_SESSION['ajaxCampus'] ?? $get_nid_data['top_campus'];
      }

      // \Drupal::logger('top campus')->info('<pre>' . print_r($campus, TRUE) . '</pre>');
      $undergrad_interest = htmlspecialchars($submission_data['would_you_rather_study'] ?? '', ENT_QUOTES, 'UTF-8');
      $grad_interest = htmlspecialchars($submission_data['would_you_rather_study_grad'] ?? '', ENT_QUOTES, 'UTF-8');
      $asulocal_interest = htmlspecialchars($submission_data['what_s_your_top_interest_area_'] ?? '', ENT_QUOTES, 'UTF-8');
      $asulocal_online_interest = htmlspecialchars($submission_data['what_s_your_top_interest_area_online'] ?? '', ENT_QUOTES, 'UTF-8');
      $type_degree = htmlspecialchars($submission_data['asu_can_help_you_achieve_your_academic_goals_no_matter_what_they'], ENT_QUOTES, 'UTF-8');
      // if(!empty($undergrad_interest)){
      if (strpos($type_degree, 'bachelor') !== FALSE) {
        $degreeType = "undergrad";
      }
      // if(!empty($grad_interest)){
      if (strpos($type_degree, 'advanced') !== FALSE) {
        $degreeType = "graduate";
      }

      // Get interest value.
      $final_interest = '';
      foreach (
      [
        $undergrad_interest,
        $grad_interest,
        $asulocal_interest,
        $asulocal_online_interest,
      ] as $interest
      ) {
        if (!empty($interest)) {
          $final_interest = $interest;
          break;
        }
      }

      $pos = strrpos($final_interest, '-');
      if ($pos !== FALSE) {
        $trimmedInterest = rtrim(substr($final_interest, 0, $pos));
      }
      else {
        // No '-' found, return original.
        $trimmedInterest = rtrim($final_interest);
      }

      if($trimmedInterest == "Exploratory"){
        $interestLabel = "Undecided / Exploratory";
      }
      elseif($trimmedInterest == "Pre health"){
        $interestLabel = "Pre-health";
      }
      elseif($trimmedInterest == "Pre law"){
        $interestLabel = "Pre-law";
      }
      else{
        $interestLabel = $trimmedInterest;
      }
      // dpm($trimmedInterest);
      if ($trimmedInterest == 'Pre health') {
        $degreeDescr = "<h3>Pre health</h3><p>Students can major in any program at ASU and still be considered a pre-health student with advisor support to help you pursue a career in medicine. There is not a \"pre-health\" major at ASU, so please select a major that interests you. See <a href='https://prehealth.asu.edu/'>prehealth.asu.edu</a></p>";
        $healthLaw = TRUE;
      }
      elseif ($trimmedInterest == 'Pre law') {
        $degreeDescr = "<h3>Pre law</h3><p>Prelaw advising supports all current students interested in pursuing a career in law and the application to law school. Students can major in any degree and still be considered a pre-law student. See <a href='https://prelaw.asu.edu'>prelaw.asu.edu</a></p>";
        $healthLaw = TRUE;
      }
      else {
        $degreeDescr = "<p>$interestLabel related degrees on the $campus campus</p>";
      }
      $client = \Drupal::service('http_client_factory')->fromOptions();
      $domain = $_SERVER['HTTP_HOST'];
      try {
        $top_10_degrees = [];
        $apiUrl = "https://$domain/campusfit/degrees/$degreeType/$trimmedInterest/$campus";
        \Drupal::logger('$apiUrl')->info('<pre>' . print_r($apiUrl, TRUE) . '</pre>');
        $response = $client->get($apiUrl);
        $degreedata = json_decode($response->getBody()->getContents(), TRUE);
        // dpm($degreedata);
        // \Drupal::logger('$degreedatadata')->info('<pre>' . print_r($degreedata, TRUE) . '</pre>');
        // $top_10_degrees = array_slice($degreedata, 0, 6);.
        $top_10_degrees = $degreedata['topDegrees'] ?? [];
        $moreDegrees = !empty($degreedata['moreDegreesLinks']) ? $degreedata['moreDegreesLinks'] : '';
        // \Drupal::logger('$moreDegreesdatadiv')->info('<pre>' . print_r($moreDegrees, TRUE) . '</pre>');
        $allDegrees = !empty($degreedata['allDegreesLinks']) ? $degreedata['allDegreesLinks'] : '';
        // \Drupal::logger('alldegrees')->info('<pre>' . print_r($allDegrees, TRUE) . '</pre>');
        $noSpaceInterest = trim($trimmedInterest);
        if ($top_10_degrees && is_array($top_10_degrees)) {
          $size = sizeof($top_10_degrees);
        }
        else {
          $size = 0;
        }
        // \Drupal::logger('degrees size')->info('<pre>' . print_r($size, TRUE) . '</pre>');
        if ($size <= 6) {
          $tax_id = $degreedata['campusTid'] ?? '';
          // \Drupal::logger('taxo_id')->info('<pre>' . print_r($tax_id, TRUE) . '</pre>');
          // Load the taxonomy term
          // Replace with your term ID
          $term = Term::load($tax_id);
          if ($term && !$term->get('field_category_images')->isEmpty()) {
            // Get the first image field item.
            $media = $term->get('field_category_images')->first()->entity;
            // \Drupal::logger('image_item')->info('<pre>' . print_r($media, TRUE) . '</pre>');
            if ($media && $media->hasField('field_media_image')) {
              // Load the File entity from the Media image field.
              $file = $media->get('field_media_image')->entity;

              // Get the image URL.
              $image_url = $file ? \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri()) : '';
              // \Drupal::logger('image_url')->info('<pre>' . print_r($image_url, TRUE) . '</pre>');
            }
            else {
              $image_url = '';
            }
          }
          else {
            $image_url = '';
          }
          // Add image.
          if (!empty($image_url)) {
            $imageData = "<div class='campusfit-degrees-image pt-8'><img class='imagefluid' width='375px' src='$image_url' alt='$trimmedInterest Image' /></div>";
          }
          else {
            $imageData = '';
          }
        }
        else {
          $imageData = '';
        }
        // dpm($degreeDescr);
        if (!empty($top_10_degrees)) {
          // dpm(sizeof($top_10_degrees));.
          if (sizeof($top_10_degrees) == 1) {
            $healthLaw == TRUE;
          }
          if ($healthLaw == TRUE) {
            $degreeList = "<h3>$degreeDescr</h3><div class='uds-grid-links one-column campusfit-degrees-right-block'>";
          }
          else {
            $degreeList = "<h3>$degreeDescr</h3><div class='uds-grid-links two-columns campusfit-degrees-right-block'>";
          }
          // $degreeList = "<h3>$degreeDescr</h3><div class='uds-grid-links two-columns campusfit-degrees-right-block'>";
          // Foreach (array_slice($top_10_degrees, 0, 10) as $degreename => $degreeLink) {.
          foreach ($top_10_degrees as $degreename => $degreeLink) {
            $degreeList .= "<div class='degreeCard' style='border: 1px solid #d0d0d0;'><a href='$degreeLink' onclick=\"dataLayer.push({
              'event': 'link',
              'action': 'click',
              'name': 'onclick',
              'type': 'internal link',
              'region': 'main content',
              'section': 'Campusfit degrees block',
              'text': '$degreename'
            });\">$degreename</a></div>";
          }
          $degreeList .= '</div>';
        }
        else {
          if (($trimmedInterest == 'Pre health') || ($trimmedInterest == 'Pre law')) {
            $degreeList = "<p class='pt-8'>" . $degreeDescr . "</p>";
          }
          else {
            $degreeList = "";
          }
        }
        // $degreeList = "<h3>$degreeDescr</h3><div class='uds-grid-links two-columns campusfit-degrees-right-block'></div>";
        // More degrees code link.
        if (!empty($moreDegrees)) {
          $more_text = "<p class='pt-3'><div class='highlight-black more-degrees-heading'>There are even more degree options avaialable in the $campus campus.</div></p>";
          if (sizeof($moreDegrees) > 1) {
            $extraClass = 'mr-div';
          }
          else {
            $extraClass = '';
          }
          $moreDiv = "<div class='extra-degrees-buttons pt-2'>";
          foreach ($moreDegrees as $moreKey => $moreLink) {
            //dpm($moreKey);
            $mlink = ltrim(strip_tags($moreLink));
            // \Drupal::logger('$mlink ')->info('<pre>' . print_r($mlink, TRUE) . '</pre>');
            $moreDiv .= "<p><a onclick=\"dataLayer.push({
              'event': 'link',
              'action': 'click',
              'name': 'onclick',
              'type': 'internal link',
              'region': 'main content',
              'section': 'Campusfit degrees block',
              'text': '$moreKey'
            });\" class ='btn btn-gold $extraClass' href='$mlink'>$moreKey</a></p>";
          }
          $moreDiv .= "</div>";
        }
        else {
          $more_text = '';
          $moreDiv = '';
        }

        // \Drupal::logger('$imageData')->info('<pre>' . print_r($imageData  , TRUE) . '</pre>');
        // All degrees link code
        if (!empty($allDegrees)) {
          if (sizeof($allDegrees) > 1) {
            $extraClass = 'mr-div';
          }
          else {
            $extraClass = '';
          }
          $allDiv = "<div class='two-columns pt-2'>";
          foreach ($allDegrees as $allKey => $allLink) {
            $alllink_data = ltrim(strip_tags($allLink));
            $allDiv .= "<a onclick=\"dataLayer.push({
              'event': 'link',
              'action': 'click',
              'name': 'onclick',
              'type': 'internal link',
              'region': 'main content',
              'section': 'Campusfit degrees block',
              'text': 'All degrees'
            });\" class='btn btn-gold $extraClass' href='$alllink_data'>All degrees</a>";
            $allDiv .= "</div>";
          }
        }
        else {
          $allDiv = '';
        }
      }
      catch (\Exception $e) {
        return [];
      }

      $degreesDataDiv = $degreeList . $more_text . $moreDiv . $allDiv . $imageData;
      // $degreesDataDiv = $degreeList . $more_text . $moreDiv . $allDiv;
    }
    else {
      // $degreeList = '<div class="empty_data"></div>';
      $degreesDataDiv = '<div class="empty_data"></div>';
    }
    unset($_SESSION['ajaxCampus']);
    return [
      '#markup' => Markup::create($degreesDataDiv),
      '#cache' => [
        'max-age' => 0,
      ],
      '#attached' => [
        'library' => [
          'asu_campus_fit/campusFitResults',
        ],
      ],

    ];
  }

  /**
   *
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
