<?php
namespace Drupal\fdist\Controller ;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


class FdistController {

  public function fdist_load($project) {

    $asurite = \Drupal::currentUser()->getAccountName();
  
    switch ($project) {
      case "connections":
      case "transfer" :
      case "veterans" :
        $settings = get_fileInfo($project, $asurite ) ;
        break;
      case 'egift':    
        $settings = get_giftInfo($asurite ) ;
        break;
    }
    //debug ($settings) ;
    return array(
      '#cache' => [
        'contexts' => [ 'user' ]
      ],
      '#attached' =>
          array(
            'library' => array('fdist/fdist-app' ),
            'drupalSettings' =>  $settings
          ),
      '#markup' => '<div class="fdist-wrapper"></div>'
    );
  }

  function fdist_sync() {

    $asurite = \Drupal::currentUser()->getAccountName();
    $syncinfo = get_giftInfo($asurite ) ;
    return new JsonResponse($syncinfo);

  }

  public function fdist_post() {

    $_POST = json_decode(file_get_contents('php://input'), true);
    $ts =  \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d H:i:s') ;

    $sql = \Drupal::database()->update('UOEEE_FileDistribution_Log') // Table name no longer needs {}
    ->fields(array(
      'accept_dt' => $ts,
    ))
    ->condition('fileinfo', $_POST['fileinfo'])
    ->execute();

    return new Response('OK', Response::HTTP_OK   ) ;

  }
  
}

function get_fileInfo($project, $asurite ) {
  $settings = [] ;
  $queryfile = "SELECT fileinfo FROM UOEEE_FileDistribution_Log WHERE project LIKE '". $project ."' AND useridentifier LIKE '".$asurite."'" ;
  $verifyInfo =  \Drupal::database()->query($queryfile)->fetchObject() ;
  //debug ($verifyInfo) ;
  if ($verifyInfo) { 
    $baseurl = $GLOBALS['base_url'];
    $info = explode("_", $verifyInfo->fileinfo);
    switch ($project) {
      case "connections":
      case "transfer" :
      case "veterans" :
        $settings = array(
          'info' => array(
            'baseurl' => $baseurl,
            'project' =>  $project,    
            'asurite' => $asurite,
            'fname' => $info[0] ,
            'lname' => $info[1] ,
            'major' =>  $info[2],
            'college' =>  $info[3],
            'received' =>  $info[4],
          ),
        );
        break;
    }
    return $settings ;
  }
}

function get_giftInfo($asurite ) {

  $baseurl=$GLOBALS['base_url'] ;

  $settings = array(
    'info' => array(
      'baseurl' => $baseurl,
      'project' =>  'egift',    
      'asurite' => $asurite,
      'fname' => '' ,
      'lname' => '' ,
      'major' =>  '' ,
      'college' =>  '' ,
      'received' =>  '' ,
      'egifts' => array(),
    )
  );

  $queryfile = "SELECT * FROM UOEEE_FileDistribution_Log WHERE useridentifier LIKE '".$asurite."' AND cardnumber NOT LIKE ''" ;
  $result =  \Drupal::database()->query($queryfile)->fetchAll() ;
  if (sizeof($result)>0){
    $info = explode("_", $result[0]->fileinfo);
    $settings['info']['fname']=$info[0];
    $settings['info']['lname']=$info[1];
    $settings['info']['egifts']=$result;
  }
  
  return $settings ;

}