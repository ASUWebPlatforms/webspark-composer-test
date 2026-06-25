<?php

namespace Drupal\asu_format_advising;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityFieldManagerInterface;


class NodeQueryService {

  protected $currentUser;
  protected $entityFieldManager;
  private $mapValues;

  public function __construct(AccountProxyInterface $current_user, EntityFieldManagerInterface $entity_field_manager) {
    $this->currentUser = $current_user;
    $this->entityFieldManager = $entity_field_manager;
  }

  private function getLastFormatAdvisingNode() {
    $query = \Drupal::entityQuery('node')
        ->condition('type', 'formatadvising')
        ->condition('uid', $this->currentUser->id())
        ->condition('status', 1)
        ->sort('created', 'DESC')
        ->range(0, 1)
        ->accessCheck(TRUE);

    $nids = $query->execute();

    // Load the node if we have a result
    if ($nids) {
        $nid = reset($nids);
        return Node::load($nid);
    }

    return NULL; // Return NULL if no nodes were found
  }

  public function getUserFormatAdvising(){
    $node = $this->getLastFormatAdvisingNode();
    if($node == NULL){
      $node = Node::create([
        'type' => 'formatadvising', 
        'title' => $this->currentUser->getAccountName() . ' - Format Advising'
      ]);
      $node->save();
    }
    $this->mapValues();
    return $node;
  }

  private function multiple($value, $field, &$node){
    foreach($value as $k => $v){
      $texts[] = ['value' => $v];
    }
    $node->set($field, $texts);
  }

  public function saveStep($data){
    $node = $this->getLastFormatAdvisingNode();
    foreach ($data as $key => $value) {

      // Chapter
      if ($key === 'chapters'){
        $this->multiple($value, 'field_chapter_title', $node);
        continue;
      }

      // Members
      if ($key === 'members'){
        $this->multiple($value, 'field_your_committee', $node);
        continue;
      }

      // Array values like Document sections
      if(is_array($value)){
        $node->set($key, $value);
        continue;
      }

      // Dates
      if ($key != 'field_graduation_date' || $key != 'field_defense_date'){
        $node->set($key, [$value]); // Array filter problem with datelist in this php version
        continue;
      }
    
      // Default
      $node->set($key, $value);
    }
    $node->save();
  }

  public function getLabel($field_name, $key){
    return $this->mapValues[$field_name][$key];
  }

  public function mapValues(){
    $node = $this->getLastFormatAdvisingNode();
    $this->mapValues = [];
    foreach([
      'field_approved_font',
      'field_degree',
      'field_document_type',
      'field_style_guide',
      'field_template_name'
    ] as $field_name){
      $field_items = $node->get($field_name);
      $field_definition = $field_items->getFieldDefinition();
      $this->mapValues[$field_name] = $field_definition->getSetting('allowed_values');
    }
  }

  public function getLabelValues($values){
    $value_keys = array_keys($values);
    foreach($this->mapValues as $field_name => $allowed_values){
      if(in_array($field_name, $value_keys) && !empty($values[$field_name])){
        $values[$field_name] = $this->getLabel($field_name, $values[$field_name]);
      }
    }
    return $values;
  }

  public function getDefaultFields(){
    $node = $this->getLastFormatAdvisingNode();
    $field_definitions = $this->entityFieldManager->getFieldDefinitions('node', $node->bundle());
    $values = [];
  
    foreach ($field_definitions as $field_name => $field_definition) {
      if (strpos($field_name, 'field_') === 0) {
          if($field_name == 'field_chapter_title' && $node->hasField($field_name) && !$node->get($field_name)->isEmpty()){
            foreach($node->get($field_name)->getValue() as $key => $chapter){
              $values['chapters']['chapter_' . ($key + 1)] =  $chapter['value'];
            }
            continue;
          }
          if($field_name == 'field_your_committee' && $node->hasField($field_name) && !$node->get($field_name)->isEmpty()){
            foreach($node->get($field_name)->getValue() as $key => $member){
              $values['members']['member_' . ($key + 1)] = $member['value'];
            }
            continue;
          }
          if($field_name == 'field_document_sections' && $node->hasField($field_name) && !$node->get($field_name)->isEmpty()){
            foreach($node->get($field_name)->getValue() as $section){
              $values[$field_name][] = $section['value'];
            }
            continue;
          }
          if($node->hasField($field_name) && !$node->get($field_name)->isEmpty()){
            $values[$field_name] = $node->get($field_name)->getValue()[0]['value'];
          }
      }
    }
    return $values;
  }

  public function saveCookie() {
    $fieldsValues = $this->getDefaultFields();
    
    $cookie = [
      'step' => 1,
      'data' => $this->getDefaultFields()
    ];

    $cookie_encode = json_encode($cookie);

    if (!headers_sent()) {
        setcookie('FormatAdvisingData', $cookie_encode, time() + (86400 * 30), "/");
    }
  }

  public function clearNodeData(){
    setcookie('FormatAdvisingData', '', time() - 3600, "/");
    $node = $this->getLastFormatAdvisingNode();
    if ($node) {
      $field_definitions = $this->entityFieldManager->getFieldDefinitions('node', $node->bundle());
  
      foreach ($field_definitions as $field_name => $field_definition) {
        if (strpos($field_name, 'field_') === 0 && !empty($node->get($field_name)->getValue())) {
          $node->set($field_name, NULL);
        }
      }
  
      $node->save();
      $this->saveCookie();
    }
  }

}
