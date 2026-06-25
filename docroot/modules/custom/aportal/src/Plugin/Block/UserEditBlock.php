<?php

namespace Drupal\aportal\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Render\Markup;

/**
 * Provides a Current access table for User Edit webform block
 *
 * @Block(
 *   id = "apuseredit_block",
 *   admin_label = @Translation("User Edit webform block"),
 *   category = @Translation("User Edit webform block"),
 * )
 */
class UserEditBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {


    $uname = \Drupal::currentUser()->getAccountName();
    $asurite =  $_GET['code'];
    $markup = '<div style="">';
    $markup .= '<p><a href="/aportal">Assessment Portal</a>> User Edit</p>';

    $query = "SELECT * FROM PA_User WHERE Asurite LIKE '$asurite' ORDER BY Element, ElementType";
    $result =  \Drupal::database()->query($query);
    $result->allowRowCount = TRUE;
    $row_cnt = $result->rowCount() ;

    if ($row_cnt>0){
      $markup .= "<div style='align:center;'><table id='useredit' border='1' style='margin:auto;'>
                    <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Access</th>
                    </tr>";
      $counter = 0;
        foreach ($result as $row) {
          $FirstName=$row->FirstName;
          $LastName=$row->LastName;
          $Element=$row->element;
          $ElementType=$row->ElementType;

          $markup .= "<tr>";
          $markup .= "<td>" . $FirstName . "</td>";
          $markup .= "<td>" . $LastName . "</td>";
          $markup .= "<td>" . $Element . " (".$ElementType." Level Access)</td>";
          $markup .= "</tr>";
        }
      $markup .= "<h2 style='text-align:center; margin:auto;'>$FirstName $LastName</h2>";
      $markup .= "</table></div>";
    }
    else{
      $markup.= "<p>*$FirstName $LastName ($asurite) does not appear to currently have access. Check with UOEEE at assessment@asu.edu if this is incorrect.</div>";
    }

    $markup.= "</div>";

    return array(
      '#type' => 'markup',
      '#markup' => Markup::create($markup)
    );
  }


}
