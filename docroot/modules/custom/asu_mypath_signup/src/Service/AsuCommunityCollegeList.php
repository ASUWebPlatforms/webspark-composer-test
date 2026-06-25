<?php

namespace Drupal\asu_mypath_signup\Service;

/**
 * The DoStuff service. Does a bunch of stuff.
 */
class AsuCommunityCollegeList {

  /**
   * Does something.
   *
   * @return string
   *   Some value.
   */
  public function getCommunityCollegeList() {

    $cc_list = ['0' => 'Select...', 'Maricopa Community College' => 'Maricopa Community College', 'ABRAHAM BALDWIN AGRICULTURAL COLLEGE' => 'ABRAHAM BALDWIN AGRICULTURAL COLLEGE'];

    return $cc_list;
  }

}
