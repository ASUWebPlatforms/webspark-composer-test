<?php

namespace Drupal\asuaec_transferoption\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\Element\EntityAutocomplete;

/**
 * Defines a route controller for watches autocomplete form elements.
 * Thanks to:
 * https://antistatique.net/en/blog/how-to-create-a-custom-autocomplete-using-the-drupal-8-form-api
 */
class KeywordAutoCompleteController extends ControllerBase {

  /**
   * Handler for autocomplete request.
   * Build JSON page.
   * ie: /admin/asuaec_transferoption/autocomplete/keywords?q=bio
   */
  public function handleAutocomplete(Request $request) {

    // Get type (mapp/tag) from request URI
    $requestUri = $request->getRequestUri();
    $uriSegment_array = explode('/',$requestUri);
    $lastUriSegment = end($uriSegment_array);
    $type = strtok($lastUriSegment, '?');
//    \Drupal::logger('asuaec_transferoption')->notice("type:<pre>" . $type . "</pre>");

    $results = [];
    $input = $request->query->get('q');

    // Get the typed string from the URL, if it exists.
    if (!$input) {
      return new JsonResponse($results);
    }

    $input = Xss::filter($input);

    $database = \Drupal::database();
    $query = $database->select('asu_transferoption_keyword', 'k');
    $query->fields('k', ['keyword']);
    $query->condition('keyword', '%' . Database::getConnection()->escapeLike($input) . '%', 'LIKE');
    $query->condition('transferAgreementType', $type, '=');
    $query->orderBy('keyword', 'ASC');
    $query->range(0, 10);
    $result = $query->distinct()->execute();
    $keywordOptions = array();
    foreach ($result as $record) {
      $keywordName = $record->keyword;
      $keywordOptions[] = array('value'=>$keywordName, 'label'=>$keywordName);
    }
    return new JsonResponse($keywordOptions);
  }
}