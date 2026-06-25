<?php

namespace Drupal\ceadmin\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Render\Markup;

/**
 * Gets and provides downloads for course evaluation report PDF zips
 *
 * @Block(
 *   id = "cezips_block",
 *   admin_label = @Translation("Anthology Evaluate ZIPs Block"),
 *   category = @Translation("Anthology Evaluate ZIPs Block"),
 * )
 */
class CEzipsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $markup = '<div style="">';

    $asurite = \Drupal::currentUser()->getAccountName();
    
    $query = "SELECT a.field_asurite_value as asurite, m.name as zipname 
          FROM media__field_asurite a JOIN media_field_data m
          ON a.entity_id = m.mid 
          WHERE a.field_asurite_value='$asurite';";
    $result =  \Drupal::database()->query($query);
    $archive = $result->fetchObject() ;
    if ($archive) {
      $zn = $archive->zipname;
      $zip = "public://private/ce_archive/$zn" ;
      if (file_exists($zip)) {
        $content = file_get_contents($zip);
        if (isset($_GET["user"])){
          header('Content-Description: File Transfer');
          header('Content-Type: application/octet-stream');
          header('Content-Disposition: attachment; filename="' . basename($zn) . '"');
          header('Expires: 0');
          header('Cache-Control: must-revalidate');
          header('Pragma: public');
          header('Content-Length: ' . strlen($content));
          echo $content;
          exit ;
        } else {
          $markup.= '<div style="margin-top: 100px;"><form method=GET>
            <input type = "hidden" name = "user" value = "'.$asurite.'" />
            <button name=" type="submit" value="" style="width: 200px; height: 30px">Download</button></form></div>';
        }                      
     } else {
        $markup.= '<div style="margin-top: 100px;"<p>File found and did not download.</p></div>';
     }
    } else {
      $markup.= '<div style="margin-top: 100px;"<p>No file found.</p></div>';
    }
 
   return array(
     '#type' => 'markup',
     '#markup' => Markup::create($markup),
     '#cache' => ['max-age' => 0],
   );

  }

  public function downloadzip(){

    $asurite = \Drupal::currentUser()->getAccountName();
    
    $query = "SELECT a.field_asurite_value as asurite, m.name as zipname 
          FROM media__field_asurite a JOIN media_field_data m
          ON a.entity_id = m.mid 
          WHERE a.field_asurite_value='$asurite';";
    $result =  \Drupal::database()->query($query);
    $archive = $result->fetchObject() ;
    if ($archive) {
      $zn = $archive->zipname;
      $zip = "public://private/ce_archive/$zn" ;
      if (file_exists($zip)) {
        $content = file_get_contents($zip);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($zn) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($content));
        
        echo $content;
      }
    }
  }
}
