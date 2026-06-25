<?php

namespace Drupal\asu_gradapps_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AsuGradappsApiController extends ControllerBase {

  /**
   * Handles POST requests to /asu_gradapps_api/announcements endpoint.
   */
  public function createAnnouncement(Request $request) {
    // Get data from POST request.
    $data = json_decode($request->getContent(), TRUE);
  
      if (!empty($data) && is_array($data)) {
        $database = Database::getConnection();
        
        // Truncate the announcements table.
        $database->truncate('announcements')->execute();
        
        foreach ($data as $row) {
          if (
            isset($row['lname'], $row['fname'], $row['level'], $row['datetime'], $row['building'], $row['room'], $row['title'], $row['descr'], $row['bldg_location'], $row['degree'], $row['virtual_meetinglink'], $row['defense_type'], $row['virtual_audiencelink'])
          ) {
            // Insert new data.
            $database->insert('announcements')
              ->fields([
                'lname' => $row['lname'],
                'fname' => $row['fname'],
                'level' => $row['level'],
                'datetime' => $row['datetime'],
                'building' => $row['building'],
                'room' => $row['room'],
                'title' => $row['title'],
                'descr' => $row['descr'],
                'bldg_location' => $row['bldg_location'],
                'degree' => $row['degree'],
                'virtual_meetinglink' => $row['virtual_meetinglink'],
                'defense_type' => $row['defense_type'],
                'virtual_audiencelink' => $row['virtual_audiencelink'],
              ])
              ->execute();
          } else {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid data in one of the rows'], 400);
          }
        }
        // Log the successful refresh of the announcement table.
        \Drupal::logger('asu_gradapps_api')->notice('Announcements table refreshed');
      
        return new JsonResponse(['status' => 'success', 'message' => 'Announcement added']);
      }
    return new JsonResponse(['status' => 'error', 'message' => 'No data provided'], 400);
  }

  /**
   * Handles POST requests to /asu_gradapps_api/gf_data endpoint.
   */
  public function createGFData(Request $request) {
    // Get data from POST request.
    $data = json_decode($request->getContent(), TRUE);
  
    if (!empty($data) && is_array($data)) {
      $database = Database::getConnection();
  
      try {
        // Truncate the gf_data table.
        $database->truncate('gf_data')->execute();
  
        foreach ($data as $row) {
          if (isset($row['emplid'], $row['last_name'], $row['first_name'], $row['email_addr'], $row['phone'], $row['oprid'], $row['job_title'], $row['company_name'], $row['highest_lvl_approval'], $row['plancode'], $row['plan_descr'], $row['category'], $row['website'], $row['eid'], $row['employee_flag'])) {
            // Insert new data.
            $database->insert('gf_data')
              ->fields([
                'emplid' => $row['emplid'],
                'last_name' => $row['last_name'],
                'first_name' => $row['first_name'],
                'email_addr' => $row['email_addr'],
                'phone' => $row['phone'],
                'oprid' => $row['oprid'],
                'job_title' => $row['job_title'],
                'company_name' => $row['company_name'],
                'highest_lvl_approval' => $row['highest_lvl_approval'],
                'plancode' => $row['plancode'],
                'plan_descr' => $row['plan_descr'],
                'category' => $row['category'],
                'website' => $row['website'],
                'eid' => $row['eid'],
                'employee_flag' => $row['employee_flag'],
              ])
              ->execute();
          } else {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid data in one of the rows'], 400);
          }
        }

        // Log the successful refresh of the gf_data table.
        \Drupal::logger('asu_gradapps_api')->notice('gf_data table refreshed');
  
        return new JsonResponse(['status' => 'success', 'message' => 'GF data added']);
      } catch (\Exception $e) {
        return new JsonResponse(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
      }
    }
  
    return new JsonResponse(['status' => 'error', 'message' => 'No data provided or invalid format'], 400);
  }

  /**
   * Handles POST requests to /asu_gradapps_api/plancodes.
   */
  public function createPlancodes(Request $request) {
    // Get data from POST request.
    $data = json_decode($request->getContent(), TRUE);
  
    if (!empty($data) && is_array($data)) {
      $database = Database::getConnection();
      
      // Truncate the gf_plancodes table.
      $database->truncate('gf_plancodes')->execute();
      
      foreach ($data as $row) {
        if (isset($row['plancode'], $row['plan_descr'])) {
          // Insert new data.
          $database->insert('gf_plancodes')
            ->fields([
              'plancode' => $row['plancode'],
              'plan_descr' => $row['plan_descr'],
            ])
            ->execute();
        } else {
          return new JsonResponse(['status' => 'error', 'message' => 'Invalid data in one of the rows'], 400);
        }
      }
      // Log the successful refresh of the gf_plancodes table.
      \Drupal::logger('asu_gradapps_api')->notice('gf_plancodes table refreshed');
  
      return new JsonResponse(['status' => 'success', 'message' => 'Plancode data added']);
    }
  
    return new JsonResponse(['status' => 'error', 'message' => 'No data provided or invalid format'], 400);
  }

  /**
   * Handles POST requests to /asu_gradapps_api/categories.
   */
  public function createCategories(Request $request) {
    // Get data from POST request.
    $data = json_decode($request->getContent(), TRUE);
  
    if (!empty($data) && is_array($data)) {
      $database = Database::getConnection();
  
      try {
        // Truncate the gf_categorylist table.
        $database->truncate('gf_categorylist')->execute();
  
        foreach ($data as $row) {
          if (isset($row['asu_plan_cat_cd'], $row['descr100'])) {
            // Insert new data.
            $database->insert('gf_categorylist')
              ->fields([
                'asu_plan_cat_cd' => $row['asu_plan_cat_cd'],
                'descr100' => $row['descr100'],
              ])
              ->execute();
          } else {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid data in one of the rows'], 400);
          }
        }
        // Log the successful refresh of the gf_categorylist table.
        \Drupal::logger('asu_gradapps_api')->notice('gf_categorylist table refreshed');
  
        return new JsonResponse(['status' => 'success', 'message' => 'Category data added']);
      } catch (\Exception $e) {
        return new JsonResponse(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
      }
    }
  
    return new JsonResponse(['status' => 'error', 'message' => 'No data provided or invalid format'], 400);
  }

}