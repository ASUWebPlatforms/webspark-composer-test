<?php

namespace Drupal\asu_document_creation;

class DocumentSaveService {
  protected $latexDocumentCreation;
  protected $wordDocumentCreation;

  public function __construct(LatexDocumentCreation $latexDocumentCreation, WordDocumentCreation $wordDocumentCreation) {
    $this->latexDocumentCreation = $latexDocumentCreation;
    $this->wordDocumentCreation = $wordDocumentCreation;
  }

  public function createDocument($values) {

    $fields = $this->enshortValues($values);
    foreach (['graduation_date', 'defense_date'] as $date) {
      $fields[$date] = $this->formatDate($fields[$date]);
    }

    if ($fields['template_name'] == 'Microsoft Word') {
      return $this->createWordDocument($fields);
    } else {
      return $this->createLatexDocument($fields);
    }
  }

  private function enshortValues($values) {
    return array_combine(
      array_map(function ($k) {
        return str_replace('field_', '', $k);
      }, array_keys($values)),
      array_values($values)
    );
  }

  private function formatDate($stringDate) {
    if (!is_null($stringDate)) {
      $date = new \DateTime($stringDate);
      return $date->format('F Y');
    } else {
      return NULL;
    }
  }

  private function createWordDocument(array $fields) {
    return $this->wordDocumentCreation->createDocument($fields);
  }

  private function createLatexDocument(array $fields) {
    return $this->latexDocumentCreation->createDocument($fields);
  }
}
