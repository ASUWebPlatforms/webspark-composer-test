<?php

namespace Drupal\asu_document_creation;
use Drupal\Core\File\FileSystemInterface;

class LatexDocumentCreation {
  private function getLatexChapter($chapter) {
    $chapter = preg_replace('/([#_$%&{}])/','\\\$1', $chapter);
    return "\\chapter{" . $chapter . "}
  \\section{Section1}
  \\section{Section2}";
  }

  private function loadLatexTemplate($templateName) {
    $extension_list = \Drupal::service('extension.list.module');
    $latexpath = $extension_list->getPath('asu_document_creation') . '/latex/LaTex2024/';

    $templatePath = $latexpath . $templateName;
    return file_get_contents($templatePath);
  }

  private function getSty($fields) {
    extract($fields);

    $titles = '';
    foreach ([$first_title, $second_title, $third_title] as $key => $title) {
        if (!empty($title)) {
          // Find special characters and escape them
          $title = preg_replace('/([#_$%&{}])/','\\\$1', $title);
          $titles .= $key > 0 ? "\\\ \\ \\\ " . $title : $title;
        }
    }

    $chair = '';
    if($committee_chairs == 1){
      $chair .= $committee_chair_name . ', Chair \ ' . $committee_mem_first;
    } else {
      $chair .= $committee_chair_name . ', Co-Chair \ ' . $committee_mem_first . ', Co-Chair ';
    }
    foreach($members as $member){
      $chair .= '\\\ ' . $member;
    }

    $styTemplate = $this->loadLatexTemplate('asudis.sty');

    // Associative array where keys are placeholders and values are the replacements
    $placeholders = [
      'FieldTitle' => $titles,
      'FieldFullName' => $full_name,
      'FieldDefenseDate' => $defense_date,
      'FieldGraduationDate' => $graduation_date,
      'FieldDegree' => $degree,
      'FieldDocumentType' => $document_type,
      'FieldMembers' => $chair,
      'FieldChapters' => count($chapters) ?? 0
    ];

    // Loop through the array and replace each placeholder
    foreach ($placeholders as $key => $value) {
      $styTemplate = str_replace("{" . $key . "}", "{" . $value . "}", $styTemplate);
    }

    foreach ($document_sections as $section){
      $styTemplate = str_replace(
        "\setboolean{Show" . ucfirst($section) . "}{false}",
        "\setboolean{Show" . ucfirst($section) . "}{true}",
        $styTemplate
      );
    }

    return $styTemplate;
  }


  public function createDocument($fields){
    extract($fields);
    $currentUser = \Drupal::currentUser();
    
    $file_system = \Drupal::service('file_system');
    $directory = 'public://latex';
    $file_system->prepareDirectory($directory, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    $extension_list = \Drupal::service('extension.list.module');
    $latexpath = $extension_list->getPath('asu_document_creation') . '/latex/';

    $zip_filename = $file_system->realpath($directory . '/' . $currentUser->getAccountName() . '-' . $document_type . '.zip');

    if (file_exists($zip_filename)) {
      unlink($zip_filename);
    }

    $zip = new \ZipArchive();

    $result = $zip->open($zip_filename, constant('ZipArchive::CREATE'));
    if (!$result) {
      \Drupal::logger('asu_document_creation')->warning('Zip archive could not be created. Error: ' . $result);
    }

    $extraFiles = array(
      'ack.tex',
      'appendix1.tex',
      'asudis.bst',
      'dis.bib',
      'cover.tex',
      'notation.tex',
      'README.md',
      'vita.tex',
      'dis.tex'
    );

    foreach ($extraFiles as $eF) {
      $zip->addFile($latexpath . '/LaTex2024/' . $eF, '' . $eF);
    }

    $zip->addFromString('asudis.sty', $this->getSty($fields));
    // escape special characters
    $abstract = preg_replace('/([#_$%&{}])/','\\\$1', $abstract);
    $zip->addFromString('abstract.tex', '\begin{abstract}' . $abstract . '\\end{abstract}');

    foreach (array_values($chapters) as $key => $chapter){
      $zip->addFromString('chapter' . ($key+1) . '.tex', $this->getLatexChapter($chapter));
    }

    if (!$result) {
      \Drupal::logger('asu_document_creation')->warning('File could not be added to zip archive.');
    }
    
    $zip->close();

    return $zip_filename;

  }
}
