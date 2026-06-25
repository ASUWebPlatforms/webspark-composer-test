<?php
namespace Drupal\artifacts\Controller ;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;



class artifactsController {

  public function artifacts_load() {

    $asurite = \Drupal::currentUser()->getAccountName();
  
    $queries = array(
      'users' => "SELECT DISTINCT * FROM ar_user",
      'projects' => "SELECT DISTINCT * FROM ar_projects",
      'artifacts' => "SELECT DISTINCT artifact_id, asurite, `filename`, project, lastupdate, uploaddate, review_ts FROM ar_artifacts",
      'rubrics' => "SELECT DISTINCT * FROM ar_rubrics",
      'access' => "SELECT DISTINCT * FROM ar_access",
      'scores' => "SELECT DISTINCT * FROM ar_scores", //WHERE reviewer LIKE '$asurite'"
      'training' => "SELECT DISTINCT * FROM ar_train",
      'groups' => "SELECT * FROM ar_groups",
    );
  
    foreach ($queries as $key => $q) {
      ${$key} = \Drupal::database()->query($q)->fetchAll() ;
    };
  
    $settings = array(
      'artifacts' => array(
        'projects' => $projects,
        'access' => $access,
        'user' => $asurite,
        'users' => $users ,
        'artifacts' => $artifacts ,
        'rubrics' => $rubrics ,
        'scores' => $scores ,
        'train' => $training ,
        'groups' => $groups ,
      ),
    );

    return array(
      '#cache' => [
        'contexts' => [ 'user' ]
      ],
      '#attached' =>
          array(
            'library' => array('artifacts/artifacts-app' ),
            'drupalSettings' =>  $settings
          ),
      '#markup' => '<div class="artifacts-wrapper"></div>'
    );
  }





  function artifacts_sync() {
 
    $reviewer = \Drupal::currentUser()->getAccountName();
  
    $scores = array(
      'score' => 'ar_scores' ,
      'calibrate' => 'ar_calibrate',
      'train' => 'ar_calibrate'
    );
  
    $tables = array(
      'score' => 'ar_artifacts' ,
      'calibrate' => 'ar_artifacts',
      'train' => 'ar_train'
    );

    $queries = array(
      'scores' => "SELECT t.* FROM ar_scores t JOIN ar_projects t2 ON t.project=t2.project WHERE t2.active='Y'" ,  //WHERE t.project = '$project'",
      'artifacts' => "SELECT DISTINCT artifact_id, asurite, `filename`, t.project, lastupdate, uploaddate, review_ts  FROM ar_artifacts t JOIN ar_projects t2 ON t.project=t2.project WHERE t2.active='Y'", // WHERE t.project = '$project';",
      'rubrics' => "SELECT DISTINCT * FROM ar_rubrics",
      'projects' => "SELECT DISTINCT * FROM ar_projects",
      'users' => "SELECT DISTINCT * FROM ar_user",
      'access' => "SELECT DISTINCT * FROM ar_access",
    );

    foreach ($queries as $key => $q) {
      ${$key} = \Drupal::database()->query($q)->fetchAll() ;
    };
  
    $sync = array(
        'scores' => $scores,
        'artifacts' => $artifacts,
        'rubrics' => $rubrics,
        'projects' => $projects,
        'users' => $users,
        'access' => $access,
    );
  
    return new JsonResponse($sync);

  }


  function artifacts_next($process, $artifact_id) {

    
    $reviewer = \Drupal::currentUser()->getAccountName(); 

    $tbl = array(
      'score' => "ar_artifacts",
      'calibrate' => "ar_train",
      'train' => "ar_train"
    );

    $scr = array(
      'score' => "ar_scores",
      'calibrate' => "ar_calibrate",
      'train' => "ar_calibrate"
    );

    $artifact = \Drupal::database()->query("SELECT DISTINCT * FROM $tbl[$process] WHERE artifact_id LIKE '$artifact_id';")->fetchObject();
    $scores = \Drupal::database()->query("SELECT * FROM $scr[$process] WHERE artifact_id LIKE '" . $artifact_id ."' AND reviewer LIKE '".$reviewer."';")->fetchAll();
  
      //For reviews process, insert into log table so that this artifact won't be pulled by another reviewer
      if ($process=='score') {
        $active_update = \Drupal::database()->insert('ar_log')
          ->fields(array(
            'artifact_id' => $artifact_id, 
            'reviewer' => $reviewer,
          ))
          ->execute();
      };
  
    $next = array(
      'artifact_id' => $artifact_id,
      'artifact' => $artifact,
      'scores' => $scores,
    );

    return new JsonResponse($next);
  }

  function artifacts_getSubmit($process, $artifact_id) {
    
    $reviewer = \Drupal::currentUser()->getAccountName(); 

    $scores = \Drupal::database()->query("SELECT * FROM ar_scores WHERE reviewer='$reviewer' AND artifact_id = '$artifact_id';")->fetchAll();

    return new JsonResponse($scores);
  }

  function artifacts_post() {

    $_POST = json_decode(file_get_contents('php://input'), true)['body'];
    $ts =  \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d H:i:s') ;

  
    $tables = array(
      'score' => 'ar_scores' ,
      'calibrate' => 'ar_calibrate',
      'train' => 'ar_calibrate'
    );
  
    switch ($_POST['action']) {
      
      case 'adduser':
        $person_insert = \Drupal::database()->insert('ar_user')
          ->fields(array(
            'user' => $_POST['user'], 
            'fname' => $_POST['fname'] ,
            'lname' => $_POST['lname'],
          ))
          ->execute();

      case 'addrole':

        $role_insert = \Drupal::database()->insert('ar_access')
          ->fields(array(
            'user' => $_POST['user'], 
            'role' => $_POST['role'],
            'access' => $_POST['access'],
            'reviews' => $_POST['reviews'] ,
          ))
          ->execute();
        break;

      case 'editproject':
        $project_update = \Drupal::database()->update('ar_projects')
          ->fields(array(
            'sample' => $_POST['sample'] , 
            'reviewsper' => $_POST['reviewsper'] , 
            'discrepancy' => $_POST['discrepancy'] , 
          ))
          ->condition('project', $_POST['project'], 'LIKE')
          ->execute();
          break;
          
      case 'addartifacts':
        //artifact_id, alternate, asurite, strm, section, project, url, title,  lastupdate, uploaddate, review_ts, Document_Url, Document_Loaded
        // { project:selProject.value, id:'', file:'', asurite:'', strm:'', section:'', paired:false }
        foreach ($_POST['artifacts'] as $fld) {
          $art_upsert = \Drupal::database()->upsert('ar_artifacts')
            ->key('artifact_id')
            ->fields(array('project' ,  'artifact_id'  ,  'filename' ,  'asurite'  ,  'strm', 'section','Document_Loaded'  ))
            ->values(array(
              'project' => $fld['project'] , 
              'artifact_id' => $fld['id'] , 
              'filename' => $fld['file'] ,
              'asurite' => $fld['asurite'] ,
              'strm' => $fld['strm'] ,
              'section' => $fld['section'] ,
              'Document_Loaded' => 1 ,
            ))
            ->execute();
        }
        break;
      
      case 'linkrubric':
        $project_update = \Drupal::database()->update('ar_projects')
        ->fields(array(
          'rubric' => $_POST['rubric'] , 
        ))
        ->condition('project', $_POST['project'], 'LIKE')
        ->execute();
        break;

      case 'saverubric':
        $rubric_insert = \Drupal::database()->upsert('ar_rubrics')
          ->fields(array('title','code','rubric','creator' ))
          ->key('code')
          ->values(array($_POST['rubricname'] ,$_POST['rubriccode'] , $_POST['rubric'] , $_POST['user']))
          ->execute();
          break;

      case 'saveartifact':
        $id=$_POST['selArtifact']['id'];
        $asurite=$_POST['selArtifact']['asurite'];
        $group=$_POST['selArtifact']['group'];
        $project=$_POST['selArtifact']['project'];
        $strm=$_POST['selArtifact']['term'];
        $artifact=$_POST['artifact'];
  
        $active_insert=\Drupal::database()->query("INSERT INTO ar_artifacts 
            (artifact_id, unscorable, selected, asurite, section, gened, externalText, strm, lastupdate, uploaddate)
            VALUES ( '$id', 0, 1,'$asurite','$group','$project','$artifact', '$strm','$ts', '$ts' )
              ON DUPLICATE KEY UPDATE externalText='$artifact', lastupdate='$ts'") ;
  
        break;
  
      case 'removeartifact':
        $active_delete = \Drupal::database()->delete('ar_artifacts')
          ->condition('artifact_id', $_POST['artifact_id'], 'LIKE')
          ->execute();
  
        break;
  
      case 'unscorable':
  
        $active_update = \Drupal::database()->update('ar_artifacts')
        ->fields(array(
          'selected' => 0 , 
          'unscorable' => 1 ,
        ))
        ->condition('artifact_id', $_POST['artifact_id'], 'LIKE')
        ->execute();
  
        $active_delete = \Drupal::database()->delete('ar_log')
          ->condition('artifact_id', $_POST['artifact_id'], 'LIKE')
          ->condition('reviewer', $_POST['reviewer'], 'LIKE')
          ->execute();
  
        break;
  
      case 'score':
        $active_update = \Drupal::database()->update('ar_log')
        ->fields(array(
          'overall' => $_POST['overall'],
          'review_ts' => $ts,
        ))
        ->condition('artifact_id', $_POST['artifact_id'], 'LIKE')
        ->condition('reviewer', $_POST['reviewer'], 'LIKE')
        ->execute();
  
      case 'calibrate':
      case 'train':
  
        $delete = \Drupal::database()->delete($tables[$_POST['action']]) // Table name no longer needs {}
          ->condition('artifact_id', $_POST['artifact_id'], 'LIKE')
          ->condition('reviewer', $_POST['reviewer'], 'LIKE')
          ->condition('project', $_POST['project'], 'LIKE')
          ->execute();
  
        foreach ($_POST['score'] as $area => $o) {
            $insert = \Drupal::database()->insert($tables[$_POST['action']]) // Table name no longer needs {}
              ->fields(array(
                'artifact_id' => $_POST['artifact_id'],            
                'project' => $_POST['project'],
                'area' => $area,           
                'score' => $o['score'],
                'reviewer' => $_POST['reviewer'],
                'review_ts' => $ts,
              ))
              ->execute();
        }
  
        break;
        
    }

    return new Response('OK', Response::HTTP_OK   ) ;

  }
  

  public function artifacts_fileupload() {
  
    $dirs =  array(
      'artifact_review' => './sites/default/files/docs/assessment/artifact_review/' ,
    );
  
    $uploaddir = $dirs[$_POST['type']] ;
    //debug($_FILES) ;
    $uploadfiletype = '.' . strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION)) ;
    $uploadfile = $uploaddir . $_POST['name'] ;
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
      $status = true;
    } else {
      $status = false;
      debug(print_r($_FILES));
    }
  
    return new JsonResponse($status);
  }

  public function artifacts_loaded() {
  
    $list = \Drupal::service('file_system')->scanDirectory('./sites/default/files/docs/assessment/artifact_review', '/.pdf$/') ;
    return new JsonResponse($list);
  }

  

}

