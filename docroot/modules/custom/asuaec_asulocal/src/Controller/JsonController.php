<?php

namespace Drupal\asuaec_asulocal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Html;

/**
 * Defines a route controller for generating JSON pages.
 */
class JsonController extends ControllerBase {

  /**
   * Handler for JSON request. - Webservice direct
   * Build JSON page.
   * ie: /admin/asuaec-asulocal/json/wsdirect/categories/ground/ugrad
   *
   * @param Request $request
   * @return JsonResponse
   */
  public function generateJsonWsdirectCat(Request $request) {
    // Get ground/online and Ugrad/Grad
    $requestUri = $request->getRequestUri();
    $uriSegment_array = explode('/',$requestUri);
    $lastUriSegment = end($uriSegment_array);
    $grad_ugrad = $lastUriSegment;
    if($grad_ugrad == 'grad') {
      $grad_ugrad = 'graduate';
    } else if ($grad_ugrad == 'ugrad') {
      $grad_ugrad = 'undergrad';
    }
    $ground_online = $uriSegment_array[count($uriSegment_array)-2];

    $catOptions = [];

    // Get the grad/ugrad string from the URL, if it exists.
    if (!$grad_ugrad || !$ground_online) {
      return new JsonResponse($catOptions);
    }

    // Direct Web service
    switch ($ground_online) {
      case 'ground':
        // Added caching mechanism
        //$catOptions = $this->_get_all_categories_from_webservice_ground($grad_ugrad);

        // Use cached ground cats if available
        $cached_items = \Drupal::cache()->get('asuaec_ground_cats_' . $grad_ugrad);
        \Drupal::logger('asuaec_asulocal')->notice("cached ground cats: <pre>" . print_r($cached_items, true) . "</pre>");
        if($cached_items) {
          \Drupal::logger('asuaec_asulocal')->notice("There is cached cats.(ground) - cstest");
          $catOptions = $cached_items->data;
        } else {
          $catOptions = $this->_get_all_categories_from_webservice_ground($grad_ugrad);
          \Drupal::cache()->set('asuaec_ground_cats_' . $grad_ugrad, $catOptions);
        }
        break;

      case 'online':

        //---------------------
        // Web service direct version

        // Use cached online cats if available
        $cached_items = \Drupal::cache()->get('asuaec_online_cats_' . $grad_ugrad);
        \Drupal::logger('asuaec_asulocal')->notice("cached online cats: <pre>" . print_r($cached_items, true) . "</pre>");
        if($cached_items) {
          \Drupal::logger('asuaec_asulocal')->notice("There is cached cats.(online) - cstest");
          $catOptions = $cached_items->data;
        } else {

          // Get online degrees first

          // Added caching mechanism
//        $degrees = $this->_get_degrees_from_webservice_online($grad_ugrad);

          // Use cached online degrees if available
          $cached_items2 = \Drupal::cache()->get('asuaec_online_degrees_' . $grad_ugrad);
          if($cached_items2) {
            $degrees = $cached_items2->data;
          } else {
            $degrees = $this->_get_degrees_from_webservice_online($grad_ugrad);
          }
          foreach($degrees as $key => $value) {
            $catOptions[$key] = $key;
          }
          // Cache the catOptions
          \Drupal::cache()->set('asuaec_online_cats_' . $grad_ugrad, $catOptions);
        }
        break;
    }
    asort($catOptions);
    return new JsonResponse($catOptions);
  }


  /**
   * Pull from Web service directly.
   *
   * @param Request $request
   * @return JsonResponse
   */
  public function generateJsonWsdirectDegree(Request $request) {
    // Get ground/online and Ugrad/Grad
    $requestUri = $request->getRequestUri();
    $uriSegment_array = explode('/',$requestUri);
    $lastUriSegment = end($uriSegment_array); //<-- Interest
    $interest = urldecode($lastUriSegment);
    $grad_ugrad = $uriSegment_array[count($uriSegment_array)-2];
    $ground_online = $uriSegment_array[count($uriSegment_array)-3];

    $results = [];
    // Get the grad/ugrad string from the URL, if it exists.
    if (!$grad_ugrad || !$ground_online) {
      return new JsonResponse($results);
    }

    $degree_options = array();
    switch ($ground_online) {
      case 'ground':
        // There is no ground for ASU Local.
        break;

      case 'online':
        // For cache, use "graduate"/"undergrad" instead of "grad"/"ugrad"
        if($grad_ugrad == 'ugrad') {
          $grad_ugrad_caching = 'undergrad';
        } else if ($grad_ugrad == 'grad') {
          $grad_ugrad_caching = 'graduate';
        }

        // Use cached online degrees if available
        $cached_items = \Drupal::cache()->get('asuaec_online_degrees_' . $grad_ugrad_caching);
        \Drupal::logger('cstest')->notice("cached online degree: <pre>" . print_r($cached_items, true) . "</pre>");
        if($cached_items) {
          \Drupal::logger('cstest')->notice("There is cached degrees.(online) - cstest");
          $degrees = $cached_items->data;
        } else {
          $degrees = $this->_get_degrees_from_webservice_online($grad_ugrad);
          //\Drupal::cache()->set('asuaec_online_degrees_' . $grad_ugrad_caching, $degrees); //<-- Cache degrees inside _get_degrees_from_webservice_online()
        }
        $interest_lc = mb_strtolower(trim((string) $interest));

        foreach ($degrees as $key_interest => $value_array) {
          if (mb_strtolower(trim((string) $key_interest)) === $interest_lc) {
            foreach ($value_array as $key_onlinecode => $value) {
              $degree_options[$key_onlinecode] = $value['onlinetitle'];
            }
            break; // we found the bucket; no need to keep looping
          }
        }

        break;
    }
    asort($degree_options);
    return new JsonResponse($degree_options);
  }


  /**
   * Get Online degrees from web service and cache degrees.
   * Modified function asupesc_degree_webservice_data_insert_online.
   *
   * @param $grad_ugrad
   * @return array
   */
  protected function _get_degrees_from_webservice_online($grad_ugrad) {
    if($grad_ugrad == 'undergrad') {
      $grad_ugrad = 'ugrad';
    } else if ($grad_ugrad == 'graduate') {
      $grad_ugrad = 'grad';
    }
    // \Drupal::logger('cstest')->notice("grad_ugrad:". $grad_ugrad);
    $degrees_array = array();
    $client = \Drupal::httpClient();
    try {
      // Get degrees from web service
      $webservice_url = 'https://cms.asuonline.asu.edu/lead-submissions-v3.5/programs';

      $filter = '';
      if($grad_ugrad == 'ugrad') {
        $filter = '?category=undergraduate';
      }
      if($grad_ugrad == 'grad') {
        $filter = '?category=graduate';
      }
      $url = $webservice_url . $filter;
      $request = $client->get($url, array('headers' => array('Accept' => 'text/xml', 'Content-Type' => 'application/x-www-form-urlencoded')));

      $code = $request->getStatusCode();
      if ($code == 200) {
        $content = $request->getBody()->getContents();
        \Drupal::logger('cstest')->notice('content (escaped): <pre>@c</pre>', [
          '@c' => Html::escape(substr($content ?? '', 0, 2000)),
        ]);

        if ($content !== null && $content !== '') {

          // Capture XML errors nicely.
          libxml_use_internal_errors(true);

          // Trim + remove BOM(Byte Order Mark) if present.
          $xml_string = (string) $content;
          $xml_string = preg_replace('/^\xEF\xBB\xBF/', '', $xml_string);

          // Parse XML WITHOUT utf8_encode().
          $xml = simplexml_load_string($xml_string);

          if ($xml === false) {
            \Drupal::logger('asupesc_degree_webservice')->error('XML parse failed: @e', [
              '@e' => implode(' | ', array_map(fn($e) => trim($e->message), libxml_get_errors())),
            ]);
            libxml_clear_errors();

            // Helpful debug: log the first chunk escaped.
            \Drupal::logger('asupesc_degree_webservice')->error('XML (escaped head): <pre>@x</pre>', [
              '@x' => Html::escape(substr($xml_string, 0, 1500)),
            ]);

            return [];
          }

          libxml_clear_errors();

          foreach ($xml->program as $degree_obj ) {
            // Interest area
            $interest_areas_array = array();
            $onlineinterestareas_obj = $degree_obj->interestareas;
            foreach($onlineinterestareas_obj->value as $interest_area ) {
              array_push($interest_areas_array, $interest_area);
            }
            $interest_areas_string = implode('|', $interest_areas_array);
            // Sub plan
            $subplans_array = array();
            $onlinesubplancode_obj = $degree_obj->subplancode;
            foreach($onlinesubplancode_obj->value as $subplan ) {
              array_push($subplans_array, $subplan);
            }
            $subplans_string = implode('|', $subplans_array);
            $onlinecode = isset($degree_obj->code) ? $degree_obj->code : '';
            // \Drupal::logger('asuaec_asulocal')->notice("onlinecode: <pre>" . $onlinecode . "</pre>");
            $onlinecategory = isset($degree_obj->category) ? $degree_obj->category : '';
            $onlineprogcode = $degree_obj->progcode;
            $onlineplancode = $degree_obj->plancode;
            $onlineshortdesc = $degree_obj->shortdesc;
            $onlineurl = $degree_obj->detailpage;
            $onlinecrmdestination = $degree_obj->crmdestination;

            foreach($interest_areas_array as $interest_area_obj) {
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlinecode'] = "{$onlinecode}";
              
              // Fix mojibake on the field itself.
              $title = isset($degree_obj->title) ? (string) $degree_obj->title : '';
              $title = $this->normalizeText($title);
              // Normalize the en dash
              $title = str_replace('–', '-', $title);
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlinetitle'] = $title;

              // shortdesc
              $onlineshortdesc = isset($degree_obj->shortdesc) ? (string) $degree_obj->shortdesc : '';
              $onlineshortdesc = $this->normalizeText($onlineshortdesc);

              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlinecategory'] = "{$onlinecategory}";
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlineinterestarea'] = $interest_areas_string;
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlineprogcode'] = "{$onlineprogcode}";
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlineplancode'] = "{$onlineplancode}";
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlinesubplancode'] = $subplans_string;
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlineshortdesc'] = "{$onlineshortdesc}";
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlineurl'] = "{$onlineurl}";
              $degrees_array["{$interest_area_obj}"]["{$onlinecode}"]['onlinecrmdestination'] = "{$onlinecrmdestination}";
            }
          } // END of foreach ($xml->program as $key => $value )
        } else {
          throw new Exception("Web service didn't return anything.");
        }
      } else {
        throw new Exception("Error occured. Error code: " . $code);
      }

    }
    catch (Exception $e) {
      $messenger = \Drupal::messenger();
      $messenger->addMessage(t($e->getMessage(), []));
      \Drupal::logger('asuaec_asulocal')->error($e->getMessage());
    }

    // Cache $degrees_array for later use
    if($grad_ugrad == 'ugrad') {
      $grad_ugrad_cache = 'undergrad';
    } else if ($grad_ugrad == 'grad') {
      $grad_ugrad_cache = 'graduate';
    }
    \Drupal::cache()->set('asuaec_online_degrees_' . $grad_ugrad_cache, $degrees_array);
    return $degrees_array;
  } // END OF function asupesc_degree_webservice_data_insert_online()


  /**
   * Normalize text coming from the ASU Online XML feed.
   * Fixes mojibake like "â" / "â" by replacing the *byte sequences*.
   */
  protected function normalizeText(string $s): string {
    if ($s === '') {
      return $s;
    }

    // These are UTF-8 bytes for the broken sequences (mojibake).
    // Example: "â" (C3 A2 C2 80 C2 93) should become "–" (E2 80 93).
    $replacements = [
      "\xC3\xA2\xC2\x80\xC2\x93" => "–", // â  en dash
      "\xC3\xA2\xC2\x80\xC2\x94" => "—", // â  em dash
      "\xC3\xA2\xC2\x80\xC2\x99" => "’", // â  right apostrophe
      "\xC3\xA2\xC2\x80\xC2\x98" => "‘", // â  left apostrophe
      "\xC3\xA2\xC2\x80\xC2\x9C" => "“", // â  left quote
      "\xC3\xA2\xC2\x80\xC2\x9D" => "”", // â  right quote
      "\xC3\xA2\xC2\x80\xC2\xA6" => "…", // â¦  ellipsis
      "\xC2\xA0"                 => " ", // non-breaking space -> space
    ];

    $s = strtr($s, $replacements);

    // Safety: ensure valid UTF-8 output
    $s = iconv('UTF-8', 'UTF-8//IGNORE', $s);

    return $s;
  }




} // END OF class