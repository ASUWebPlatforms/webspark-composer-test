<?php
namespace Drupal\tracking\Controller ;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


class trackingController {

  public function tracking_load($project) {

    $asurite = \Drupal::currentUser()->getAccountName();
    $baseurl = $GLOBALS['base_url'];
    $ts =  \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d H:i:s') ;
  
    switch ($project) {
      case 'connections':
      case 'fys-connections':
      case 'asuh-connections':
        $fileproject = "'connections', 'transfer', 'veterans'" ;
        break;
      case 'transfer':
      case 'dept-transfer':
      case 'fys-transfer':
        $fileproject = "'connections', 'transfer', 'veterans'" ;
        break;
      default:
        $fileproject = "'$project'" ;
        break;
    }  

    $queries = array(
      'projects' => "SELECT * FROM pt_projects",
      'admins' => "SELECT * FROM pt_admins",
      'groups' => "SELECT * FROM pt_groups",
      'participants' => "SELECT * FROM pt_participants p WHERE p.project_id LIKE '$project'",
      'completes' => "SELECT project, fileinfo, useridentifier, dateloaded, accept_dt, cardnumber, pin, `value` FROM UOEEE_FileDistribution_Log WHERE project IN ( $fileproject) AND DATEDIFF('$ts', dateloaded)<400;"
    );
  
    $tracking = [] ;
    foreach ($queries as $key => $q) {
      $tracking[$key] = \Drupal::database()->query($q)->fetchAll() ;
    };
    $tracking['baseurl'] = $baseurl ;
    $tracking['user'] = $asurite ;
    $tracking['project'] = $project ;
    $settings['tracking'] = $tracking ;
    
    return array(
      '#cache' => [
        'contexts' => [ 'user' ]
      ],
      '#attached' =>
          array(
            'library' => array('tracking/tracking-app' ),
            'drupalSettings' =>  $settings
          ),
      '#markup' => '<div class="tracking-wrapper"></div>'
    );
  }

  function tracking_sync($project) {

    $query="SELECT * FROM pt_participants WHERE project_id LIKE '" . $project . "'";

    $syncdata = [
        'data' => \Drupal::database()->query($query)->fetchAll() ,
    ];
  
    return new JsonResponse($syncdata);

  }

  function tracking_ask($project) {

    switch ($project) {
      case 'connections':
      case 'fys-connections':
      case 'asuh-connections':
        $fileproject = "connections" ;
        break;
      case 'transfer':
      case 'dept-transfer':
      case 'fys-transfer':
        $fileproject = "transfer" ;
        break;
      default:
        $fileproject = "'$project'" ;
        break;
    }  

    $asurite = \Drupal::currentUser()->getAccountName();
    $ts =  \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d H:i:s') ;
    $query="SELECT dateloaded, useridentifier, campus, college, AskIncidentDisrespected, AskMajor, AskProblemTeacher, OtherResources_Open, downloaded FROM UOEEE_FileDistribution_Log WHERE project LIKE '$fileproject' AND 
            (AskIncidentDisrespected=1 OR AskMajor=1 OR AskProblemTeacher=1 ) AND DATEDIFF('$ts', dateloaded)<180 " ;

    $queryAll = \Drupal::database()->query("SELECT * FROM pt_admins WHERE unit='ALL' AND admin_id = '$asurite' AND project_id = '$project'");
    $qrycnt = count($queryAll->fetchAll());

    //check college access
    if ($qrycnt == 0 ) {
      $colleges = \Drupal::database()->query("SELECT * FROM pt_admins WHERE unit NOT LIKE 'ALL' AND admin_id = '$asurite' AND project_id = '$project'")->fetchAll() ;
      $carr =  $colleges;
      $collegestr = "";
      foreach ($colleges as $c) {
        $collegestr.= "'" . $c->unit . "'," ;
      }
      $collegestr = rtrim($collegestr,',');
      $query =  "$query AND unit IN ($collegestr)" ;
    } 

    $syncdata = [
        'data' => \Drupal::database()->query($query)->fetchAll() ,
    ];
  
    return new JsonResponse($syncdata);

  }


}