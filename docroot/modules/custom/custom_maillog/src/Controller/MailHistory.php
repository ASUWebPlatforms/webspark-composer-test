<?php

namespace Drupal\custom_maillog\Controller;
use Drupal\Core\Controller\ControllerBase;

class MailHistory extends ControllerBase {

    public function maillist($param) {
        $result = \Drupal::database()->query('SELECT * FROM custom_mail_log where id ='.$param)->fetchAll();
      
        if(count($result) == 0){
            return [
            '#type' => 'markup',
            '#markup' => 'Data not found!'
            ];
        }else{
            $mail = $result[0];
            $from = $mail->from;
            $to = $mail->to;
            $uid = $mail->uid;
            $module = $mail->module;
            $subject = $mail->subject;
            $created_at = $mail->created_at;
            $message = $mail->message;
            $html = "<div class='container' style='padding:0px 0px'>
                        <h2>Mail Log</h2>
                        <a href='/site/maillogs' class='btn btn-primary'>Back</a>
                        <table class='table table-bordered'>";
            $html .="<tr>
                        <th>From</th>
                        <td>".$from."</td>
                    </tr>
                    <tr>
                        <th>To</th>
                        <td>".$to."</td>
                    </tr>
                    <tr>
                        <th>Subject</th>
                        <th>".$subject."</th>
                    </tr>
                    <tr>
                        <th>Time</th>
                        <td>".$created_at."</td>
                    </tr>
                    <tr>
                        <th>Modules</th>
                        <td>".$module."</td>
                    </tr>
                    <tr>
                        <th>Message</th>
                        <td>".$message."</td>
                    </tr>";
            
            $html .= "</table></div>";
            return [
            '#type' => 'markup',
            '#markup' => $html
            ];

        }
        }
}


?>