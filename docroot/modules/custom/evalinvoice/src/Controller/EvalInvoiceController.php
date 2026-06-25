<?php

namespace Drupal\evalinvoice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Datetime\DateFormatter;

/**
 * EvalInvoiceController - updated to remove deprecated helpers and superglobals.
 */
class EvalInvoiceController extends ControllerBase {

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The date.formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack service.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   Date formatter service.
   */
  public function __construct(RequestStack $request_stack, DateFormatter $date_formatter) {
    $this->requestStack = $request_stack;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('date.formatter')
    );
  }

  /**
   * Load monthly invoices / approval UI.
   */

  /**
   * Load monthly invoices / approval UI.
   */
  public function evalInvoiceLoad() {
    $request = $this->requestStack->getCurrentRequest();
    $query = $request->query;

    $baseurl = $GLOBALS['base_url'] ?? '';
    $uname = $this->currentUser()->getAccountName();
    $isEvalMgr = FALSE;
    $appmessage = '';
    $list = '';
    $name = '';

    // Get display name for current user (tokenized).
    $sqlName = "SELECT DISTINCT User_Name FROM {EvalUser} WHERE Asurite LIKE :uname";
    $name = \Drupal::database()->query($sqlName, [':uname' => $uname])->fetchField();
    $markup = '<div style="width:70%;margin:2% 15% 0 15%"><p>Welcome, ' . $this->escape($name);

    // --- Prepare project selection safely (read param once) ---
    $conditions = [];
    $args = [];
    $project = NULL;

    if ($query->has('project')) {
      $projParam = $query->get('project');
      if ($projParam !== NULL && $projParam !== 'None Selected') {
        // Use exact match; switch to LIKE and wrap with % if needed.
        $conditions[] = 'Invoice1.Project_ID = :project';
        $args[':project'] = $projParam;
        $project = $projParam;
      }
    }

    // If Approve button was pressed and a project is selected, process approvals.
    if (!empty($query->all()) && $project !== NULL && $query->has('ApproveButton') && $query->get('ApproveButton') === 'Approve') {
      $appmessage = 'Approved';
      $now = $this->dateFormatter->format(time(), 'custom', 'Y-m-d');

      $selectSql = "SELECT Invoice1.* FROM Invoice1";
      if (!empty($conditions)) {
        $selectSql .= ' WHERE ' . implode(' AND ', $conditions);
      }
      $selectSql .= " ORDER BY Invoice_Date DESC";

      $rows = \Drupal::database()->query($selectSql, $args)->fetchAll();

      // Collect approved invoice codes for batch update.
      $approved_codes = [];

      foreach ($rows as $row) {
        $invoiceCd = $row->Invoice_Code;
        $projectId = $row->Project_ID;
        $billamount = $row->{'Billing_Amount'};

        if ($query->has($invoiceCd) && $query->get($invoiceCd) === 'Approve') {
          \Drupal::database()->insert('Invoice1_Approvals')
            ->fields([
              'ApprovedDate' => $now,
              'ApprovedBy' => $uname,
              'Invoice_Code' => $invoiceCd,
              'ApprovedAmount' => $billamount,
              'Project_ID' => $projectId,
              'ApprovedByName' => $name,
            ])
            ->execute();

          $approved_codes[] = $invoiceCd;
        }
      }

      if (!empty($approved_codes)) {
        $placeholders = implode(',', array_fill(0, count($approved_codes), '?'));

        $update_sql = "UPDATE Invoice1
                      SET Approved = ?,
                          Approval_Date = ?,
                          Approver = ?,
                          ApproverName = ?
                      WHERE Invoice_Code IN ($placeholders)";

        $params = array_merge(['Yes', $now, $uname, $name], $approved_codes);

        \Drupal::database()->query($update_sql, $params);

        $approved_count = count($approved_codes);
        if ($approved_count > 0) {
          $markup .= '<p><i>' . (int) $approved_count . ' invoices have been approved</i></p>';
          // Redirect back to monthly invoices page for the selected project.
          return new RedirectResponse('/monthly-invoices?Submission=Updated&project=' . rawurlencode($project));
        }
      }
    }

    // --- Build the page intro and project list ---
    $markup .= '<p>This page is for you to review your monthly invoices, and to <b>APPROVE</b> the <b>billed amount</b> and <b>account number</b> each month.</p>';

    $sql = "SELECT DISTINCT Project_ID FROM {EvalUser} WHERE Asurite = :uname";
    $result2 = \Drupal::database()->query($sql, [':uname' => $uname]);
    $evalRows = $result2->fetchAll();

    $fproject = [];
    if (empty($evalRows)) {
      \Drupal::logger('evalinvoice')->notice('No EvalUser rows for @user', ['@user' => $uname]);
    }
    else {
      foreach ($evalRows as $evalRow) {
        if (!isset($evalRow->Project_ID) || $evalRow->Project_ID === NULL || $evalRow->Project_ID === '') {
          continue;
        }

        $projectId = $evalRow->Project_ID;
        if ($projectId === 'EvalMGR') {
          $isEvalMgr = TRUE;
          $sqlInv = "SELECT DISTINCT Project_ID FROM {Invoice1} ORDER BY Project_ID ASC";
          $result3 = \Drupal::database()->query($sqlInv);
          $invRows = $result3->fetchAll();

          foreach ($invRows as $invRow) {
            if (isset($invRow->Project_ID) && $invRow->Project_ID !== NULL && $invRow->Project_ID !== '') {
              $fproject[] = $invRow->Project_ID;
            }
          }
          break;
        }
        else {
          $fproject[] = $projectId;
        }
      }
    }

    $fproject = array_values(array_unique($fproject));

    if ($isEvalMgr) {
      $markup .= '<p>To view all evaluation invoices by month, <a href="/invoices-month">Click Here</a></p>';
    }

    $uproject = array_values(array_unique($fproject));

    // Project selector and form start.
    $markup .= '<form method="GET">';
    $markup .= '<p>Project:<select name="project"><option value="None Selected">None Selected</option>';
    foreach ($uproject as $opt) {
      $selected = ($query->has('project') && $query->get('project') === $opt) ? ' selected' : '';
      $markup .= '<option value="' . $this->escape($opt) . '"' . $selected . '>' . $this->escape($opt) . '</option>';
    }
    $markup .= '</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    $markup .= '<button name="Submission" type="submit" value="Updated" style="width: 200px; height: 30px">View</button>';

    // If a project is selected, show invoices for it (safe tokenized SELECT).
    if ($project !== NULL) {
      // Build the SELECT for the listing; reuse $conditions and $args prepared above.
      $listSql = "SELECT Invoice1.* FROM Invoice1";
      if (!empty($conditions)) {
        $listSql .= ' WHERE ' . implode(' AND ', $conditions);
      }
      $listSql .= " ORDER BY Invoice_Date DESC";

      $result = \Drupal::database()->query($listSql, $args)->fetchAll();
      $row_cnt = count($result);

      if ($row_cnt > 0) {
        $projectname = str_replace('&', '%26', $project);
        $projectname = str_replace(' ', '%20', $projectname);

        $markup .= '&nbsp;&nbsp;&nbsp;&nbsp;<a href="/billing-information?project=' . $this->escape($projectname) . '">Confirm Billing Information and Update Contacts</a><p>Please verify the billing account and billing amount, and click "Approve". If you would like to change the billing account, or have questions about the billing amount, please <a href="mailto:autumn@asu.edu?Subject=Evaluation%20Invoice%20Question" target="_top">contact us</a>.</p>';
        $markup .= '<button name="ApproveButton" type="submit" value="Approve" style="width: 200px; height: 30px; float: right;">Submit Approval</button><br>';
        $markup .= '<br><table class="invoicetable" border="1">
          <tr>
            <th>Invoice Date</th>
            <th>Month</th>
            <th>Department</th>
            <th>Account</th>
            <th>Billing Amount</th>
            <th>Approve?</th>
            <th>Pdf</th>
            <th>Details</th>
          </tr>';

        foreach ($result as $row) {
          $month = $row->{'Billing_Month'};
          $account = $row->{'Billing_Account'};
          $year = $row->{'Calendar_Year'};
          $department = $row->{'Billing_Department'};
          $cc = $row->{'CostCenter'};
          $bn = $row->{'BillingNumber'};
          if (!is_null($cc) && $cc !== '') {
            $account = $cc . ' (' . $bn . ')';
          }
          $invoicedt = $row->Invoice_Date;
          $billamount = $row->{'Billing_Amount'};
          $invoicecode = $row->Invoice_Code;
          $approveddisp = $row->Approved;
          $approvedt = $row->Approval_Date;
          $projectVal = $row->Project_ID;
          $approvername = $row->ApproverName;

          if ($approveddisp === 'Yes') {
            $date = date_create($approvedt);
            $approvedate = date_format($date, 'm/d/Y');
            $approved = 'Approved ' . $approvedate;
            if (!is_null($approvername)) {
              $approved .= ' by ' . $approvername;
            }
          }
          else {
            $approved = '<input type="checkbox" name="' . $this->escape($invoicecode) . '" value="Approve">';
          }

          $markup .= '<tr><td>' . $this->escape($invoicedt) . '</td><td>' . $this->escape($month) . ', ' . $this->escape($year) . '</td><td>' . $this->escape($department) . '</td><td>' . $this->escape($account) . '</td><td align="right">' . $this->escape($billamount) . '</td><td>' . $approved . '</td><td>';

          $filename = 'public://docs/Evaluation/InvoicePDFs/' . $invoicecode . '_' . $projectVal . '.pdf';
          $filename_url = \Drupal::service('file_url_generator')->generateString($filename);
          if (file_exists($filename)) {
            $markup .= '<a href="' . $this->escape($filename_url) . '" target="_blank">PDF</a>';
          }
          $markup .= '</td><td><a href="/invoice-details?page=month&month=' . rawurlencode($month) . '&year=' . rawurlencode($year) . '&project=' . rawurlencode($projectname) . '">Details</a></td></tr>';
        }
        $markup .= '</table>';
      }
    }

    $markup .= '</form>';

    // If Approve was pressed, add JS hook (keeps original behavior).
    if (!empty($query->all()) && $project !== NULL && $query->has('ApproveButton') && $query->get('ApproveButton') === 'Approve') {
      $markup .= '<script type="text/javascript">SendLinkByMail();</script>';
    }

    return [
      '#cache' => [
        'contexts' => ['user'],
      ],
      '#attached' => [
        'library' => ['evalinvoice/evalinvoice'],
      ],
      '#type' => 'markup',
      '#markup' => Markup::create($markup),
    ];
  }

  /**
   * Load month view.
   */
  public function evalMonthLoad() {
    $request = $this->requestStack->getCurrentRequest();
    $query = $request->query;

    $uname = $this->currentUser()->getAccountName();

    // Get evaluator rows for this user.
    $query2 = "SELECT DISTINCT User_Name, Project_ID FROM {EvalUser} WHERE Asurite LIKE :uname";
    $result2 = \Drupal::database()->query($query2, [':uname' => $uname]);

    $markup = '<div style="width:70%;margin:2% 15% 0 15%">';
    $projcheck = NULL;
    $name = NULL;
    foreach ($result2 as $row) {
      $name = $row->User_Name;
      $projcheck = $row->Project_ID;
    }

    if ($projcheck === 'EvalMGR') {
      $markup .= '<p>Welcome, ' . $this->escape($name);

      // Read month/year query params once.
      $monthParam = $query->has('month') ? $query->get('month') : NULL;
      $yearParam = $query->has('year') ? $query->get('year') : NULL;

      // Prepare conditions and args for listing / approval SELECTs.
      $conditions = [];
      $args = [];

      if ($monthParam !== NULL && $monthParam !== 'None Selected') {
        // Use LIKE to preserve original behavior; change to '=' if you want exact match.
        $conditions[] = "`Billing_Month` LIKE :month";
        $args[':month'] = $monthParam;
      }

      if ($yearParam !== NULL && $yearParam !== 'None Selected') {
        $conditions[] = "`Calendar_Year` LIKE :year";
        $args[':year'] = $yearParam;
      }

      // Approval flow: if form submitted and Approve button pressed and month selected.
      if (!empty($query->all()) && $monthParam !== NULL && $monthParam !== 'None Selected' && $query->has('ApproveButton') && $query->get('ApproveButton') === 'Approve') {
        $now = $this->dateFormatter->format(time(), 'custom', 'Y-m-d');

        // Build tokenized SELECT for candidate invoices.
        $selectSql = "SELECT Invoice1.* FROM Invoice1";
        if (!empty($conditions)) {
          $selectSql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $result = \Drupal::database()->query($selectSql, $args)->fetchAll();

        $approved_codes = [];

        foreach ($result as $row) {
          $invoiceCd = $row->Invoice_Code;
          $projectId = $row->Project_ID;
          $billamount = $row->{'Billing_Amount'};

          if ($query->has($invoiceCd) && $query->get($invoiceCd) === 'Approve') {
            try {
              \Drupal::database()->insert('Invoice1_Approvals')
                ->fields([
                  'ApprovedDate' => $now,
                  'ApprovedBy' => $uname,
                  'Invoice_Code' => $invoiceCd,
                  'ApprovedAmount' => $billamount,
                  'Project_ID' => $projectId,
                  'ApprovedByName' => $name,
                ])
                ->execute();
            }
            catch (\PDOException $e) {
              $this->messenger()->addError($this->t('Error: @message', [':message' => $e->getMessage()]));
            }

            $approved_codes[] = $invoiceCd;
          }
        }

        // Batch-update Invoice1 for approved codes using positional placeholders.
        if (!empty($approved_codes)) {
          $placeholders = implode(',', array_fill(0, count($approved_codes), '?'));

          $update_sql = "UPDATE Invoice1
                        SET Approved = ?,
                            Approval_Date = ?,
                            Approver = ?,
                            ApproverName = ?
                        WHERE `Billing_Month` LIKE ? AND `Calendar_Year` LIKE ? AND Invoice_Code IN ($placeholders)";

          // Fixed params (Approved, date, approver, approverName, month, year) then codes.
          $params = array_merge(
            ['Yes', $now, $uname, $name, $monthParam, $yearParam],
            $approved_codes
          );

          \Drupal::database()->query($update_sql, $params);

          $approved_count = count($approved_codes);
          if ($approved_count > 0) {
            $markup .= '<p><i>' . (int) $approved_count . ' invoices have been approved</i></p>';
            $month = rawurlencode($monthParam) . '&year=' . rawurlencode($yearParam);
            return new RedirectResponse('/invoices-month?Submission=Updated&month=' . $month);
          }
        }
      }

      $markup .= '<p>This page is for you to review your monthly invoices, and to <b>APPROVE</b> the <b>billed amount</b> and <b>account number</b> each month.</p><p>To return to the invoices home page, <a href="/monthly-invoices">Click Here</a></p>';

      // Build month/year list for selector (static query; safe).
      $query3 = "SELECT `Billing_Month`, `Calendar_Year`
        FROM (
            SELECT DISTINCT `Billing_Month`, `Calendar_Year`, `Billing_Month_Number`
            FROM Invoice1
        ) AS sub
        ORDER BY `Billing_Month_Number`, `Calendar_Year` ASC;";
      $result3 = \Drupal::database()->query($query3);
      $fmonth = [];
      $fyear = [];
      foreach ($result3 as $row) {
        $fmonth[] = $row->{'Billing_Month'};
        $fyear[] = $row->{'Calendar_Year'};
      }

      $uyear = array_values(array_unique($fyear));
      $umonth = array_values(array_unique($fmonth));

      $markup .= '<form method="GET">';
      $markup .= '<p>Month:<select name="month"><option value="None Selected">None Selected</option>';
      foreach ($umonth as $opt) {
        $selected = ($monthParam !== NULL && $monthParam === $opt) ? ' selected' : '';
        $markup .= '<option value="' . $this->escape($opt) . '"' . $selected . '>' . $this->escape($opt) . '</option>';
      }
      $markup .= '</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
      $markup .= '<p>Year:<select name="year"><option value="None Selected">None Selected</option>';
      foreach ($uyear as $opt) {
        $selected = ($yearParam !== NULL && $yearParam === $opt) ? ' selected' : '';
        $markup .= '<option value="' . $this->escape($opt) . '"' . $selected . '>' . $this->escape($opt) . '</option>';
      }
      $markup .= '</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
      $markup .= '<button name="Submission" type="submit" value="Updated" style="width: 200px; height: 30px">View</button></p>';

      // If month selected, show invoices for that month/year.
      if ($monthParam !== NULL && $monthParam !== 'None Selected') {
        $listSql = "SELECT Invoice1.* FROM Invoice1";
        $listConditions = [];
        $listArgs = [];

        $listConditions[] = "`Billing_Month` LIKE :month";
        $listArgs[':month'] = $monthParam;

        $listConditions[] = "`Calendar_Year` LIKE :year";
        $listArgs[':year'] = $yearParam;

        if (!empty($listConditions)) {
          $listSql .= ' WHERE ' . implode(' AND ', $listConditions);
        }
        $listSql .= " ORDER BY Project_ID ASC";

        $rows = \Drupal::database()->query($listSql, $listArgs)->fetchAll();
        $row_cnt = count($rows);

        if ($row_cnt > 0) {
          $markup .= '<p>Please verify the billing account and billing amount, and click "Approve". If you would like to change the billing account, or have questions about the billing amount, please <a href="mailto:Heather.Fauland@asu.edu?Subject=Evaluation%20Invoice%20Question" target="_top">contact us</a>.</p>';
          $markup .= '<button name="ApproveButton" type="submit" value="Approve" style="width: 200px; height: 30px; float: right;">Submit Approval</button><br>';
          $markup .= '<br><table class="invoicetable" border="1">
                        <tr><th>Project ID</th>
                        <th>Invoice Date</th>
                        <th>Month</th>
                        <th>Department</th>
                        <th>Account</th>
                        <th>Billing Amount</th>
                        <th>Approve?</th>
                        <th>Pdf</th>
                        <th>Details</th>
                        </tr>';

          foreach ($rows as $row) {
            $month = $row->{'Billing_Month'};
            $year = $row->{'Calendar_Year'};
            $department = $row->{'Billing_Department'};
            $account = $row->Billing_Account;
            $invoicedt = $row->Invoice_Date;
            $billamount = $row->{'Billing_Amount'};
            $invoicecode = $row->Invoice_Code;
            $approveddisp = $row->Approved;
            $approvedt = $row->Approval_Date;
            $projectVal = $row->Project_ID;
            $approvername = $row->ApproverName;

            if ($approveddisp === 'Yes') {
              $date = date_create($approvedt);
              $approvedate = date_format($date, 'm/d/Y');
              $approved = 'Approved ' . $approvedate;
              if (!is_null($approvername)) {
                $approved .= ' by ' . $approvername;
              }
            }
            else {
              $approved = '<input type="checkbox" name="' . $this->escape($invoicecode) . '" value="Approve">';
            }

            $markup .= '<tr><td>' . $this->escape($projectVal) . '</td><td>' . $this->escape($invoicedt) . '</td><td>' . $this->escape($month) . ', ' . $this->escape($year) . '</td><td>' . $this->escape($department) . '</td><td>' . $this->escape($account) . '</td><td align="right">' . $this->escape($billamount) . '</td><td>' . $approved . '</td><td>';

            // Use stream wrapper -> realpath and file_url_generator to create browser URL.
            $uri = 'public://docs/Evaluation/InvoicePDFs/' . $invoicecode . '_' . $projectVal . '.pdf';
            $realpath = \Drupal::service('file_system')->realpath($uri);
            if ($realpath && file_exists($realpath)) {
              $filename_url = \Drupal::service('file_url_generator')->generateString($uri);
              $markup .= '<a href="' . $this->escape($filename_url) . '" target="_blank">PDF</a>';
            }

            $markup .= '</td><td><a href="/invoice-details?page=month&month=' . rawurlencode($month) . '&year=' . rawurlencode($year) . '&project=' . rawurlencode($projectVal) . '">Details</a></td></tr>';
          }
          $markup .= '</table>';
        }
      }
      $markup .= '</form></div>';
    }

    return [
      '#cache' => [
        'contexts' => ['user'],
      ],
      '#attached' => [
        'library' => ['evalinvoice/evalinvoice'],
      ],
      '#type' => 'markup',
      '#markup' => Markup::create($markup),
    ];
  }

  /**
   * Load invoice details (evhours).
   */
  public function detailsLoad() {
    $request = $this->requestStack->getCurrentRequest();
    $query = $request->query;

    $uname = $this->currentUser()->getAccountName();

    $project = $query->get('project');
    $year = $query->get('year');
    $month = $query->get('month');
    $page = $query->get('page');

    $projectname = str_replace('&', '%26', $project);
    $projectname = str_replace(' ', '%20', $project);
    $markup = '<div style="width:70%;margin:2% 15% 0 15%">';
    if ($page === 'month') {
      $markup .= '<a href="/invoices-month?month=' . rawurlencode($month) . '&year=' . rawurlencode($year) . '&Submission=Updated">Return to previous page</a><br>';
    }
    else {
      $markup .= '<a href="/monthly-invoices?project=' . rawurlencode($projectname) . '">Return to previous page</a><br>';
    }
    $markup .= '<h2>' . $this->escape($project) . ', ' . $this->escape($month) . ' ' . $this->escape($year) . '</h2>';

    $queryStr = "SELECT * FROM {EvalHours} Where `Billing_Month` like :month AND `Calendar_Year` like :year AND Project like :project";
    $result = \Drupal::database()->query($queryStr, [
      ':month' => $month,
      ':year' => $year,
      ':project' => $project,
    ]);
    $result = $result->fetchAll();
    $row_cnt = count($result);

    if ($row_cnt > 0) {
      $markup .= '<br><table class="invoicetable" border="1">
          <tr>
            <th>Task</th>
            <th>Description</th>
            <th style="text-align:right; white-space:nowrap;">Hours</th>
            <th style="text-align:right; white-space:nowrap;">Billing Amount</th>
          </tr>';

      foreach ($result as $row) {
        $taskdescr = $row->{'Task_Description'};
        $billamount = $row->{'Task_Billing_Amount'};
        $hours = number_format((float) $row->Hours, 2);
        $task = $row->Task;

        $markup .= '<tr><td>' . $this->escape($task) . '</td><td>' . $this->escape($taskdescr) . '</td><td align="right">' . $this->escape($hours) . '</td><td align="right">$' . number_format($billamount, 2) . '</td></tr>';
      }
    }

    $markup .= '</table></div>';

    return [
      '#cache' => [
        'contexts' => ['user'],
      ],
      '#attached' => [
        'library' => ['evalinvoice/evalinvoice'],
      ],
      '#type' => 'markup',
      '#markup' => Markup::create($markup),
    ];
  }

  /**
   * Escapes strings for safe HTML output in legacy markup building.
   *
   * @param string|null $value
   *   The value to escape.
   *
   * @return string
   *   Escaped string (empty string if null).
   */
  protected function escape($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
  }

}
