<?php

namespace Drupal\custom_maillog\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class MailLogForm extends FormBase   {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'custom_mail_log_form';
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $Count_result = \Drupal::database()->query('SELECT count(*) AS datacount FROM custom_mail_log')->fetchAll();
        $dataCount=$Count_result[0]->datacount*1;
        $result = \Drupal::database()->query('SELECT * FROM custom_mail_log order by created_at desc LIMIT 0, 100')->fetchAll();
        $user_list = \Drupal::database()->query('SELECT cml.uid as muid, ufd.uid, ufd.name, count(cml.uid) FROM custom_mail_log cml INNER JOIN users_field_data ufd on ufd.uid = cml.uid GROUP By cml.uid')->fetchAll();
        $users=['all'=>'All', '0' => 'System'];
        
        foreach($user_list as $user){
            if($user->uid * 1 > 0){
                $users[$user->uid] = $user->name;
            }
        }
        asort($users);
                
        if(count($result) == 0){
            $html='<div id="results-container">No Mail Logs</div>';
           
        }else{
            $available = $dataCount;
            if($dataCount > 100){
                $available = 100;
            }
            $html = "<div id='results-container' class='table' style='font-size:12px;margin-top:25px;'>
            <h4><b>Showing rows 0 - ".$available." (".$dataCount." total)</b></h4>
            <table class='table'><tr><th>User ID</th><th>Module</th><th>From</th><th>To</th><th>Subject</th><th>Date Time</th></tr>";
           
            foreach($result as $mail) {
                
                if($mail->uid == 0){
                    $user = "System";
                }else{
                    $userEntity = \Drupal\user\Entity\User::load($mail->uid);
                    if($userEntity){
                        $user = $userEntity->get('name')->value;
                    }else{
                        $user = $mail->uid;
                    }
                    
                }                
                $html .= "<tr><td>".$user."</td><td>".$mail->module."</td><td>".$mail->from."</td><td>".$mail->to."</td><td><a href='/site/mail-log/".$mail->id."'>".$mail->subject."</a></td><td>".$mail->created_at."</td></tr>";
                
            }
            $html .= "</table></div>";    
        }


        $form['user_id'] = array (
            '#type' => 'select',
            '#title' => ('User'),
            '#options' => $users,            
            '#ajax' => [
                'callback' => '::filterResultsCallback',
                'event' => 'change',
                // 'wrapper' => 'results-container',  // Must match the ID in #markup
                'method' => 'replace',
            ],
          );
            
          if($dataCount > 0){
            $pageCount = ceil($dataCount / 100);

            $pageOptions=[];
            for($i=1; $i<=$pageCount; $i++){
              $pageOptions[$i] = "Page ".$i;
            }
          }else {
            $pageOptions=["_none" => "NA"];
          }
          
          

          $form['page_limit'] = array (
            '#type' => 'select',
            '#title' => ('Page'),
            '#options' => $pageOptions,
            '#prefix' => '<div id="page-list">',
            '#suffix' => '</div>',
            '#ajax' => [
              'callback' => '::filterResultsCallback',
              'event' => 'change',
            //   'wrapper' => 'results-container',  // Must match the ID in #markup
              'method' => 'replace',
          ],
          );
          
          // $form['actions']['submit'] = array(
          //   '#type' => 'submit',
          //   '#value' => $this->t('Save'),
          //   '#button_type' => 'primary',
  
          // );

        $form['table'] =  [
            '#type' => 'markup',
            '#markup' => $html
        ];
        return $form;
    }

    /**
   * AJAX callback for filtering results.
   */
  function filterResultsCallback(array &$form, FormStateInterface $form_state) {
    $search_value = $form_state->getValue('user_id');
    $page = $form_state->getValue('page_limit')*1;
    //
    
    $connection = Database::getConnection();
    $query = $connection->select('custom_mail_log', 'n')->fields('n');
    $cond="";
    if($search_value != "all"){
        $query->condition('n.uid', $search_value);
        $cond=' where `uid`= '.$search_value;
    }

    $Count_result = \Drupal::database()->query('SELECT count(*) AS datacount FROM custom_mail_log '.$cond)->fetchAll();

        $dataCount=$Count_result[0]->datacount*1;
        if($dataCount > 0){
            $pageCount = ceil($dataCount / 100);
            $pageOptions=[];

            for($i=1; $i<=$pageCount; $i++){
                $pageOptions[$i] = "Page ".$i;
            }
        }else {
            $pageOptions=["_none" => "NA"];
        }

    $query->orderBy('n.created_at', 'desc');    
    
    $start_count = 0;
    $end_count = $dataCount;
    if( (($page*100)-100) <  $dataCount){
        $start_count = ($page*100)-100;
        $end_count = ($page*100);
        $query->range(($page*100)-100, 100);

    }else{
        $query->range(0, 100);

    }

    if($end_count > $dataCount){
        $end_count = $dataCount;
    }

    $results = $query->execute()->fetchAll();

    
    if (!empty($results)) {
        $html = "<h4><b>Showing rows ".$start_count." - ".$end_count." (".$dataCount." total)</b></h4><table class='table'><tr><th>User ID</th><th>Module</th><th>From</th><th>To</th><th>Subject</th><th>Date Time</th></tr>";
        
        foreach ($results as $mail) {

            if($mail->uid == 0){
                $user = "System";
            }else{
                $userEntity = \Drupal\user\Entity\User::load($mail->uid);
                if($userEntity){
                    $user = $userEntity->get('name')->value;
                }else{
                    $user = $mail->uid;
                }
            }
            $html .= "<tr><td>".$user."</td><td>".$mail->module."</td><td>".$mail->from."</td><td>".$mail->to."</td><td><a href='/site/mail-log/".$mail->id."'>".$mail->subject."</a></td><td>".$mail->created_at."</td></tr>";
            
        }
        $html .= '</table>';
    } else {
        $html = '<div id="results-container">No Mail Logs</div>';
    }

    $form['table']['#markup'] = "<div id='results-container' class='table' style='font-size:12px;margin-top:25px'>" . $html . '</div>';
    $form['page_limit']['#options'] = $pageOptions;

    // return $form['table'];
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand("#results-container", $form['table']));
    $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand("#page-list", $form['page_limit']));
    return $response;
  }



    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

        // drupal_set_message($this->t('@can_name ,Your application is being submitted!', array('@can_name' => $form_state->getValue('candidate_name'))));
        foreach ($form_state->getValues() as $key => $value) {
        // drupal_set_message($key . ': ' . $value);
        }
    }


}

?>