<?php

namespace Drupal\asu_campus_fit\Service;

use Drupal\Core\Database\Database;

/**
 * This service is called in mMyPathFormASU.php file to get programs list.
 */
class AsuOnCampusProgramList {

  /**
   * Function to pull oncampus porgrams from weservices.
   *
   * @return array of programs
   */
  public function getOnCampusProgramList($program = NULL, $url_interest = NULL, $url_campus = NULL) {

    $interest = !empty($url_interest) ? urldecode($url_interest) : '';
    //dpm($interest);
    // \Drupal::logger('interest')->info('<pre>' . print_r($interest, TRUE) . '</pre>');
    // \Drupal::logger('url interest')->info('<pre>' . print_r($url_interest, TRUE) . '</pre>');
    $connection = Database::getConnection();
    $node_title = $interest . '-' . $url_campus;
    // Build query based on url parameters.
    $query = $connection->select('node__field_degrees', 'm');
    $query->leftJoin('node_field_data', 'nd', 'm.entity_id = nd.nid');
    $query->leftJoin('node__field_degrees_campus', 'ndc', 'ndc.entity_id = nd.nid');
    $query->leftJoin('node__field_degree_type', 'nfdt', 'nfdt.entity_id = nd.nid');
    $query->leftJoin('node__field_academic_category', 'nfc', 'nfc.entity_id = nd.nid');
    $query->leftJoin('node__field_more_degrees', 'nfmd', 'nfmd.entity_id = nd.nid');
    $query->leftJoin('node__field_all_degrees_on_campus', 'nfadeg', 'nfadeg.entity_id = nd.nid');
    $query->leftJoin('taxonomy_term_field_data', 'tfd', 'tfd.tid = ndc.field_degrees_campus_target_id');
    $query->leftJoin('taxonomy_term_field_data', 'catfd', 'catfd.tid = nfc.field_academic_category_target_id');
    $query->leftJoin('taxonomy_term__field_category_images', 'taximage', 'taximage.entity_id = catfd.tid');
    $query->fields('m', ['field_degrees_value']);
    $query->addField('ndc', 'field_degrees_campus_target_id');
    $query->addField('nfmd', 'field_more_degrees_value');
    $query->addField('nfadeg', 'field_all_degrees_on_campus_value');
    $query->addField('nfc', 'field_academic_category_target_id');
    $query->addField('nfdt', 'field_degree_type_value');
    $query->addField('tfd', 'name', 'campus');
    $query->addField('taximage', 'entity_id', 'campus_tid');
    $query->addField('catfd', 'name', 'catName');
    $query->addField('nd', 'nid', 'nid');
    // Apply filters.
    if (!empty($program)) {
      $query->condition('nfdt.field_degree_type_value', '%' . $connection->escapeLike($program) . '%', 'LIKE');
    }
    if (!empty($interest)) {
      // $query->condition('catfd.name', '%' . $connection->escapeLike($interest) . '%', 'LIKE');
      $query->condition('catfd.name', $interest, '=');
    }
    if (!empty($url_campus)) {
      $query->condition('tfd.name', '%' . $connection->escapeLike($url_campus) . '%', 'LIKE');
    }

    /*$query->condition('tfd.name', '%' . $connection->escapeLike($url_campus) . '%', 'LIKE');
    $query->condition('catfd.name', '%' . $connection->escapeLike($interest) . '%', 'LIKE');
    $query->condition('nfdt.field_degree_type_value', '%' . $connection->escapeLike($program) . '%', 'LIKE');*/

    // Execute and fetch all.
    $results = $query->execute()->fetchAll();
   //\Drupal::logger('resuts')->info('<pre>' . print_r($results, TRUE) . '</pre>');
    // Initialize $data as an empty array.
    $data = [];
    // Print results.
    foreach ($results as $row) {

      $cat = $row->catName;
      // \Drupal::logger('$cat')->info('<pre>' . print_r($cat, TRUE) . '</pre>');
      $data[] = $row->field_degrees_value;
      $moredegrees[] = $row->field_more_degrees_value;
      $all_degree[] = $row->field_all_degrees_on_campus_value;
      $tax_id = $row->campus_tid;
      $nid = $row->nid;
    }
    // dpm($nid);
    //dpm($moredegrees);
    // \Drupal::logger('data')->info('<pre>' . print_r($data, TRUE) . '</pre>');.
    if (!empty($data)) {
      // 6 top degrees data
      $eachDegree = explode('*', $data[0]);
      $top_10_degrees = [];
      foreach ($eachDegree as $key => $value) {
        $degreesdata = explode('^', $value);
        $top_10_degrees[$degreesdata[0]] = $degreesdata[1] ?? '';
      }
      // \Drupal::logger('top_10_degrees-data')->info('<pre>' . print_r($top_10_degrees, TRUE) . '</pre>');
      // more degrees data
      if (!empty($moredegrees)) {
        $initialMoreDegrees = explode('*', $moredegrees[0]);
        foreach ($initialMoreDegrees as $morekey => $morevalue) {
          $moredegreesdata = explode('^', $morevalue);

          // $key = $more_degrees_links[ltrim(strip_tags($moredegreesdata[0]))] ?? '';
          // $more_degrees_links[ltrim(strip_tags($moredegreesdata[0]))] = ltrim($moredegreesdata[1]);
          $key = isset($moredegreesdata[0]) ? ltrim(strip_tags($moredegreesdata[0])) : '';
          $value = isset($moredegreesdata[1]) ? ltrim($moredegreesdata[1]) : '';

          $more_degrees_links[$key] = $value;
          unset($more_degrees_links['']);
        }
      }
      else {
        $more_degrees_links = [];
      }
      //dpm($more_degrees_links);
      // All degrees data.
      $allDegreesInitial = explode('*', $all_degree[0]);
      foreach ($allDegreesInitial as $allekey => $allvalue) {
        $alldegreesdata = explode('^', $allvalue);
        $allKey = isset($alldegreesdata[0]) ? ltrim(strip_tags($alldegreesdata[0])) : '';
        $allValue = isset($alldegreesdata[1]) ? ltrim($alldegreesdata[1]) : '';
        $all_degrees_links[$allKey] = $allValue;
        // $all_degrees_links[ltrim($alldegreesdata[0])] = ltrim($alldegreesdata[1]);
        unset($all_degrees_links['']);
      }

      // \Drupal::logger('more degrees')->info('<pre>' . print_r($more_degrees_links, TRUE) . '</pre>');
      // \Drupal::logger('all degrees')->info('<pre>' . print_r($all_degrees_links, TRUE) . '</pre>');
      // $safe_text = Html::escape($key);
      foreach ($top_10_degrees as $key => $degreeLink) {
        $safe_text = strip_tags($key);
        // $degreeList .= "<div style='border: 1px solid #d0d0d0;'><a href='$degreeLink'>$key</a></div>";
        $progList[ltrim($safe_text)] = ltrim($degreeLink);
        unset($progList['']);
      }
      // $degreeList .='</div>';
      // Sorting deferred or removed for performance optimization.
      // Uncomment the following line if sorting is necessary at this point.
      //dpm($progList);
      ksort($progList);

      \Drupal::logger('prog list')->info('<pre>' . print_r($progList, TRUE) . '</pre>');
      $finalList = [
        'topDegrees' => $progList,
        'moreDegreesLinks' => $more_degrees_links,
        'allDegreesLinks' => $all_degrees_links,
        'campusTid' => $tax_id,
        'nid' => $nid,
      ];
    }
    else {
      $finalList[0] = 'No data';
    }
    // ksm($progList);
    //dpm($finalList);
    return $finalList;
  }

}
