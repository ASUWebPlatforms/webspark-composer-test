<?php

namespace Drupal\asu_campus_fit\Service;

/**
 *
 */
class CampusFitJsSettings {

  /**
   *
   */
  public function getJsSettings($submission_data = '') {
    $database = \Drupal::database();

    $config_data = \Drupal::config('asu_campus_fit.admin_settings');
    if (!empty($submission_data)) {
      $campus_array = ['Tempe' => 'Tempe', 'West' => 'West', 'Poly' => 'Poly', 'Downtown' => 'Downtown', 'Online' => 'Online'];
      // dpm($submission_data);
      //$top_campus_select_value = $submission_data['would_you_rather_live_and_learn_in_a_place_where_'] ?? $submission_data['would_you_rather_live_and_learn_in_a_place_where_grad_options'] ?? '';
      if(!empty($submission_data['would_you_rather_live_and_learn_in_a_place_where_'])){
        $top_campus_select_value = $submission_data['would_you_rather_live_and_learn_in_a_place_where_'];
      }
      elseif(!empty($submission_data['would_you_rather_live_and_learn_in_a_place_where_grad_options'])){
        $top_campus_select_value = $submission_data['would_you_rather_live_and_learn_in_a_place_where_grad_options'];
      }
      else{
        $top_campus_select_value = '';
      }
      if (strpos($top_campus_select_value, 'Tempe') !== FALSE) {
        $top_campus_select = 'Tempe';
      }
      elseif (strpos($top_campus_select_value, 'Poly') !== FALSE) {
        $top_campus_select = 'Poly';
      }
      elseif (strpos($top_campus_select_value, 'West') !== FALSE) {
        $top_campus_select = 'West';
      }
      elseif (strpos($top_campus_select_value, 'Downtown') !== FALSE) {
        $top_campus_select = 'Downtown';
      }
      else {
        $top_campus_select = '';
      }
      foreach ($submission_data as $sub_key => $sub_value) {
        $score_field = "campus_fit-" . $sub_key . '_score_check';
        $form_score_field = \Drupal::state()->get($score_field);
        //dpm($form_score_field, '$form_score_field');
        if ($form_score_field == 1) {
         //$trimmed_sub_values = str_replace(" ",'',$sub_value);
          if ($sub_value !== null) {
            $trimmed_sub_values = str_replace(" ", '', $sub_value);
          }
          else {
            $trimmed_sub_values = '';
      }
          //$trimmed_sub_values = htmlspecialchars(trim($sub_value ?? ''), ENT_QUOTES, 'UTF-8');
          
          foreach ($campus_array as $campus) {
            $keys = "campus_fit-" . $sub_key . "-" . $trimmed_sub_values . "-" . $campus;
            $getSubValues[$keys] = \Drupal::state()->get($keys);
          }
        }
      }
     
      $data_key = [];
      foreach ($getSubValues as $dkey => $data) {
        if (intval($data)) {
          $data_key[$dkey] = $data;
        }

      }
      //dpm($data_key);
      // define arrays.
      $new_tempe_array[] = '';
      $new_poly_array[] = '';
      $new_west_array[] = '';
      $new_downtown_array[] = '';
      foreach ($data_key as $campus_key => $camp_score) {
        // dpm($campus_key . '=>' . $camp_score);.
        /* if(strpos($campus_key, 'campus_fit-would_you_rather_live_and_learn_in_a_place_where_-Tempe') !== FALSE) {
        $top_tempe_campus_select = 'Tempe';
        }
        elseif(strpos($campus_key, 'campus_fit-would_you_rather_live_and_learn_in_a_place_where_-Poly') !== FALSE) {
        $top_campus_select = 'Poly';
        }
        elseif(strpos($campus_key, 'campus_fit-would_you_rather_live_and_learn_in_a_place_where_-West') !== FALSE) {
        $top_campus_select = 'West';
        }
        elseif(strpos($campus_key, 'campus_fit-would_you_rather_live_and_learn_in_a_place_where_-Downtown') !== FALSE) {
        $top_campus_select = 'Downtown';
        }
        else{
        $top_campus_select = '';
        } */

        if (strpos($campus_key, 'Tempe') != FALSE) {
          $new_tempe_array[] = $camp_score;
        }
        else {
          $new_tempe_array[] = '';
        }
        if (strpos($campus_key, 'Poly') != FALSE) {
          $new_poly_array[] = $camp_score;
        }
        else {
          $new_poly_array[] = '';
        }
        if (strpos($campus_key, 'West') != FALSE) {
          $new_west_array[] = $camp_score;
        }
        else {
          $new_west_array[] = '';
        }

        if (strpos($campus_key, 'Downtown') != FALSE) {
          $new_downtown_array[] = $camp_score;
        }
        else {
          $new_downtown_array[] = '';
        }
        /*if(strpos($campus_key,'Havasu') != false){
        $new_havasu_array[] = $camp_score;
        }
        else{
        $new_havasu_array[] = '';
        }*/
      }

      //$tempe_score = '';

      if (!empty($new_tempe_array) && is_array($new_tempe_array)) {
        // Filter to keep only numeric values.
        $tempe_numeric_values = array_filter($new_tempe_array, 'is_numeric');

        // Then sum them.
        $tempe_score = array_sum($tempe_numeric_values);
      }

      if (!empty($new_poly_array) && is_array($new_poly_array)) {
        // Filter to keep only numeric values.
        $poly_numeric_values = array_filter($new_poly_array, 'is_numeric');

        // Then sum them.
        $poly_score = array_sum($poly_numeric_values);
      }

      if (!empty($new_west_array) && is_array($new_west_array)) {
        // Filter to keep only numeric values.
        $west_numeric_values = array_filter($new_west_array, 'is_numeric');

        // Then sum them.
        $west_score = array_sum($west_numeric_values);
      }

      if (!empty($new_downtown_array) && is_array($new_downtown_array)) {
        // Filter to keep only numeric values.
        $downtown_numeric_values = array_filter($new_downtown_array, 'is_numeric');

        // Then sum them.
        $downtown_score = array_sum($downtown_numeric_values);
      }
      // $score_array = array('Tempe' => $tempe_score, 'Poly' => $poly_score, 'West' => $west_score, 'Downtown' => $downtown_score, 'havasu' => $havasu_score);
      // $score_field_value = "Tempe-$tempe_score,Poly-$poly_score,West-$west_score,Downtown-$downtown_score,Havasu-$havasu_score";
      $score_array = ['Tempe' => $tempe_score, 'Poly' => $poly_score, 'West' => $west_score, 'Downtown' => $downtown_score];
      $score_field_value = "Tempe-$tempe_score,Poly-$poly_score,West-$west_score,Downtown-$downtown_score";

      arsort($score_array);
      // dpm($score_array, 'score array');
      // dpm($top_campus_select, 'top campus select');.
      $top_campus = array_search(max($score_array), $score_array);
      $dkeys = array_keys(array_intersect($score_array, [max($score_array)]));
      //dpm($top_campus,'top campus');
      //dpm($dkeys, 'dkeys');
      $top_campus_config_var = strtolower($top_campus) . '_' . 'res';
      // ksm($top_campus_config_var);
      if (!empty($dkeys)) {
        foreach ($dkeys as $dup_keys => $dup_values) {
          $lower_campsu_key = strtolower($dup_values) . '_' . 'res';
          $top_campus_results_nid[$dup_values] = $config_data->get($lower_campsu_key);
        }
      }
      else {
        $top_campus_results_nid[$top_campus] = !empty($config_data->get($top_campus_config_var)) ? $config_data->get($top_campus_config_var) : '';
      }
      $top_campus_var_for_js = strtolower($dkeys[0]) . '_' . 'res';

    }
    else {
      $top_campus = '';
      $score_field_value = '';
      $top_campus_config_var = '';
    }

    $campus_top_nid = !empty($top_campus_results_nid) ? $top_campus_results_nid : '';
    $campus_new_top_nid = !empty($top_campus_results_nid) ? $top_campus_results_nid : '';
    $settings = [];
    //dpm($top_campus_select, 'top campus select before multiple check');
    //dpm($campus_top_nid, 'campus top nid before multiple check');
    if (is_array($campus_top_nid)) {
      if (sizeof($campus_top_nid) > 1) {
        if (!empty($top_campus_select)) {
          $intersection = array_intersect_key($campus_top_nid, [$top_campus_select => '']);
          //dpm($intersection, 'intersection');
          if (!empty($intersection)) {
            // ✅ common key found
            $settings['multiple_campuses'] = "no";
            $campus_top_nid = $intersection;
          }
          else {
            $settings['multiple_campuses'] = "no";
            $campus_top_nid = [$top_campus_select => $campus_top_nid[$dkeys[0]]];
          }
        }
        else {
          $settings['multiple_campuses'] = "yes";
        }
        $settings['multiple_campus_names'] = $dkeys;
        $settings['multiple_campus_nids'] = $campus_top_nid;

      }
    }
    //dpm($campus_top_nid, 'campus top nid after multiple check');
    $settings['ofs'] = [];
    $settings['ofs']['ulcres'] = $config_data->get('ulcres');
    $settings['ofs']['lalocalresults'] = $config_data->get('lalocalresults');
    $settings['ofs']['onlres'] = $config_data->get('onlres');
    $settings['ofs']['ccres'] = $config_data->get('ccres');
    $settings['ofs']['lalocalCAresults'] = $config_data->get('lalocalCAresults');
    $settings['ofs']['asu4ures'] = $config_data->get('asu4ures');
    $settings['ofs']['asuLocalLAGrad'] = $config_data->get('asuLocalLAGrad');
    $settings['ofs']['londonRes'] = $config_data->get('londonRes');
    /*$settings['ofs']['asuWestHawai'] = $config_data->get('asuWestHawai');
    $settings['ofs']['asuWestHavasu'] = $config_data->get('asuWestHavasu');*/
    $settings['top_campus_var_for_js'] = !empty($top_campus_config_var) ? $config_data->get($top_campus_config_var) : '';
    $settings['top_campus_nid'] = $campus_top_nid;
    $settings['top_campus'] = $top_campus;
    $settings['campusRes'] = $config_data->get('campusRes');
    $settings['CARes'] = $config_data->get('CARes');
    $settings['wueRes'] = $config_data->get('wueRes');
    $settings['score_value'] = $score_field_value;
    //dpm($settings);
    // Return $config_data;
    // $settings['form_defaults'] = $config_data->get('asu_tuition_search_page_form_defaults', array());
    return $settings;
  }

}
