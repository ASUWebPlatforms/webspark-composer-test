<?php
namespace Drupal\workplus\Controller ;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Render\Markup;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Component\Utility\Html;

class workplusController {

  public function workplus_load() {

    $asurite = \Drupal::currentUser()->getAccountName();
    global $base_url ;

    $queries = wplinfo() ;
    $workplus = array(
      'baseurl' => $base_url,
      'asurite' => $asurite,      ) ;
    foreach ($queries as $key => $q) {
      $workplus[$key] = \Drupal::database()->query($q)->fetchAll() ;
    };
    //debug($program_qry);
    $settings = array ( "workplus" => $workplus ) ;
    return array(
      '#cache' => [
        'contexts' => [ 'user' ]
      ],
      '#attached' =>
          array(
            'library' => array('workplus/workplus-app' ),
            'drupalSettings' =>  $settings
          ),
      '#markup' => '<div class="workplus-wrapper"></div>'
    );

  }

  public function workplus_post() {
    $_POST = json_decode(file_get_contents('php://input'), true)['body'];
    $ts =  \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d H:i:s') ;
    $asurite = \Drupal::currentUser()->getAccountName();
  
    try {
      $rowsAffected="Affected rows: ";
  
      switch ($_POST['action']) {

        case 'supervisor':
          $sql = \Drupal::database()->insert('WPL_managers') 
            ->fields(array(
              'DeptCode' => $_POST['dept'],
              'manager' => $_POST['asurite'],
            ))
            ->execute();

          break;

        case 'notSupervisor':
          \Drupal::database()->update('WPL_managers')
              ->fields(array(
                'supervising' => 'N',
              ))
          ->condition('manager', $asurite)//$_POST['asurite'])
          ->execute();
          break;
      }
    } catch (Exception $e) {
      echo($e);
    }
  
    return new Response('OK', Response::HTTP_OK   ) ;

  }
  
  public function workplus_sync() {

    $asurite = \Drupal::currentUser()->getAccountName();

    $queries = wplinfo() ;
    $wplinfo = array('asurite' => $asurite) ;
    $skips = array("asuperson");
    foreach ($queries as $key => $q) {
      if (!in_array($key, $skips)) $wplinfo[$key] = \Drupal::database()->query($q)->fetchAll() ;
    };

    return new JsonResponse($wplinfo);

  }

}

function wplinfo() {

  $asurite = \Drupal::currentUser()->getAccountName();

  $queries = array(
    'asuperson' => "SELECT ASU_ASURITE_ID AS asurite, LAST_NM AS lastname, FIRST_NM as firstname, ASU_EMAIL_ADDR AS email,
                        CONCAT(FIRST_NM, ' ', LAST_NM, ' (', ASU_ASURITE_ID, ')' ) AS usertext
                        FROM ASU_Person_Active ap;",
    'departments' => "SELECT d.DeptCode as code, DeptDescr as descr FROM WPL_departments d INNER JOIN
                        (SELECT DeptCode FROM WPL_managers WHERE manager='$asurite') m2 
                        ON d.DeptCode=m2.DeptCode;",
    'managers' => "SELECT m.DeptCode as code, manager, supervising, UploadDt as uploaded, UpdateDt as updated, firstname, usertext, lastemail FROM WPL_managers m 
                    INNER JOIN (SELECT DeptCode FROM WPL_managers WHERE manager='$asurite') 
                        m2 ON m.DeptCode=m2.DeptCode
                    INNER JOIN (SELECT FIRST_NM as firstname, CONCAT(FIRST_NM, ' ', LAST_NM, ' (', ASU_ASURITE_ID, ')' ) AS usertext,ASU_ASURITE_ID FROM ASU_Person_Active)
                        p ON m.manager=p.ASU_ASURITE_ID;",
    'survey' => "SELECT * FROM Qualtrics_SurveyLogin WHERE survey = 'workplus';"
  );

  return $queries ;
}