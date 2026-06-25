<?php

namespace Drupal\asu_tuition\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */

/**
 * Protected $requestStack;.
 */
class AsuTuitionGetOptions {

  /**
   * Does something.
   *
   * @return string
   *   Some value.
   */

  /**
   * Public function __construct(RequestStack $requestStack) {
   * $this->requestStack = $requestStack;
   * }
   */
  public function getOptions($first_option, $sql, $id, $descr_key, $args = [], $group_key = NULL) {
    // Get the options in the cache if there are any.
    // $cid = 'search_page_options:' . md5(serialize(func_get_args()));
    // $cache = asu_tuition_get_cache_by_id($cid);
    //
    //      if ($cache) {
    //        $options = $cache;
    //      }
    //      else {.
    $database = \Drupal::database();
    if (!$args) {
      $args = [];
    }
    elseif (!is_array($args)) {
      $args = [$args];
    }
    $results = $database->query($sql, $args);
    $options = [];

    // If group_key is not null, make options array an associative array to
    // allow Drupal to make option groups.
    if (is_null($group_key)) {
      $options = $results->fetchAllKeyed();
    }
    else {
      foreach ($results->fetchAll() as $row) {
        $options[$row->$group_key][$row->$id] = $row->$descr_key;
      }
    }

    // Only make a first option if $first_option is not blank.
    if (!is_null($first_option)) {
      $options = ['' => $first_option] + $options;
    }

    // asu_tuition_set_cache_by_id($cid, $options);
    // }
    // ksm($options);
    return $options;
  }

}
