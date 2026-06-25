<?php

namespace Drupal\camo\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for CAMO - Call Attempt Management Online.
 *
 * Provides endpoints for the CAMO Vue application including
 * calling management, text field standardization, and open comment extraction.
 */
class CamoController {

  /**
   * Builds the main CAMO application page.
   *
   * Loads the current user information and camo db data,
   * attaches the Vue application library, and returns the container
   * markup used to bootstrap the frontend.
   *
   * @return array
   *   A render array containing the inline template and attached settings.
   */
  public function camoLoad() {

    $asurite = \Drupal::currentUser()->getAccountName();
    $baseurl = \Drupal::request()->getSchemeAndHttpHost();

    $queries = [
      'access' => "SELECT DISTINCT survey, `role`, `priority` FROM CAMO_User WHERE asurite LIKE '" . $asurite . "'",
      'users' => "SELECT DISTINCT asurite, FirstNm, LastNm FROM CAMO_User",
      'callers' => "SELECT DISTINCT asurite, survey FROM CAMO_attemptRecord" ,
      'surveys' => "SELECT DISTINCT * FROM CAMO_surveys WHERE url IS NOT NULL AND url <> '';" ,
      'tags' => "SELECT DISTINCT * FROM CAMO_extrtag ;" ,
      'typethemes' => "SELECT DISTINCT * FROM CAMO_extractiontype ;",
      'themes' => "SELECT DISTINCT * FROM CAMO_extrtheme ;" ,
      'tagthemes' => "SELECT DISTINCT * FROM CAMO_extrtagtheme ;" ,
    ];

    foreach ($queries as $key => $q) {
      ${$key} = \Drupal::database()->query($q);
    };

    $settings = [
      'camo' => [
        'baseurl' => $baseurl,
        'user' => $asurite,
        'users' => $users->fetchAll() ,
        'callers' => $callers->fetchAll() ,
        'access' => $access->fetchAll() ,
        'surveys' => $surveys->fetchAll() ,
        'calling' => callingcomps(),
        'stnd' => stnd_count(),
        'extr' => extr_count(),
        'tags' => $tags->fetchAll() ,
        'themes' => $themes->fetchAll() ,
        'tagthemes' => $tagthemes->fetchAll() ,
        'typethemes' => $typethemes->fetchAll(),
      ],
    ];
    return [
      '#cache' => [
          'contexts' => ['user']
        ],
      '#attached' =>
          [
            'library' => ['camo/camo-app'],
            'drupalSettings' => $settings
          ],
      // '<div class="camo-wrapper d-print-none"></div>' //
      '#markup' => '<div class="camo-wrapper"></div>'
    ];
  }

  /**
   * Retrieves the next respondent for calling based on the survey parameter.
   *
   * @param string $param
   *   The survey identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing respondent data, attempts, start time, and calling components.
   */
  public function camoNext($param) {

    $caller = \Drupal::currentUser()->getAccountName();
    $ts = \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d H:i:s');
    $query = \Drupal::request()->get('priority');

    $disp_active = "'0', 'Phone busy', 'No Answer', 'Left Message', 'Respondent not available', 'Schedule Callback', 'Accidental Terminate'";
    $not_active = "`Caller` IS NULL OR `Caller` LIKE ''";

    $priority = '';
    $priorities = explode('-', $query);
    // dump($priorities);
    if ($query != '*'  && count($priorities) > 0) {
      foreach ($priorities as $pt) {
        $priority = "$priority `priority` LIKE '%|$pt|%' OR";
      }
      $priority = 'AND (' . substr($priority, 0, -2) . ')';
      // dump($priority);
    }

    // Query next respondent
    // Check Call back.
    $query = "SELECT * FROM (
          (SELECT * FROM CAMO_Respondent
                    WHERE Respondent_Id LIKE :param)
          UNION
          (SELECT * FROM CAMO_Respondent
                WHERE (Cast(TIMESTAMPDIFF(MINUTE, ScheduleCallback, :ts) As decimal) BETWEEN -7 AND 15 AND
                  (survey LIKE :param) AND
                    ( $not_active ) AND
                        (Disposition IN (  $disp_active ))))
            UNION
            (SELECT * FROM CAMO_Respondent WHERE survey LIKE :param AND ( $not_active ) $priority AND
            (ScheduleCallback IS NULL OR Cast(TIMESTAMPDIFF(MINUTE, ScheduleCallback, :ts) As decimal) Not BETWEEN -100000 AND 1080) AND
            (Disposition IS NULL OR Disposition IN ( $disp_active ) )
            ORDER BY PreviousAttempt, AttemptCount, svyURL, Respondent_Id, PNo ASC LIMIT 1 ) )
            AS u LIMIT 1";
    // dump($query);
    $database = \Drupal::database();
    $result = $database->query($query, [
      ':param' => $param,
      ':ts' => $ts,
    ]);

    // dump($result);
    $respondent = $result->fetchObject();
    // Update caller field so that this respondent won't be called by another caller while active.
    $active_update = \Drupal::database()->update('CAMO_Respondent')
      ->fields([
        'Caller' => $caller,
      ])
      ->condition('Respondent_Id', $respondent->Respondent_Id, 'LIKE')
      ->execute();

    // Update CAMO_shifts table.
    camo_shift();

    // Query all attempts for this respondent.
    $query = "SELECT * FROM CAMO_attemptRecord WHERE `Respondent_Id` LIKE '" . $respondent->Respondent_Id . "'";
    $result = \Drupal::database()->query($query);
    $attempts = $result->fetchAll();
    $next = [
      'respondent' => $respondent,
      'attempts' => $attempts,
      'startTime' => $ts,
      'calling' => callingcomps()
    ];

    // header('Content-type: application/json');.
    return new JsonResponse($next);
  }

  /**
   * Retrieves current calling information for a parameterized survey or all surveys.
   *
   * @param string $survey
   *   The survey identifier or 'surveys' for all.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response with records, shifts, respondents, and calling components.
   */
  public function camoSync($survey) {
    $svy = $survey == 'surveys' ? '%' : $survey;
    $query = "SELECT *, TIME_TO_SEC(TIMEDIFF(Attempt_TimeStamp, starttime))/60 AS callduration  FROM CAMO_attemptRecord WHERE `survey` LIKE '$svy' ORDER BY Attempt_TimeStamp desc LIMIT 100000;
    ;";
    $result = \Drupal::database()->query($query);
    $records = $result->fetchAll();

    $query = "SELECT shiftDay, asurite, SUM(TIME_TO_SEC(TIMEDIFF(lastaction, shiftstart)))/60 AS SumOfShiftDuration
          FROM CAMO_shifts GROUP BY asurite, shiftDay ORDER BY shiftDay DESC LIMIT 1000";
    $result = \Drupal::database()->query($query);
    $shifts = $result->fetchAll();

    // Query all active survey respondents.
    $query = "SELECT r.* FROM CAMO_Respondent r INNER JOIN (SELECT * FROM CAMO_surveys WHERE url IS NOT NULL AND url <> '') s ON r.survey=s.survey";
    $result = \Drupal::database()->query($query);
    $respondents = $result->fetchAll();

    $sync = [
      'records' => $records,
      'shifts' => $shifts,
      'respondents' => $respondents,
      'calling' => callingcomps(),
    ];

    // Echo json_encode($sync);
    return new JsonResponse($sync);

  }

  /**
   * Retrieves the current standardization record for the given survey and type.
   *
   * @param string $survey
   *   The survey identifier.
   * @param string $type
   *   The standardization type.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response with current standardization data and counts.
   */
  public function camoCrrStnd($survey, $type) {

    $asurite = \Drupal::currentUser()->getAccountName();
    if ($survey == '---' || !$survey) {
      $survey = '%';
    };
    if ($type == '---' || !$type) {
      $type = '%';
    };
    $query = "SELECT s.* FROM CAMO_standardization s INNER JOIN CAMO_methods m ON s.Survey = m.method
                WHERE (Standardizer IS NULL OR Standardizer LIKE '' OR Standardizer LIKE '$asurite') AND (Cleaned IS NULL OR Cleaned LIKE '')  AND m.`survey` LIKE '$survey' AND s.Standardization_Type LIKE '$type'
                ORDER BY ImportDt ASC LIMIT 1";
    $result = \Drupal::database()->query($query);
    $crrstnd = $result->fetchObject();
    $crr = [];
    if ($crrstnd) {
      $sid = $crrstnd->Standardization_ID;
      $org = $crrstnd->Original;
      $stype = $crrstnd->Standardization_Type;

      $stnd_update = \Drupal::database()->update('CAMO_standardization')
        ->fields([
          'Standardizer' => $asurite,
        ])
        ->condition('Standardization_ID', $sid)
        ->execute();

      $related = stnd_related($sid, $stype, 'Cleaned', $org);

      $crr = [
        'crrstnd' => $crrstnd,
        'crrbefore' => $related['before'],
        'crrafter' => $related['after'],
        'crrshared' => $related['shared'],
      ];
    }

    $crr['stnd'] = stnd_count();

    // Echo json_encode($sync);
    return new JsonResponse($crr);

  }

  /**
   * Retrieves standardization records for the given standardization ID.
   *
   * @param string $sid
   *   The standardization ID.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response with standardization data and related records.
   */
  public function camoStndRecords($sid) {

    $asurite = \Drupal::currentUser()->getAccountName();

    $query = "SELECT s.* FROM CAMO_standardization s INNER JOIN CAMO_methods m ON s.Survey = m.method WHERE Standardization_ID='$sid'
                ORDER BY ImportDt ASC LIMIT 1";
    $result = \Drupal::database()->query($query);
    $crrstnd = $result->fetchObject();
    $org = $crrstnd->Original;
    $cln = $crrstnd->Cleaned;
    $entry = empty($cln) ? $org : $cln;
    $stype = $crrstnd->Standardization_Type;

    $related = stnd_related($sid, $stype, 'Cleaned', $entry);

    $crr = [
      'crrstnd' => $crrstnd ,
      'crrbefore' => $related['before'],
      'crrafter' => $related['after'],
      'crrshared' => $related['shared'],
      'stnd' => stnd_count(),
    ];

    // Echo json_encode($sync);
    return new JsonResponse($crr);

  }

  /**
   * Searches for similar standardization records based on the search text.
   *
   * @param string $search
   *   The search term or 'n' for no search.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response with search results, recent records, and counts.
   */
  public function camoStndSearch($search) {

    $asurite = \Drupal::currentUser()->getAccountName();

    if ($search != 'n') {
      $words = explode(" ", trim($search));
      $search = '';
      foreach ($words as $w) {
        $w = str_replace("\'", "\\'", $w);
        $$w = preg_replace("/;/", "//", $w);
        debug($w);
        if (!in_array($w, ['the', 'of', 'a', 'on', 'and', 'an', 'for'])) {
          $search .= " `Cleaned` LIKE '%$w%' OR ";
        }
      }
      $search = substr($search, 0, -3);
      $query = "SELECT * FROM CAMO_standardization WHERE ($search);";
      $result = \Drupal::database()->query($query);
      $search = $result->fetchAll();
    }
    else {
      $search = [];
    }

    $query = "SELECT * FROM CAMO_standardization WHERE Standardize_TimeStamp IS NOT NULL ORDER BY Standardize_TimeStamp DESC LIMIT 1000";
    $result = \Drupal::database()->query($query);
    $recent = $result->fetchAll();

    $s = [
      'search' => $search,
      'recent' => $recent,
      'stnd' => stnd_count(),
    ];

    // Echo json_encode($sync);
    return new JsonResponse($s);

  }

  /**
   * Retrieves the current extraction record for the given type and extraction ID.
   *
   * @param string $type
   *   The extraction type.
   * @param string $eid
   *   The extraction ID or '--' for next.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response with current extraction data and counts.
   */
  public function camoCrrExtr($type, $eid) {

    $asurite = \Drupal::currentUser()->getAccountName();

    if ($eid == '--') {
      if ($type == '---' || !$type) {
        $type = '%';
      };
      $query = "SELECT e.* FROM CAMO_extraction e
                  WHERE (reviewer IS NULL OR reviewer LIKE '' OR reviewer LIKE '$asurite') AND (tags IS NULL OR tags LIKE '') AND e.extraction_type LIKE '$type'
                  ORDER BY ProcessedDt ASC LIMIT 1";
      $result = \Drupal::database()->query($query);
      $next = $result->fetchObject();
      $eid = $next->extractionID;
    }

    $query = "SELECT * FROM CAMO_extraction WHERE extractionID='$eid'";
    $crrextr = \Drupal::database()->query($query)->fetchAll();

    $extr_update = \Drupal::database()->update('CAMO_extraction')
      ->fields([
        'reviewer' => $asurite,
      ])
      ->condition('extractionID', $eid)
      ->execute();

    $crr = [
      'crrextr' => $crrextr,
      'extr' => extr_count(),
    ];

    // Echo json_encode($sync);
    return new JsonResponse($crr);

  }

  /**
   * Retrieves extraction records history.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response with extraction history.
   */
  public function camoExtrRecords() {

    $asurite = \Drupal::currentUser()->getAccountName();

    $query = "SELECT * FROM CAMO_extraction WHERE reviewer IS NOT NULL ORDER BY reviewdt DESC LIMIT 7500";

    $history = [
      'history' => \Drupal::database()->query($query)->fetchAll(),
    ];

    return new JsonResponse($history);

  }

  /**
   * Handles POST requests for various CAMO actions.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   An OK response.
   */
  public function camoPost() {

    $post = json_decode(file_get_contents('php://input'), TRUE)['body'];
    $ts = \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d H:i:s');
    $asurite = \Drupal::currentUser()->getAccountName();

    switch ($post['action']) {

      case 'Submit':
      case 'End':
        $dupChk = \Drupal::database()->select('CAMO_attemptRecord', 'r')
          ->fields('r')
          ->condition('Respondent_Id', $post['Respondent_Id'], 'LIKE')
          ->condition('Asurite', $post['asurite'], 'LIKE')
          ->condition('StartTime', $post['StartTime'], 'LIKE')
          ->countQuery()
          ->execute()
          ->fetchField();

        // debug($dupChk) ;.

        if ($dupChk == 0) {
          // Table name no longer needs {}.
          $insert = \Drupal::database()->insert('CAMO_attemptRecord')
            ->fields([
              'Attempt_TimeStamp' => $ts,
              'StartTime' => $post['StartTime'],
              'Asurite' => $post['asurite'],
              'Disposition' => $post['Disposition'],
              'Note' => $post['note'],
              'Respondent_Id' => $post['Respondent_Id'],
              'ScheduleCallback' => $post['ScheduleCallback'] == '' ? NULL : $post['ScheduleCallback'] ,
              'NextAttempt' => $post['NextAttempt'],
              'survey' => $post['survey'],
            ])
            ->execute();
        }
        else {
          $update = \Drupal::database()->update('CAMO_attemptRecord')
            ->fields([
              'Attempt_TimeStamp' => $ts,
              'StartTime' => $post['StartTime'],
              'Asurite' => $post['asurite'],
              'Disposition' => $post['Disposition'],
              'Note' => $post['note'],
              'Respondent_Id' => $post['Respondent_Id'],
              'ScheduleCallback' => $post['ScheduleCallback'] == '' ? NULL : $post['ScheduleCallback'] ,
              'NextAttempt' => $post['NextAttempt'],
              'survey' => $post['survey'],
            ])
            ->condition('Respondent_Id', $post['Respondent_Id'], 'LIKE')
            ->condition('Asurite', $post['asurite'], 'LIKE')
            ->condition('StartTime', $post['StartTime'], 'LIKE')
            ->execute();
        }

        $update = \Drupal::database()->update('CAMO_Respondent')
          ->expression('AttemptCount', 'AttemptCount + 1')
          ->fields([
            'NewNo' => $post['NextAttempt'],
            'ScheduleCallback' => $post['ScheduleCallback'] == '' ? NULL : $post['ScheduleCallback'],
            'Disposition' => $post['Disposition'],
            'PreviousAttempt' => $ts,
            'Caller' => NULL,
          ])
          ->condition('Respondent_Id', $post['Respondent_Id'], 'LIKE')
          ->execute();

        /*Make sure to clear Caller field for this caller
        $update = \Drupal::database()->update('CAMO_Respondent')
        ->fields(array(
        'Caller' => Null,
        ))
        ->condition('Caller', $post['asurite'], 'LIKE')
        ->execute();
        break;
         */
      case 'EditAttempt':
        $update = \Drupal::database()->update('CAMO_attemptRecord')
          ->fields([
            'Disposition' => $post['Disposition'],
            'NextAttempt' => $post['NextAttempt'],
            'ScheduleCallback' => $post['ScheduleCallback'] == '' ? NULL : $post['ScheduleCallback'],
            'Note' => $post['note'],
          ])
          ->condition('Attempt_Record_Id', $post['attempt_id'], 'LIKE')
          ->execute();
        break;

      case 'EditRespondent':
        $update = \Drupal::database()->update('CAMO_Respondent')
          ->fields([
            'NewNo' => $post['NextAttempt'],
            'Disposition' => $post['Disposition'],
            'ScheduleCallback' => $post['ScheduleCallback'] == '' ? NULL : $post['ScheduleCallback'],
            'Caller' => NULL,
          ])
          ->condition('Respondent_Id', $post['attempt_id'], 'LIKE')
          ->condition('Survey', $post['survey'], 'LIKE')
          ->execute();
        break;

      case 'clear':

        $update = \Drupal::database()->update('CAMO_Respondent')
          ->fields([
            'Caller' => NULL,
          ])
          ->condition('Respondent_Id', $post['Respondent_Id'], 'LIKE')
          ->execute();
        break;

      case 'Reset':

        $update = \Drupal::database()->update('CAMO_Respondent')
          ->fields([
            'NewNo' => NULL,
            'ScheduleCallback' => NULL,
            'Disposition' => 0,
            'AttemptCount' => 0,
            'Caller' => NULL,
          ])
          ->condition('survey', $post['param'], 'LIKE')
          ->execute();
        break;

      case 'SubmitCleaned':
        $update = \Drupal::database()->update('CAMO_standardization')
          ->fields([
            'Standardizer' => $asurite,
            'Standardize_TimeStamp' => $ts,
            'Cleaned' => $post['Cleaned'],
          ])
          ->condition('Standardization_ID', $post['Standardization_ID'], 'LIKE')
          ->execute();
        break;

      case 'AuditCleaned':
        $update = \Drupal::database()->update('CAMO_standardization')
          ->fields([
            'Auditor' => $asurite,
            'Audit_TimeStamp' => $ts,
            'Cleaned' => $post['Cleaned'],
          ])
          ->condition('Standardization_ID', $post['Standardization_ID'], 'LIKE')
          ->execute();
        break;

      case 'SubmitExtr':
      case 'AuditExtr':
        // camo_ID, extractionID, ProcessedDt, extraction_type, textdata, sentiment, tags, extraction, reviewer, reviewdt.
        foreach ($post['extraction'] as $sub) {
          if ($sub['camo_ID'] == 'new') {
            $insert = \Drupal::database()->insert('CAMO_extraction')
              ->fields([
                'reviewdt' => $ts,
                'reviewer' => $asurite,
                'extractionID' => $sub['extractionID'],
                'ProcessedDt' => $sub['ProcessedDt'],
                'extraction_type' => $sub['extraction_type'],
                'textdata' => $sub['textdata'],
                'sentiment' => $sub['sentiment'],
                'tags' => $sub['tags'],
                'extraction' => $sub['extraction'],
              ])
              ->execute();
          }
          else {
            $update = \Drupal::database()->update('CAMO_extraction')
              ->fields([
                'reviewdt' => $ts,
                'reviewer' => $asurite,
                'sentiment' => $sub['sentiment'],
                'tags' => $sub['tags'],
                'extraction' => $sub['extraction'],
              ])
              ->condition('camo_ID', $sub['camo_ID'])
              ->execute();
          }
        }
        $delete = \Drupal::database()->delete('CAMO_extraction')
          ->condition('extractionID', $sub['extractionID'])
          ->condition('reviewdt', $ts, 'NOT LIKE')
          ->execute();
        break;

      case 'respellextr':
        $update = \Drupal::database()->update('CAMO_extraction')
          ->fields([
            'textdata' => $post['respelled'],
          ])
          ->condition('extractionID', $post['extractionID'])
          ->execute();
        break;
    }

    // Update CAMO_shifts table.
    camo_shift();

    return new Response(
      'OK', Response::HTTP_OK);
  }

}

/**
 * Updates or inserts shift information for the current user.
 */
function camo_shift() {

  $caller = \Drupal::currentUser()->getAccountName();
  $ts = \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d H:i:s');

  // Query shift info.
  $query = "SELECT id FROM CAMO_shifts WHERE asurite = '$caller' AND TIMESTAMPDIFF(MINUTE, lastaction, '$ts')< 30";
  $result = \Drupal::database()->query($query);
  $shift = $result->fetchAll();
  $shift_rows = count($shift);

  if ($shift_rows > 0) {
    $row = \Drupal::database()->query($query)->fetchObject();
    // \Drupal::logger('module-custom-camo')->notice('<pre>'.print_r($row, TRUE).'</pre>');

    $shift_update = \Drupal::database()->update('CAMO_shifts')
      ->fields([
        'lastaction' => $ts,
      ])
      ->condition('id', $row->id)
      ->execute();
  }
  else {
    // Table name no longer needs {}.
    $insert = \Drupal::database()->insert('CAMO_shifts')
      ->fields([
        'shiftday' => date($ts),
        'shiftstart' => $ts,
        'lastaction' => $ts,
        'asurite' => $caller,
      ])
      ->execute();
  }
}

/**
 * Counts remaining and recent standardization records.
 *
 * @return array
 *   An array with remaining and counting data.
 */
function stnd_count() {

  $asurite = \Drupal::currentUser()->getAccountName();

  $query = "SELECT COUNT(*) AS ustnd, Standardization_Type, m.survey FROM CAMO_standardization s LEFT JOIN CAMO_methods m ON
              s.Survey = m.method
              WHERE Cleaned IS NULL OR Cleaned LIKE ''
              GROUP BY s.Standardization_Type, m.Survey
              ORDER BY m.Survey ;
              ";
  $remaining = \Drupal::database()->query($query)->fetchAll();

  $query = "SELECT * FROM CAMO_standardization s LEFT JOIN CAMO_methods m ON
              s.Survey = m.method
              WHERE Standardize_TimeStamp BETWEEN (CURDATE() - INTERVAL 1 MONTH ) AND NOW() OR
              Audit_TimeStamp BETWEEN (CURDATE() - INTERVAL 1 MONTH ) AND NOW() ORDER BY Standardize_TimeStamp DESC;
              ";
  $counting = \Drupal::database()->query($query)->fetchAll();

  $stnd = [
    'remaining' => $remaining,
    'counting' => $counting,
  ];

  return $stnd;
}

/**
 * Finds related standardization records.
 *
 * @param string $sid
 *   The standardization ID.
 * @param string $stype
 *   The standardization type.
 * @param string $fld
 *   The field to search.
 * @param string $entry
 *   The entry value.
 *
 * @return array
 *   An array with before, after, and shared records.
 */
function stnd_related($sid, $stype, $fld, $entry = '') {
  $entry = str_replace("'", "\'", $entry);
  $entry = preg_replace("/;/", "//", $entry);

  $database = \Drupal::database();

  $query = $database->select('CAMO_standardization', 'c');
  $query->fields('c');
  $query->condition('Original', $entry, '>');
  $query->condition('Standardization_Type', $stype, '=');
  $query->orderBy('Original', 'ASC');
  $query->range(0, 25);

  $result = $query->execute();
  $after = $result->fetchAll();

  $query = $database->select('CAMO_standardization', 'c');
  $query->fields('c');
  $query->condition('Original', $entry, '<');
  $query->condition('Standardization_Type', $stype, '=');
  $query->orderBy('Original', 'DESC');
  $query->range(0, 25);

  $result = $query->execute();
  $before = $result->fetchAll();

  // Get entries that share common words.
  $words = explode(" ", trim($entry));
  $shared = "";
  foreach ($words as $w) {
    if (!in_array($w, ['the', 'of', 'a', 'on', 'and', 'an', 'for'])) {
      $shared .= " `$fld` LIKE '%$w%' OR ";
    }
  }
  $shared = substr($shared, 0, -3);
  $query = "SELECT * FROM CAMO_standardization WHERE ($shared) AND Standardization_ID NOT LIKE '$sid' AND Standardization_Type='$stype' AND Standardization_Type NOT IN ('Cities');";
  // debug($query) ;.
  $result = \Drupal::database()->query($query);
  $shared = $result->fetchAll();

  $stnd = [
    'before' => $before,
    'after' => $after,
    'shared' => $shared,
  ];

  return $stnd;
}

/**
 * Counts remaining and recent extraction records.
 *
 * @return array
 *   An array with remaining and counting data.
 */
function extr_count() {

  $asurite = \Drupal::currentUser()->getAccountName();

  $query = "SELECT COUNT(*) AS uextr, extraction_type FROM CAMO_extraction e
                WHERE (tags IS NULL OR tags LIKE '') OR  reviewer is NULL AND (tags <> 'exclude')
                GROUP BY extraction_type ;
              ";
  $remaining = \Drupal::database()->query($query)->fetchAll();

  $query = "SELECT DISTINCT extractionID, reviewer, reviewdt FROM CAMO_extraction e
              WHERE reviewdt BETWEEN (CURDATE() - INTERVAL 1 MONTH ) AND NOW()
              ORDER BY reviewdt DESC;
              ";
  $counting = \Drupal::database()->query($query)->fetchAll();

  $extr = [
    'remaining' => $remaining,
    'counting' => $counting,
  ];

  return $extr;
}

/**
 * Retrieves calling attempt data for the current and last week.
 *
 * @return array
 *   An array of calling components.
 */
function callingcomps() {

  $query = "SELECT
              weekno,
              CASE WHEN weekno = WEEK(CURDATE()) THEN 'thisweek' ELSE 'lastweek' END AS weeklbl,
              caller,
              attempts,
              callduration,
              completes
            FROM (
              SELECT
                WEEK(r.Attempt_TimeStamp) AS weekno,
                r.Asurite AS caller,
                COUNT(*) AS attempts,
                SUM(TIME_TO_SEC(TIMEDIFF(r.Attempt_TimeStamp, r.starttime)) / 60) AS callduration,
                SUM(CASE
                      WHEN r.Disposition = 'Complete (student)' THEN 1
                      WHEN r.Disposition = 'Complete (parent)' THEN 1
                      ELSE 0
                    END) AS completes
              FROM CAMO_attemptRecord r
              INNER JOIN (
                SELECT DISTINCT asurite
                FROM CAMO_User
                WHERE `role` NOT IN ('admin', '')
              ) u ON u.asurite = r.Asurite
              WHERE YEAR(r.Attempt_TimeStamp) = YEAR(CURDATE())
                AND WEEK(r.Attempt_TimeStamp) IN (WEEK(CURDATE()), WEEK(CURDATE()) - 1)
              GROUP BY r.Asurite, WEEK(r.Attempt_TimeStamp)
            ) AS summary;";

  $calling = \Drupal::database()->query($query)->fetchAll();

  return $calling;

}
