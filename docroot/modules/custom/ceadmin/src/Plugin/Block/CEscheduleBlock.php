<?php

namespace Drupal\ceadmin\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Render\Markup;

/**
 * Provides a Course Eval Schedule Block for the FAQ page
 *
 * @Block(
 *   id = "ceschedule_block",
 *   admin_label = @Translation("Course Evaluation Schedule"),
 *   category = @Translation("Course Evaluation Schedule"),
 * )
 */
class CEscheduleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $markup = '<div style="">';

    $counter=0;
    $query = "SELECT * FROM `CourseEvalSchedule` Order by sessionName";
    $result =  \Drupal::database()->query($query);

    foreach ($result as $row) {
      $term=$row->term;
      $sessionName=$row->sessionName;
      if ($sessionName == "Session A"){
        $sessionName0="Session A";
        $sessionRange0=$row->sessionRange;
        $classBegin0=$row->classBegin;
        $dropAdd0=$row->dropAdd;
        $surveyOpen0=$row->surveyOpen;
        $classEndC0=$row->classEndC;
        $surveyClose0=$row->surveyClose;
        $classEndAB0=$row->classEndAB;
      }
      if ($sessionName == "Session B"){
        $sessionName1="Session B";
        $sessionRange1=$row->sessionRange;
        $classBegin1=$row->classBegin;
        $dropAdd1=$row->dropAdd;
        $surveyOpen1=$row->surveyOpen;
        $classEndC1=$row->classEndC;
        $surveyClose1=$row->surveyClose;
        $classEndAB1=$row->classEndAB;
      }
      if ($sessionName == "Session C"){
        $sessionName2="Session C";
        $sessionRange2=$row->sessionRange;
        $classBegin2=$row->classBegin;
        $dropAdd2=$row->dropAdd;
        $surveyOpen2=$row->surveyOpen;
        $classEndC2=$row->classEndC;
        $surveyClose2=$row->surveyClose;
        $classEndAB2=$row->classEndAB;
      }
    }

    $markup.='<table id="ceschedule">
        <tbody>
          <th><FONT SIZE=4>'.$term.'</font></th>
          <th><FONT SIZE=4>'.$sessionName0.'</font></th><th><FONT SIZE=4>'.$sessionName1.'</font></th>
          <th><FONT SIZE=4>'.$sessionName2.'</font></th>
          <tr><td></td><td ><strong>'.$sessionRange0.'</strong></td><td ><strong>'.$sessionRange1.'</strong></td><td ><strong>'.$sessionRange2.'</strong></td></tr>
          <tr><td>Classes Begin</td><td>'.$classBegin0.'</td><td>'.$classBegin1.'</td><td>'.$classBegin2.'</td></tr>
          <tr><td>Last day to drop/add</td><td>'.$dropAdd0.'</td><td>'.$dropAdd1.'</td><td>'.$dropAdd2.'</td></tr>
          <tr><td><strong>Survey open</strong></td><td><strong>'.$surveyOpen0.'</strong></td><td><strong>'.$surveyOpen1.'</strong></td><td><strong>'.$surveyOpen2.'</strong></td></tr>
          <tr><td><strong>Survey close</strong></td><td><strong>'.$surveyClose0.'</strong></td><td><strong>'.$surveyClose1.'</strong></td><td><strong>'.$surveyClose2.'</strong></td></tr>
          <tr><td>Classes End</td><td>'.$classEndAB0.'</td><td>'.$classEndAB1.'</td><td>'.$classEndC2.'</td></tr>
        </tbody></table>';
    $markup.= '<p>*Check with your department representative for unit specific dates</p></div>';

    return array(
      '#type' => 'markup',
      '#markup' => Markup::create($markup)
    );
  }


}
