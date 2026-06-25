<?php

namespace Drupal\wpc_rfi_forms\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\Cookie;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation;

/**
 * Create a new node entity from a webform submission.
 *
 * @WebformHandler(
 *   id = "rfi_form_processing",
 *   label = @Translation("RFI Form Processing"),
 *   category = @Translation("Webform Handler"),
 *   description = @Translation("Adds Special Processing During Form Submissions. Requires specific fields to work.See Help section for more information."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */

 // TODO - Need to confirm data stored is passed as needed and in right format
 // TODO - Need to add JS libraries to forms and site

class WpcFormsWebFormHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */


  // Function to be fired after submitting the Webform.
  public function preSave(WebformSubmissionInterface $webform_submission) { 

    // Set some initial vars

    $campus_key = 'Tempe';
    
    // Capture cookie session variables
      //$cookietest =\Drupal::request()->cookies->get('Drupal_visitor_cookie'); //testvar
      //$cookie_utm_medium =\Drupal::request()->cookies->get('utm_medium'); // captured in separate function
        $cookie_utm_source =\Drupal::request()->cookies->get('utm_source');
        $cookie_utm_campaign =\Drupal::request()->cookies->get('utm_campaign');
        $cookie_utm_term =\Drupal::request()->cookies->get('utm_term'); 
        $cookie_landing_page =\Drupal::request()->cookies->get('landing_page'); 
        $cookie_referring =\Drupal::request()->cookies->get('referring'); 
        $cookie_enterpriseclientid =\Drupal::request()->cookies->get('enterpriseclientid'); 
      

    // Capture submitted data
    $values = $webform_submission->getData();

    // TODO - Might want to do a check for fields on form, or add fields programatically
    // Expand user submitted values to be processed from form fields. 
        //$first_name = $values['first_name'];
        $program = $values['ps_acad_plan_descr'];
        //$campus = $values['campus'];
        //$self_source = $values['self_reported_lead_source'];

    // Extract pre-populated hidden fields
    
        $campaign_default = $values['campaign_id'];
        $ip_address_default = $values['ip_address'];
        $landing_page_default = $values['landing_page_url'];

        if ( $campaign_default == '') {
          $campaign_default = "70134000001CTaG";
        }

    // Set Program Selection Related Fields
        $program_item = $this->setProgram($webform_submission);
          $program_name = $program_item->get('name')->value; 
          $program_key = $program_item->get('ps_acad_plan_descr_key')->value; 
          $ps_acad_plan_key = $program_item->get('ps_acad_plan_key')->value;
          $campus_key = $program_item->get('campus_key')->value;

    // Set Primary and Secondary Resouce Fields
        $leadsource_item = $this->setLeadSource($webform_submission);
          $secondary_lead_source = $leadsource_item->get('name')->value;
          $primary_lead_source = $leadsource_item->get('lead_source')->value;

    // Set Tertiary Lead Source Field        
      if (isset($cookie_utm_source)) {
        $tertiary_lead_source = $cookie_utm_source;
        if ($cookie_utm_source == 'PANTHEON_STRIPPED') {
          $tertiary_lead_source = 'None';
         }
      } else if ($secondary_lead_source == 'email') {
        $tertiary_lead_source = "other";
      } else {
        $tertiary_lead_source = 'None';
      }
    
      // Set Campaign IDe
      if (isset($cookie_utm_campaign)) {
        $campaign_id = $cookie_utm_campaign;
        if(strpos(strtolower($campaign_id), 'edplus') !== false) {
          $campaign_id = "7014u000001NGaE";
          $tertiary_lead_source = "other";
          
        } elseif (substr($campaign_id, 0, 3) != '701' ) {
          $campaign_id = "70134000001CTaG";
        }
      } else {
        $campaign_id = $campaign_default;
      }

      // Set Landing Page
      if (isset($cookie_landing_page)) {
        $landing_page_init = $cookie_landing_page;
      } else {
        $landing_page_init = $landing_page_default;
      }
      //check to see if landing page is longer than 255, if so truncate.
      if (strlen($landing_page_init) >= 255) {
        $landing_page = substr($landing_page_init, 0, 255);
      } else {
        $landing_page = $landing_page_init;
      }

      // Set Keywords
      if (isset($cookie_utm_term)) {
        $keywords = $cookie_utm_term;
      } else {
        $keywords = '';
      }

      // Set Referring URL
      if (isset($cookie_referring)) {
        $referring_url = $cookie_referring;
      } else {
        $referring_url = '';
      }

      // Set Enterprise Client
      if (isset($cookie_enterpriseclientid)) {
        $enterprise_client_id = $cookie_enterpriseclientid;
      } else {
        $enterprise_client_id = '';
      }


  // Prepare processed values for re-assignment to form submission pre-save
  // TODO - Need to add error management if fields are not preset to prevent WSOD.
    $values['lead_source'] = $primary_lead_source;
    $values['secondary_lead_source'] = $secondary_lead_source;
    $values['tertiary_lead_source'] =  $tertiary_lead_source;
    $values['campaign_id'] = $campaign_id;
    $values['ip_address'] = $ip_address_default;
    $values['ps_acad_plan'] = $ps_acad_plan_key;
    $values['ps_acad_plan_descr_hidden'] = $program_key;
    $values['keywords'] = $keywords;
    $values['referring_url'] = $referring_url;
    $values['landing_page_url'] = $landing_page;
    $values['enterpriseclientid__c'] = $enterprise_client_id;
    $values['campus_key'] = $campus_key;

  // Set pre-save values from manipulated fields
    $webform_submission->setData($values);

  // Record submission to logger


  \Drupal::logger('webform custom rfi')->notice('webform saved lead source: '.$primary_lead_source);

  return true;
  }

  private function setProgram(WebformSubmissionInterface $webform_submission) { 

    // Capture Program selection from submission
    $values = $webform_submission->getData();
    $program = $values['ps_acad_plan_descr'];

    // Get the id for the selected program and use it to capture other fields.
    $programs = \Drupal::entityTypeManager()
    ->getStorage('wpc_rfi_forms_programs_program')
    ->loadByProperties([
      'id' => $program
    ]);
    $programSel = reset($programs);

    return $programSel;
  }

  private function setLeadSource(WebformSubmissionInterface $webform_submission) { 

    /* If captured from fomr submission 
    //Capture lead source from default entry to establish variable.
    $values = $webform_submission->getData();
    $source = $values['secondary_lead_source'];
    if ($source == '') {
      $source = 'Unknown';
    }
    */

    $cookie_utm_medium =\Drupal::request()->cookies->get('utm_medium');

    if (isset($cookie_utm_medium)) {
      $source = $cookie_utm_medium;
     if ($cookie_utm_medium == 'PANTHEON_STRIPPED') {
      $source = 'Unknown';
     }
    } else {
      $source = 'Web Direct';
    }
  
    // Get the id for the selected program and use it to capture other fields.
    $sources = \Drupal::entityTypeManager()
    ->getStorage('wpc_rfi_forms_sources_leadsource')
    ->loadByProperties([
      'name' => $source
    ]);
    // checking if utm_medium exists as an option, if not setting to unknown
    if ($sources){
      $sourceSel = reset($sources);
    } else {
      $sources = \Drupal::entityTypeManager()
      ->getStorage('wpc_rfi_forms_sources_leadsource')
      ->loadByProperties([
        'name' => 'Unknown'
      ]);
      \Drupal::logger('webform custom rfi')->notice('webform has unknown utm_medium: '.$source);
      $sourceSel = reset($sources);
    }
    return $sourceSel;
  }

}
