<?php

namespace Drupal\asu_tuition\Form;

use Drupal\Core\Database\StatementInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Database\Query\Condition;
use Drupal\asu_tuition\Service\QueryLogger;

/**
 * Form for querying ASU tuition tables.
 */
class AsuTuitionQueryTablesForm extends ConfigFormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;
  protected QueryLogger $queryLogger;

  /**
   * Constructs a new AsuTuitionQueryTablesForm.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\asu_tuition\Service\QueryLogger $query_logger
   *   The query logger service.
   */
  public function __construct(Connection $connection, ConfigFactoryInterface $config_factory, QueryLogger $query_logger) {
    parent::__construct($config_factory);
    $this->connection = $connection ?? Database::getConnection();
    $this->queryLogger = $query_logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('config.factory'),
      $container->get('asu_tuition.query_logger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'asu_tuition_admin_table_query_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'asu_tuition.admin_table_query_settings',

    ];
  }

  /**
   *
   */
  protected function saveQueryHistoryToConfig($query_obj, $table_name = NULL) {
    // Normalize table name for the config key.
    $table_name = $table_name ?: 'general';
    $config_name = 'asu_tuition.' . $table_name . '_query_history';
    $config = \Drupal::configFactory()->getEditable($config_name);

    $args = [];
    $sql = '';

    // If it's a Query object (SelectQuery, UpdateQuery, etc.), it exposes arguments().
    if (is_object($query_obj) && method_exists($query_obj, 'arguments')) {
      try {
        $args = $query_obj->arguments() ?? [];
      }
      catch (\Throwable $e) {
        $args = [];
      }

      try {
        // __toString() gives the SQL with placeholders.
        $sql = (string) $query_obj;
      }
      catch (\Throwable $e) {
        $sql = '[query object — SQL not available]';
      }
    }
    // If it's a StatementInterface (already executed) we can't get bound args reliably.
    elseif (is_object($query_obj) && $query_obj instanceof StatementInterface) {
      try {
        // Casting a statement to string may or may not provide SQL depending on the driver.
        $sql = (string) $query_obj;
      }
      catch (\Throwable $e) {
        $sql = '[statement object — SQL not available]';
      }
      // No bound args available from statement wrapper.
      $args = [];
    }
    // If caller passed SQL string directly.
    elseif (is_string($query_obj)) {
      $sql = $query_obj;
      $args = [];
    }
    // Fallback for anything else (arrays, primitives, etc.)
    else {
      $sql = print_r($query_obj, TRUE);
      $args = [];
    }

    // Try to replace placeholders with quoted values for readability if we have args.
    if (!empty($args) && is_array($args)) {
      foreach ($args as $key => $value) {
        $quoted = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
        // Some drivers use :name placeholders, others use ? (numeric). Do best-effort replace.
        $sql = str_replace($key, $quoted, $sql);
      }
    }

    // Build record.
    $record = [
      'query' => $sql,
      'time'  => date('Y-m-d H:i:s'),
      'user'  => \Drupal::currentUser()->getDisplayName(),
    ];

    // Get existing history (array)
    $history_key = $table_name . '_query_history';
    $history = $config->get($history_key) ?? [];

    // Keep only last 10.
    array_unshift($history, $record);
    $history = array_slice($history, 0, 10);

    // Save back to config.
    $config->set($history_key, $history)->save();

    return TRUE;
  }

  /**
   * Builds the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $table_name = NULL) {

    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
    $table = $path_args[6];
    $full_table = 'asu_tuition_' . $path_args[6];

    // Add table names.
    /*$entities = \Drupal::service('getEntityInfo')->getEntityInfo();
    ksort($entities);
    foreach ($entities as $t_name => $table_values) {
    $table_name = $t_name;
    $explode_table = explode('asu_tuition_', $table_name);
    $explode_table_name = $explode_table[1];
    // ksm($explode_table);
    $form['tablenames'][$table_name] = [
    '#markup' => t("<button class='button'><a href='/admin/config/content/tuition/tables/$explode_table_name'>$explode_table_name</a></button>&nbsp;&nbsp;"),

    ];
    } */

    $insert_url = Url::fromUri('internal:/admin/config/content/tuition/tables/' . $table . '/insert')->toString();

    // Enable CSRF token protection for this form.
    $form['#token'] = $this->getFormId();

    $updateTime = \Drupal::state()->get($full_table . '_last_updated');
    $updateUser = \Drupal::state()->get($full_table . '_last_updated_by');
    $formatted = $updateTime ? \Drupal::service('date.formatter')->format(
      $updateTime,
      'custom',
      'm-d-Y H:i a'
    ) : 'unknown time';

    $data_count = $this->connection->select($full_table, 't')
      ->countQuery()
      ->execute()
      ->fetchField();

    $markup_text = '<h3>' . $table . ' data</h3>'
    . '<div><a href="' . Url::fromUri('internal:/admin/config/content/tuition/tables/' . $table)->toString() . '">' . $this->t('Back to table') . '</a></div>'
    . '<p>This table has ' . $data_count . ' records.</p>'
    . '<p>This table was last updated on ' . $formatted . ' by ' . $updateUser . '</p>'
    . '<p>If you would like to insert data into this table, <a href="' . $insert_url . '">click here</a>.</p>';

    $form['back_link'] = [
      '#type' => 'markup',
      '#markup' => $markup_text,
    ];

    // Get table fields.
    $fields = \Drupal::service('editTableFields')->editTableFields($full_table);
    // dpm($fields);
    foreach ($fields as $field_name => $field) {
      $fieldoptions[$field_name] = $field_name;
    }

    // Used for processing form.
    $form['table'] = [
      '#type' => 'hidden',
      '#value' => $full_table,
    ];

    /* $form['describe_table'] = [
    '#type' => 'submit',
    '#value' => $this->t('Describe @table table', ['@table' => $table]),
    '#submit' => ['::describeSubmit'],
    '#limit_validation_errors' => [],
    ]; */

    // Default visibility states.
    $show_table = $form_state->get('describe_table_visible') ?? FALSE;
    $show_button = $form_state->get('describe_button_visible') ?? TRUE;

    // Only show the "Describe" button if allowed.
    if ($show_button) {
      $form['describe_table'] = [
        '#type' => 'submit',
        '#value' => $this->t('Describe @table table', ['@table' => $table_name]),
        '#submit' => ['::describeSubmit'],
        '#attributes' => ['class' => ['button--primary']],
        '#limit_validation_errors' => [],
      ];
    }
    else {
      $form['describe_table_hidden'] = [
        '#type' => 'submit',
        '#value' => $this->t('Hide @table description', ['@table' => $table_name]),
        '#submit' => ['::hideDescribeSubmit'],
        '#attributes' => ['class' => ['button--primary']],
        '#limit_validation_errors' => [],
      ];
    }

    // If describe info exists, show the rendered table.
    if ($show_table && $table_info = $form_state->get('describe_table')) {
      $form['describe_output'] = $table_info;
    }

    // Default visibility states.
    $show_history = $form_state->get('query_history_visible') ?? FALSE;
    $show_history_button = $form_state->get('query_history_button_visible') ?? TRUE;

    if ($show_history_button) {
      $form['query_history'] = [
        '#type' => 'submit',
        '#value' => $this->t('Show query history for @table table', ['@table' => $table_name]),
        '#submit' => ['::queryHistorySubmit'],
        '#attributes' => ['class' => ['button--primary']],
        '#limit_validation_errors' => [],
      ];
    }
    else {
      $form['hide_query_history'] = [
        '#type' => 'submit',
        '#value' => $this->t('Hide query history for @table table', ['@table' => $table_name]),
        '#submit' => ['::hideQueryHistorySubmit'],
        '#attributes' => ['class' => ['button--primary']],
        '#limit_validation_errors' => [],
      ];
    }
    if ($show_history && $history_info = $form_state->get('query_history')) {
      $form['query_history_output'] = $history_info;
    }

    $form['field_count'] = [
      '#type' => 'hidden',
      '#value' => count($fieldoptions),
    ];

    $form['query_operations'] = [
      '#type' => 'select',
      '#title' => $this->t('Database table operations'),
      '#description' => $this->t('Select the operation you want to perform on the table.'),
      '#options' => [
        '' => $this->t('- None-'),
        'select' => $this->t('Select'),
        'update' => $this->t('Update'),
        'delete' => $this->t('Delete'),
        'truncate' => $this->t('Truncate'),
      ],
      // Pre-selected value.
      '#default_value' => '',
      '#required' => TRUE,
    ];

    $form['auto_increment'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Rest ID and auto-increment field'),
      '#description' => $this->t('Check this box if the table has an auto-increment field that you want to reset when truncating the table.'),
      '#default_value' => FALSE,
      '#states' => [
        // Only show this checkbox if the user has selected "truncate" in operations.
        'visible' => [
          ':input[name="query_operations"]' => ['value' => 'truncate'],
        ],
      ],
    ];

    $form['confirm_truncate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I confirm that I want to truncate this table.'),
      '#description' => $this->t('This action cannot be undone. Make sure you have backed up your data.'),
      '#default_value' => FALSE,
      '#states' => [
        // Only show this checkbox if the user has selected "truncate" in operations.
        'visible' => [
          ':input[name="query_operations"]' => ['value' => 'truncate'],
        ],
      ],
    ];

    /** Joins section
     * This section allows users to join other tables.
     */
    $form['join-check'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Joins'),
      '#description' => $this->t('Check this box if you want to join other tables.'),
      '#default_value' => FALSE,
      '#states' => [
        'visible' => [
          [':input[name="query_operations"]' => ['value' => 'select']],
          [':input[name="query_operations"]' => ['value' => 'update']],
        ],
      ],
    ];

    $form['joins'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Join Tables'),
      '#tree' => TRUE,
      '#collapsible' => TRUE,
      '#description' => $this->t('You can join up to 3 tables.'),
      '#states' => [
        'visible' => [
      [':input[name="join-check"]' => ['checked' => TRUE]],
     // [':input[name="query_operations"]' => ['value' => 'select']],
     // [':input[name="query_operations"]' => ['value' => 'update']],
        ],
      ],
    ];

    // Allow upto 3 joins.
    for ($j = 0; $j < 3; $j++) {
      $form['joins'][$j]['join_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Join Type'),
        '#options' => [
          'INNER' => 'INNER JOIN / JOIN',
          'LEFT' => 'LEFT JOIN',
        ],
        '#prefix' => '<div class="joins-row">',
      ];

      $form['joins'][$j]['join_table'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Table to Join'),
        '#description' => $this->t('Enter the name of the table to join.'),
      ];

      $form['joins'][$j]['base_field'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Base Table Field'),
        '#description' => $this->t('Field from the main table.'),
      ];

      $form['joins'][$j]['join_field'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Join Table Field'),
        '#description' => $this->t('Field from the joined table.'),
        '#suffix' => '</div>',
      ];
    }

    /** Conditions section for select and delete operations.
     * This section allows users to define conditions for their queries.
     */
    $form['conditions-check'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Conditions'),
      '#description' => $this->t('Check this box if you want to add conditions to your query.'),
      '#default_value' => FALSE,
      '#states' => [
        'visible' => [
          [':input[name="query_operations"]' => ['value' => 'select']],
          [':input[name="query_operations"]' => ['value' => 'update']],
          [':input[name="query_operations"]' => ['value' => 'delete']],
        ],
      ],
    ];

    $form['conditions'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Search Conditions'),
      //'#description' => $this->t('Note: Choose operator, otherwise results will not be correct. You must select an operator for each condition.'),
      '#tree' => TRUE,
      '#collapsible' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="conditions-check"]' => ['checked' => TRUE],
        ],
      ],
      '#attributes' => ['class' => ['conditions-fieldset']],
    ];

    // Loop through condition rows.
    for ($i = 0; $i < sizeof($fieldoptions); $i++) {
      $form['conditions'][$i]['field'] = [
        '#type' => 'select',
        '#title' => $this->t('Field'),
        '#options' => array_combine($fieldoptions, $fieldoptions),
        '#prefix' => '<div class="condition-row">',
      ];

      $form['conditions'][$i]['operator'] = [
        '#type' => 'select',
        '#title' => $this->t('Operator'),
        '#options' => [
          //'' => 'select operator',
          '=' => '=',
          '!=' => '!=',
          'LIKE' => 'LIKE',
          '<' => '<',
          '>' => '>',
          '<=' => '<=',
          '>=' => '>=',
          'IN' => 'IN',
          'NULL' => 'NULL',
          'IS NOT NULL' => 'IS NOT NULL',
        ],
      ];

      $form['conditions'][$i]['value'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Value'),

      ];

      $form['conditions'][$i]['logic'] = [
        '#type' => 'select',
        '#title' => $this->t('Logical Operator'),
        '#options' => [
          'AND' => $this->t('AND'),
          'OR' => $this->t('OR'),
        ],
        '#default_value' => '',
        '#description' => $this->t('Choose how to combine multiple conditions.'),
        '#suffix' => '</div>',
      ];
    }

    /** Update section.
     * This section allows users to define fields to update.
     */
    $form['update_fields_info'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Fields to update'),
      '#tree' => TRUE,
      '#collapsible' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="query_operations"]' => ['value' => 'update'],
        ],
      ],
    ];

    // Loop through condition rows.
    for ($j = 0; $j < sizeof($fieldoptions); $j++) {
      $form['update_fields_info'][$j]['field'] = [
        '#type' => 'select',
        '#title' => $this->t('Field'),
        '#description' => $this->t('Select the field to update.'),
        '#options' => array_combine($fieldoptions, $fieldoptions),
        '#prefix' => '<div class="condition-row">',
      ];

      $form['update_fields_info'][$j]['value'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Value'),
        '#description' => $this->t('Enter the value to update.'),
      ];

      $form['update_fields_info'][$j]['update_conditiond_fields_info']['update_condition_field'] = [
        '#type' => 'select',
        '#title' => $this->t('Field'),
        '#description' => $this->t('Select the field to use in thse condition.'),
        '#options' => array_combine($fieldoptions, $fieldoptions),
        '#prefix' => '<div>Where</div>',
      ];

      $form['update_fields_info'][$j]['update_conditiond_fields_info']['update_condition_operator'] = [
        '#type' => 'select',
        '#title' => $this->t('Condition Operator'),
        '#options' => [
          '' => 'Choose operator',
          '=' => '=',
          '!=' => '!=',
          'LIKE' => 'LIKE',
          '<' => '<',
          '>' => '>',
          '<=' => '<=',
          '>=' => '>=',
          'IN' => 'IN',
          'NULL' => 'NULL',
          'IS NOT NULL' => 'IS NOT NULL',
        ],
      ];

      $form['update_fields_info'][$j]['update_conditiond_fields_info']['update_condition_value'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Value'),
        '#description' => $this->t('Enter the value for the condition.'),
        '#suffix' => '</div>',
      ];
    }

    $form['distinct'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Distinct Field'),
      '#description' => $this->t('Select a field to get distinct values.'),
      '#options' => array_combine($fieldoptions, $fieldoptions),
      // '#multiple' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="query_operations"]' => ['value' => 'select'],
        ],
      ],
    ];

    $form['max_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Get Maximum Value'),
      '#description' => $this->t('Select a field to get the maximum value.'),
      '#options' => array_merge(['' => $this->t('- None -')], array_combine($fieldoptions, $fieldoptions)),
      '#states' => [
        'visible' => [
          ':input[name="query_operations"]' => ['value' => 'select'],
        ],
      ],
    ];

    /** Form actions.
     * This section contains the actions that can be performed on the form.
     */
    $form['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['class' => ['container-inline']],
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['Submit_one'] = [
      '#type' => 'submit',
      '#value' => t('Download CSV file'),
      '#submit' => [[$this, 'submitForm']],
      '#attributes' => ['id' => 'execute-query'],
    // Custom submit handler.
      '#submit' => ['::csvSubmit'],
      '#states' => [
        'visible' => [
          ':input[name="query_operations"]' => ['value' => 'select'],
        ],
      ],
    ];

    $form['actions']['Submit_two'] = [
      '#type' => 'submit',
      '#value' => t('Json output data'),
      '#submit' => [[$this, 'submitForm']],
      '#attributes' => ['id' => 'json-query'],
    // Custom submit handler.
      '#submit' => ['::jsonSubmit'],
      '#states' => [
        'visible' => [
          ':input[name="query_operations"]' => ['value' => 'select'],
        ],
      ],
    ];

    $form['actions']['Submit_three'] = [
      '#type' => 'submit',
      '#value' => t('Truncate Table'),
      '#submit' => [[$this, 'submitForm']],
      '#attributes' => ['id' => 'truncateexecute-query'],
    // Custom submit handler.
      '#submit' => ['::truncateSubmit'],
      '#attributes' => [
        'onclick' => 'return confirm("Are you sure you want to truncate the table?");',
      ],
      '#states' => [
        'visible' => [
          ':input[name="confirm_truncate"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['actions']['Submit_four'] = [
      '#type' => 'submit',
      '#value' => t('Update data'),
      '#submit' => [[$this, 'submitForm']],
      '#attributes' => ['id' => 'update-query'],
    // Custom submit handler.
      '#submit' => ['::updateSubmit'],
      '#states' => [
        'visible' => [
          ':input[name="query_operations"]' => ['value' => 'update'],
        ],
      ],
    ];

    $form['actions']['Submit_five'] = [
      '#type' => 'submit',
      '#value' => t('Delete data'),
      '#submit' => [[$this, 'submitForm']],
      '#attributes' => ['id' => 'delete-execute-query'],
    // Custom submit handler.
      '#submit' => ['::deleteSubmit'],
      '#attributes' => [
        'onclick' => 'return confirm("Are you sure you want to delete the data?");',
      ],
      '#states' => [
        'visible' => [
          ':input[name="conditions-check"]' => ['checked' => TRUE],
          ':input[name="query_operations"]' => ['value' => 'delete'],
        ],
      ],
    ];

    $form['actions']['Submit_six'] = [
      '#type' => 'submit',
      '#value' => t('Insert data'),
      '#submit' => [[$this, 'submitForm']],
      '#attributes' => ['id' => 'insert-execute-query'],
    // Custom submit handler.
      '#submit' => ['::insertSubmit'],
      '#attributes' => [
        'onclick' => 'return confirm("Are you sure you want to insert the data?");',
      ],
      '#states' => [
        'visible' => [
          ':input[name="query_operations"]' => ['value' => 'insert'],
        ],
      ],
    ];

    // Attach inline JS behavior.
    $form['#attached']['library'][] = 'asu_tuition/tuitionAdminPage';

    return $form;
  }

  /**
   *
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate the SQL query.
    // 1 Get the SQL query from the form state.
    // 2️ Ensure it's a SELECT query.
    // 3️ Block dangerous SQL keywords.
    // 4️ Set an error if validation fails.
    // $sql = strtoupper(trim($form_state->getValue('query')));.
    $operation = strtoupper(trim($form_state->getValue('query_operations')));
    // dpm($operation);
    // 1️ Ensure it's a SELECT query.
    /* if ((stripos($sql, 'select') !== 0) || strpos($sql, 'TRUNCATE') !== 0) {
    // $form_state->setErrorByName('sql_query', $this->t('Only SELECT and TRUNCATE queries are allowed.'));
    }*/

    // Allow TRUNCATE but require checkbox.
    /*if (strpos($sql, 'TRUNCATE') === 0) {
    if (!$form_state->getValue('confirm_truncate')) {
    $form_state->setErrorByName('confirm_truncate', $this->t('You must confirm to run TRUNCATE queries.'));
    }
    return;
    }

    // 2️ Block dangerous SQL keywords
    /*if (preg_match('/\b(drop|delete|update|insert|alter|truncate)\b/i', $sql)) {
    $form_state->setErrorByName('sql_query', $this->t('Dangerous queries are not allowed.'));
    } */

  }

  /**
   * {@inheritdoc}
   */
  public function csvSubmit(array &$form, FormStateInterface $form_state) {
    $table = $form_state->getValue('table');

    $sql_query = $this->connection->select($table, 't')->fields('t');
    $operation = $form_state->getValue('query_operations');
    $conditions = $form_state->getValue('conditions');
    $distinct_fields = array_filter($form_state->getValue('distinct') ?? []);

    $count_field = $form_state->getValue('count');
    $max_field = $form_state->getValue('max_field');

    // $joins = $form_state->getValue('joins');
    $joins = $form_state->getValue('joins');
    // Filter out empty joins.
    $joins = array_filter($joins, function ($join) {
      return !empty($join['join_table']) && !empty($join['base_field']) && !empty($join['join_field']);
    });

    // Reindex array keys if needed.
    $joins = array_values($joins);
    $result = $this->getAllResults($table, $conditions, $operation, $insert_data = [], $joins, $distinct_fields, $count_field, $max_field, $batch_mode = TRUE);

    // dpm($result);
    if (empty($result)) {
      //  Use messenger instead of setErrorByName()
      $this->messenger()->addWarning($this->t('No results found for this query.'));
      return;
    }

    //  Generate CSV
    $response = new StreamedResponse(function () use ($result) {
      $output = fopen('php://output', 'w');
      fputcsv($output, array_keys((array) reset($result)));
      foreach ($result as $row) {
        fputcsv($output, (array) $row);
      }
      fclose($output);
    });
    // Get current date and time.
    $current_date = date('Y-m-d:h-ia');
    // Set the filename for download.
    $file_download_name = $table . '_query_results.' . $current_date . '.csv';
    // Test.
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $file_download_name . '"');
    $response->send();
    exit;
  }

  /**
   *
   */
  public function jsonSubmit(array &$form, FormStateInterface $form_state) {

    /* $table = $form_state->getValue('table');

    $sql_query = $this->connection->select($table, 't')->fields('t');
    $operation = $form_state->getValue('query_operations');
    $conditions = $form_state->getValue('conditions');
    $distinct_fields = array_filter($form_state->getValue('distinct') ?? []);

    $count_field = $form_state->getValue('count');
    $max_field = $form_state->getValue('max_field');

    $joins = $form_state->getValue('joins');
    // Filter out empty joins.
    $joins = array_filter($joins, function ($join) {
    return !empty($join['join_table']) && !empty($join['base_field']) && !empty($join['join_field']);
    });

    // Reindex array keys if needed.
    $joins = array_values($joins);

    $result = $this->getAllResults($table, $conditions, $operation, $insert_data = [], $joins, $distinct_fields, $count_field, $max_field, $batch_mode = TRUE);
    $count = is_array($result) ? count($result) : 0;
    $response_data = [
    'count' => $count,
    'data' => $result,
    ];

    $response = new JsonResponse($response_data);
    $response->send();

    exit; */
    $table = $form_state->getValue('table');

    $conditions = $form_state->getValue('conditions') ?? [];
    $joins = array_values(array_filter($form_state->getValue('joins') ?? [], function ($j) {
      return !empty($j['join_table']) && !empty($j['base_field']) && !empty($j['join_field']);
    }));
    $distinct_fields = array_filter($form_state->getValue('distinct') ?? []);

    // Build the SelectQuery (does not execute).
    $query = $this->buildSelectQuery($table, $conditions, $joins, $distinct_fields);

    $qarg = $query->getArguments();
    $qsql = $query->__toString();

    foreach ($qarg as $qkey => $qvalue) {
      $qquoted = is_numeric($qvalue) ? $qvalue : "'" . addslashes($qvalue) . "'";
      $qsql = str_replace($qkey, $qquoted, $qsql);
    }
    $this->queryLogger->saveQueryHistoryToConfig($qsql, $table);
    $this->messenger()->addStatus($this->t('Following query executed: @query', [
      '@query' => $qsql,
    ]));
    // Count total rows efficiently.
    $total = (int) $query->countQuery()->execute()->fetchField();

    // Set chunk size (tune for your environment).
    $chunk_size = 1000;

    // Stream JSON with count in the body (object with count + data array).
    $response = new StreamedResponse(function () use ($query, $total, $chunk_size) {
      if (function_exists('set_time_limit')) {
        @set_time_limit(0);
      }

      echo '{"count":' . (int) $total . ',"data":[';
      $first = TRUE;

      for ($offset = 0; $offset < $total; $offset += $chunk_size) {
        // Clone original query and apply range for this chunk.
        $paged = clone $query;
        $paged->range($offset, $chunk_size);

        $stmt = $paged->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
          if (!$first) {
            echo ',';
          }
          echo json_encode($row, JSON_UNESCAPED_UNICODE);
          $first = FALSE;
        }

        unset($stmt);
        if (ob_get_length()) {
          @ob_flush();
        }
        @flush();
      }

      echo ']}';
    });

    $response->headers->set('Content-Type', 'application/json; charset=utf-8');
    $form_state->setResponse($response);

  }

  /**
   *
   */

  /**
   * Public function truncateSubmit(array &$form, FormStateInterface $form_state) {
   * $sql = 'Truncate table ' . $form_state->getValue('table');
   * $query = $this->connection->query($sql);
   * $result = $query->fetchAllAssoc('id', \PDO::FETCH_ASSOC);
   * if (empty($result)) {
   * $this->messenger()->addWarning($this->t('No data exists in the table.'));
   * return;
   * }
   * else {
   * $this->messenger()->addStatus($this->t('Table truncated successfully.'));
   * }
   *
   * Exit;
   * }
   */
  public function truncateSubmit(array &$form, FormStateInterface $form_state) {

    $table_name = $form_state->getValue('table');
    $reset_auto_increment = $form_state->getValue('auto_increment');

    // Now truncate the table
    // 1️⃣ Fetch all table data.
    $rows = $this->connection->select($table_name, 't')
      ->fields('t')
      ->execute()
      ->fetchAll(\PDO::FETCH_ASSOC);

    if (empty($rows)) {
      $this->messenger()->addWarning($this->t('No data exists in the table.'));
      return;
    }

    // 2️⃣ Generate CSV
    $header = array_keys(reset($rows));
    $csv = [];
    $csv[] = implode(',', $header);
    foreach ($rows as $row) {
      $csv[] = implode(',', array_map(function ($v) {
          // Ensure $v is always a string.
          $v = $v ?? '';
          return '"' . str_replace('"', '""', (string) $v) . '"';
      }, $row));
    }
    $content = implode("\n", $csv);

    // 3️⃣ Save CSV in private://
    $fs = \Drupal::service('file_system');
    $directory = 'private://asu_tuition/backup';
    $fs->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);

    $filename = $table_name . '_backup_' . date('Ymd_His') . '.csv';
    $uri = $directory . '/' . $filename;

    $fs->saveData($content, $uri, FileSystemInterface::EXISTS_REPLACE);

    // 4️⃣ Show download link + confirmation message
    $file_url_generator = \Drupal::service('file_url_generator');
    $download_url = $file_url_generator->generateAbsoluteString($uri);
    $link = Url::fromRoute('asu_tuition.download_backup', ['filename' => $filename])->toString();
    $this->messenger()->addMessage($this->t(
      'Backup created. <a href=":url">Download CSV</a> before truncating.',
      [':url' => $link]
    ));

    // Truncate the table.
    $sql = 'Truncate table ' . $form_state->getValue('table');
    $query = $this->connection->query($sql);
    $result = $query->fetchAllAssoc('id', \PDO::FETCH_ASSOC);
    // Reset auto-increment if checked.
    $this->queryLogger->saveQueryHistoryToConfig($query, 'asu_tuition_' . $table_name);
    // $this->messenger()->addStatus($this->t('Following query executed: @query', ['@query' => $query->__toString()]));
    if ($reset_auto_increment) {
      $this->connection->query("ALTER TABLE {$table_name} AUTO_INCREMENT = 1");
      $this->messenger()->addStatus(t('Auto-increment counter has been reset.'));
    }

    if (empty($result)) {
      $this->messenger()->addStatus($this->t('Table truncated successfully.'));
    }

  }

  /**
   *
   */
  public function updateSubmit(array &$form, FormStateInterface $form_state) {
    $sql = 'update ' . $form_state->getValue('table') . ' set ' . $form_state->getValue('update_fields_list') . ' = \'' . $form_state->getValue('update_value') . '\'';

    $table = $form_state->getValue('table');
    // e.g. "status".
    $field = $form_state->getValue('update_fields_list');
    $fields_to_update = $form_state->getValue('update_fields_info');
    // e.g. 1.
    $value = $form_state->getValue('update_value');
    foreach ($fields_to_update as $data) {
      if (!empty($data['field']) && !empty($data['value'])) {
        $insert_data[$data['field']] = trim($data['value']);
      }
    }
    // dpm($insert_data);
    // $sql_query = $this->connection->update($table)->fields($insert_data);
    $rows = $form_state->getValue('update_fields_info');

    foreach ($rows as $row) {
      // Collect update field/value.
      if (!empty($row['field']) && $row['value'] !== '') {
        $update_data[$row['field']] = $row['value'];
      }

      // Collect condition if given.
      if (!empty($row['update_conditiond_fields_info']['update_condition_field']) &&
        $row['update_conditiond_fields_info']['update_condition_value'] !== '') {

        $conditions[] = [
          'field' => $row['update_conditiond_fields_info']['update_condition_field'],
          'operator' => $row['update_conditiond_fields_info']['update_condition_operator'],
          'value' => $row['update_conditiond_fields_info']['update_condition_value'],
        ];
      }
    }
    $operation = $form_state->getValue('query_operations');
    // $result = $query->fetchAllAssoc('id', \PDO::FETCH_ASSOC);
    $joins = $form_state->getValue('joins');
    $result = $this->getAllResults($table, $conditions, $operation, $insert_data, $joins = []);
    if (empty($result)) {
      $this->messenger()->addWarning($this->t('No data updated in the table.'));
      // return;.
    }
    else {
      $username = \Drupal::currentUser()->getAccountName();
      $this->messenger()->addStatus($this->t('Table updated successfully.'));
      \Drupal::state()->set("{$table}_last_updated", \Drupal::time()->getCurrentTime());
      \Drupal::state()->set("{$table}_last_updated_by", $username);
    }
    // $this->queryLogger->saveQueryHistoryToConfig($sql, $table);
    // Redirect back to the same route after submit.
    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
    $urltable = $path_args[6];
    $route_name = \Drupal::routeMatch()->getRouteName();
    $params = [
      'table_name_value' => $urltable,
    ];

    $form_state->setRedirect($route_name, $params);
    // exit;.
  }

  /**
   * Submit handler for the describe table button.
   */
  public function hideDescribeSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->set('describe_table_visible', FALSE);
    $form_state->set('describe_button_visible', TRUE);

    // Optionally remove the stored describe data.
    $form_state->set('describe_table', NULL);

    // Let the user know the table was hidden.
    $this->messenger()->addStatus($this->t('Table information hidden.'));

    // Rebuild the form to reflect the new visibility.
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function describeSubmit(array &$form, FormStateInterface $form_state) {
    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
    $table = $path_args[6];
    $table_name = 'asu_tuition_' . $table;

    // Check if table exists.
    try {
      $result = $this->connection->query("SHOW TABLES LIKE :table", [':table' => $table_name])->fetchField();

      if ($result) {
        // Run DESCRIBE query.
        $description = $this->connection->query("DESCRIBE {$table_name}")->fetchAllAssoc('Field');

        // Example: debug output
        // dpm($description);
        $header = ['Field', 'Type', 'Null', 'Key', 'Default', 'Extra'];
        // Optional: show in a message.
        foreach ($description as $fieldKey => $info) {

          $table_info[$fieldKey] = [
            'field' => $info->Field,
            'type' => $info->Type,
            'null' => $info->Null,
            'key' => $info->Key,
            'default' => $info->Default ?? 'NULL',
          ];
          $table_info[$fieldKey]['extra'] = $info->Extra;

        }
        $form_state->set('describe_table', [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $table_info,
          '#attributes' => ['class' => ['describe-table']],
        ]);

        // Hide button, show table.
        $form_state->set('describe_table_visible', TRUE);
        $form_state->set('describe_button_visible', FALSE);
        // Rebuild the form to show the table.
        $form_state->setRebuild();

        // $this->messenger()->addStatus($this->t('@table info @info.', ['@table' => $table_name, '@info' => print_r($table_info, TRUE)]));
      }
      else {
        \Drupal::messenger()->addError(t('Table @table does not exist.', ['@table' => $table_name]));
      }
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError(t('Error describing table: @msg', ['@msg' => $e->getMessage()]));
    }

  }

  /**
   *
   */
  public function queryHistorySubmit(array &$form, FormStateInterface $form_state) {
    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
    $table = $path_args[6];
    $table_name = 'asu_tuition_' . $table;
    $config = $this->configFactory->getEditable('asu_tuition.' . $table_name . '_query_history');
    $history = $config->get($table_name . '_query_history') ?? [];
    if (empty($history)) {
      $this->messenger()->addWarning($this->t('No query history found.'));
      return;
    }
    $history_info = [];
    // Display history in a message.
    foreach (array_reverse($history) as $entry) {
      // dpm($entry);
      $timestamp = strtotime($entry['time']);
      $time_formatted = date('Y-m-d h:i a', $timestamp);
      $history_info[] = [
        'table_name' => $table_name,
        'query' => $entry['query'],
        'timestamp' => $time_formatted,
      ];
      $this->messenger()->addMessage($this->t(
        '<strong>Table:</strong> @table<br>
         <strong>Query:</strong> <code>@query</code><br>
         <strong>Time:</strong> @time',
        [
          '@table' => $table_name,
          '@query' => $entry['query'],
          '@time' => $time_formatted,
        ]
      ));
    }
    // $markup = $this->t('Query history for @table table', ['@table' => $history_info]);
    // $form_state->set('query_history_markup', $markup);
    $form_state->set('query_history_visible', TRUE);
    $form_state->set('query_history_button_visible', FALSE);
    // Rebuild the form to show the table.
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the describe table button.
   */
  public function hideQueryHistorySubmit(array &$form, FormStateInterface $form_state) {
    $form_state->set('query_history_visible', FALSE);
    $form_state->set('query_history_button_visible', TRUE);

    // Optionally remove the stored query history data.
    $form_state->set('query_history_markup', NULL);

    // Let the user know the query history was hidden.
    $this->messenger()->addStatus($this->t('Query history hidden.'));

    // Rebuild the form to reflect the new visibility.
    $form_state->setRebuild();
  }

  /**
   *
   */

  /**
   * Public function deleteSubmit(array &$form, FormStateInterface $form_state) {
   * $sql = trim($form_state->getValue('query'));
   * $sql = 'delete from ' . $form_state->getValue('table');
   * $sql_query = $this->connection->delete($form_state->getValue('table'));
   * $conditions = $form_state->getValue('conditions');
   * $operation = $form_state->getValue('query_operations');
   *
   * $filtered_conditions = array_filter($conditions, function ($cond) {
   * return !empty($cond['operator']) && $cond['value'] !== '';
   * });
   *
   * Optional: reindex array
   * $filtered_conditions = array_values($filtered_conditions);
   *
   *
   *
   * $result = $query->fetchAllAssoc('id', \PDO::FETCH_ASSOC);
   * $result = $this->getAllResults($form_state->getValue('table'), $filtered_conditions, $operation, $fields_to_update = [], $joins = []);
   * if (empty($result)) {
   * $this->messenger()->addWarning($this->t('No results found for this query.'));
   * return;
   * }
   * else {
   * $this->messenger()->addStatus($this->t('Data deleted successfully.'));
   * }
   *
   * exit;
   * }
   */
  public function deleteSubmit(array &$form, FormStateInterface $form_state) {
    $table = $form_state->getValue('table');
    $conditions = $form_state->getValue('conditions');

    // Filter out empty rows.
    $filtered = array_filter($conditions, function ($c) {
      return !empty($c['field']) && !empty($c['operator']) && $c['value'] !== '';
    });

    if (empty($filtered)) {
      $this->messenger()->addWarning($this->t('No valid conditions provided.'));
      return;
    }

    $query = $this->connection->delete($table);
    // Top-level AND group.
    $root_group = new Condition('AND');
    // Current OR block.
    $or_group = new Condition('OR');

    foreach ($filtered as $i => $cond) {
      $field = $cond['field'];
      $operator = strtoupper($cond['operator']);
      $value = $cond['value'];
      $logic = strtoupper($cond['logic'] ?? 'AND');

      // Convert IN string to array.
      if ($operator === 'IN' && is_string($value)) {
        $value = array_map('trim', explode(',', $value));
      }

      // Add to OR group.
      $or_group->condition($field, $value, $operator);

      // If logic == AND, close OR group and add it to root_group.
      if ($logic === 'AND' || $i === array_key_last($filtered)) {
        $root_group->condition($or_group);
        // Start a new OR group.
        $or_group = new Condition('OR');
      }
    }

    // Attach the built condition group.
    $query->condition($root_group);
    // dpm($query->__toString());
    try {
      $count = $query->execute();
      if ($count > 0) {
        $this->queryLogger->saveQueryHistoryToConfig($query, $table);
        $this->messenger()->addStatus($this->t('@count record(s) deleted successfully.', ['@count' => $count]));
      }
      else {
        $this->messenger()->addWarning($this->t('No matching records found.'));
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('custom')->error($e->getMessage());
      $this->messenger()->addError($this->t('Error: @msg', ['@msg' => $e->getMessage()]));
    }
  }

  /**
   * Helper function to get all results based on conditions.
   */

  /**
   * Perform SELECT, UPDATE, or DELETE on a table with dynamic conditions.
   *
   * @param string $operation
   *   The operation: 'select', 'update', or 'delete'.
   * @param string $table
   *   The table name.
   * @param array $conditions
   *   Array of conditions:
   *   [
   *     'field' => 'column_name',
   *     'operator' => '=', 'IN', or 'LIKE',
   *     'value' => mixed
   *   ].
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param array|null $update_fields
   *   Optional. Only for UPDATE: array of ['field' => 'value'].
   *
   * @return array|int
   *   For SELECT: array of results. For UPDATE/DELETE: number of affected rows.
   */
  public function getAllResults(
    string $table,
    array $conditions,
    string $operation,
    array $update_fields,
    array $joins,
    $distinct_fields = NULL,
    $count_field = NULL,
    $max_field = NULL,
    $batch_mode = FALSE,
  ) {

    $fields = [];
    $field_values = [];

    // Build query.
    switch (strtolower($operation)) {
      case 'select':
        $query = $this->connection->select($table, 't');
        if (!empty($distinct_fields)) {
          $query->distinct();
          foreach ($distinct_fields as $field) {
            $query->addField('t', $field);
          }
        }
        else {
          $columns = $this->connection->query("SHOW COLUMNS FROM {$table}")->fetchCol();
          // Remove node_id if it exists.
          $fields = array_diff($columns, ['nodeid']);
          // Build the select query using only allowed fields.
          $query = $this->connection->select($table, 't')->fields('t', $fields);

        }

        // Count.
        if (!empty($count_field)) {
          $query->addExpression("COUNT(t.$count_field)", 'count_result');
        }

        break;

      case 'update':
        if (empty($update_fields)) {
          throw new \InvalidArgumentException('Update fields cannot be empty for UPDATE operation.');
        }
        $query = $this->connection->update($table)->fields($update_fields);
        // $this->queryLogger->saveQueryHistoryToConfig($query, $table);
        break;

      case 'delete':
        $query = $this->connection->delete($table);
        // $this->queryLogger->saveQueryHistoryToConfig($query, $table);
        break;

      default:
        throw new \InvalidArgumentException('Unsupported operation: ' . $operation);
    }

    // Joins code.
     // Build join alias map and apply joins (deterministic aliases j1, j2, ...).
    $join_alias_map = [];
    $join_counter = 1;
    foreach ($joins as $join) {

      if (!empty($join['join_table']) && !empty($join['base_field']) && !empty($join['join_field'])) {
        // Simple alias.
        $alias = 'j' . $join_counter++;
        $join_alias_map[$alias] = $join['join_table'];
        $queryCondition = "t.{$join['base_field']} = $alias.{$join['join_field']}";
        if (!empty($join['join_type']) && strtoupper($join['join_type']) === 'LEFT') {
          $query->leftJoin($join['join_table'], $alias, $queryCondition);
        }
        elseif (!empty($join['join_type']) && strtoupper($join['join_type']) === 'RIGHT') {
          $query->rightJoin($join['join_table'], $alias, $queryCondition);
        }
        else {
          // Default/INNER.
          $query->join($join['join_table'], $alias, $queryCondition);
        }
      }
    }

    // First, group values by field and operator.
    foreach ($conditions as $condition) {
      if (isset($condition['operator'])) {
        $field_values[$condition['field']]['operator'][] = strtoupper($condition['operator']);
        $field_values[$condition['field']]['values'][] = $condition['value'] ?? NULL;
      }
    }

    // Apply conditions to the query.
    foreach ($field_values as $field => $data) {
      $operators = $data['operator'];
      $values = $data['values'];
      // Remove empty or NULL values.
      $values = array_filter($values, function ($v) {
        return $v !== '' && $v !== NULL;
      });

      foreach ($operators as $i => $op) {
        $op = strtoupper($op);
        $value = $values[$i] ?? NULL;
        $qualified_field = $this->qualifyColumn($table, $field, $join_alias_map);

        if (in_array($op, ['NULL', 'IS NULL'])) {
          $query->isNull($qualified_field);
        }
        elseif ($op === 'IS NOT NULL') {
          $query->isNotNull($qualified_field);
        }
        elseif ($op === 'LIKE') {
          // Use escapeLike and wrap with % for pattern matching.
          
          if ($value !== NULL && $value !== '') {
            $query->condition($qualified_field, '%' . \Drupal::database()->escapeLike($value) . '%', 'LIKE');
          }
        }
        else {
          // For all other operators (=, IN, BETWEEN, >=, <=, etc.)
          $value = $values[$i] ?? NULL;
          if ($value !== NULL && $value !== '') {
            if ($op === 'IN' && is_string($value)) {
              // Convert "2,3" to ["2", "3"].
              $value = array_map('trim', explode(',', $value));
            }
            //$query->condition($field, $value, $op);
            if ($value !== NULL && $value !== '') {
              $query->condition($qualified_field, $value, $op);
            }
          }
        }
      }

    }

    // dpm((string) $query);
    // Execute query.
    if ($operation === 'select') {

      // Define the field(s) you want to remove.
      // You can list multiple if needed.
      if ($table == "asu_tuition_fee_code") {
        $fieldsToRemove = ['node_id'];

        // Loop through each result and unset unwanted fields.
        foreach ($results as &$row) {
          foreach ($fieldsToRemove as $field) {
            if (isset($row->$field)) {
              unset($row->$field);
            }
          }
        }
      }

      if ($max_field) {
        // Step 1: Get max id.
        $maxQuery = $this->connection->select($table, 't');
        $maxQuery->addExpression("MAX(t.$max_field)", 'max_id');

        $result = $maxQuery->execute();
        $max_id = $result->fetchField();

        // Step 2: Fetch that row.
        if ($max_id !== NULL && $max_id !== FALSE) {
          // Reuse the same fields list.
          if (empty($fields)) {
            $columns = $this->connection->query("SHOW COLUMNS FROM {$table}")->fetchCol();
            $fields = $columns;
          }

          $row = $this->connection->select($table, 't')
            ->fields('t', $fields)
            ->condition($max_field, $max_id)
            ->range(0, 1)
            ->execute()
            ->fetchAssoc();
          $results = [];
          if ($row) {
            $row['max_id'] = $max_id;
            $results = [$row];
          }
          // Add query message and return.
          $this->messenger()->addStatus($this->t('Following query executed: @query', ['@query' => (string) $maxQuery]));
          $this->queryLogger->saveQueryHistoryToConfig($maxQuery, $table);
          return $results;
        }
        else {
          $results = [];
        }
      }
      else {
        // Normal select query.
        // $results = $query->execute()->fetchAll();
        $result = $query->execute();
        // dpm($result);
        $results = $result->fetchAll(\PDO::FETCH_ASSOC);

      }

      $args = $query->arguments();
      $sql = $query->__toString();
      // Replace placeholders with quoted values (for display only)
      foreach ($args as $key => $value) {
        $quoted = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
        $sql = str_replace($key, $quoted, $sql);
      }
      
      $this->messenger()->addStatus($this->t('Following query executed: @query', [
        '@query' => $sql,
      ]));
      $this->queryLogger->saveQueryHistoryToConfig($sql, $table);
      // Return $results;
      // $results = $this->runBatchProcess($query);
      return $results;

    }

    else {
      $transaction = $this->connection->startTransaction();
      try {
        $affected_rows = $query->execute();
        $nonselectargs = $query->arguments();
        $nonselectsql = $query->__toString();
        // Replace placeholders with quoted values (for display only)
        foreach ($nonselectargs as $key => $value) {
          $quoted = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
          $nonselectsql = str_replace($key, $quoted, $nonselectsql);
        }

        $this->queryLogger->saveQueryHistoryToConfig($nonselectsql, $table);
        // Explicit commit.
        // $transaction->commit();
        return $affected_rows;
      }
      catch (\Exception $e) {
        // $transaction->rollback();
        \Drupal::messenger()->addError(t('Database error: @msg', ['@msg' => $e->getMessage()]));
        return FALSE;
      }

    }

  }

  /**
   * Build (but do not execute) a SelectQuery based on the same inputs used by getAllResults().
   *
   * @param string $table
   *   Full table name (e.g. 'asu_tuition_fee_code').
   * @param array $conditions
   *   Array of condition rows, each like:
   *   [
   *     'field' => 'column_name',
   *     'operator' => '=', 'IN', 'LIKE', 'NULL', 'IS NOT NULL', etc.
   *     'value' => mixed,
   *     'logic' => 'AND'|'OR' (optional, currently not used for grouping here)
   *   ].
   * @param array $joins
   *   Array of joins; each join is:
   *   [
   *     'join_table' => 'other_table',
   *     'base_field' => 't_field',
   *     'join_field' => 'o_field',
   *     'join_type'  => 'INNER'|'LEFT'|'RIGHT' (optional)
   *   ].
   * @param array|null $distinct_fields
   *   Optional array of fields to SELECT DISTINCT.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   *   A SelectQuery object, not executed.
   */
  protected function buildSelectQuery(string $table, array $conditions = [], array $joins = [], ?array $distinct_fields = NULL) {
    // Base select with alias 't'.
    $query = $this->connection->select($table, 't');

    // Fields to select.
    if (!empty($distinct_fields)) {
      // Clean distinct_fields (checkbox arrays may include empty values).
      $clean = array_values(array_filter($distinct_fields, function ($v) {
        return $v !== '' && $v !== NULL;
      }));
      if (!empty($clean)) {
        $query->distinct();
        foreach ($clean as $f) {
          $query->addField('t', $f);
        }
      }
    }
    else {
      // Default: select all table columns (except nodeid as earlier).
      $columns = $this->connection->query("SHOW COLUMNS FROM {$table}")->fetchCol();
      $fields = array_diff($columns, ['nodeid']);
      if (!empty($fields)) {
        $query->fields('t', $fields);
      }
    }

    // Apply joins.
    // Build join alias map and apply joins (deterministic aliases j1, j2, ...).
    $join_alias_map = [];
    $join_counter = 1;
    foreach ($joins as $join) {
      if (empty($join['join_table']) || empty($join['base_field']) || empty($join['join_field'])) {
        continue;
      }
      // Use an alias derived from the join table name.
      //$alias = substr($join['join_table'], 0, 1);
      $alias = 'j' . $join_counter++;
      $join_alias_map[$alias] = $join['join_table'];
      $condition = "t.{$join['base_field']} = {$alias}.{$join['join_field']}";
      $type = isset($join['join_type']) ? strtoupper($join['join_type']) : 'INNER';
      if ($type === 'LEFT') {
        $query->leftJoin($join['join_table'], $alias, $condition);
      }
      elseif ($type === 'RIGHT') {
        // SelectQuery does not always expose rightJoin on all drivers; attempt it.
        if (method_exists($query, 'rightJoin')) {
          $query->rightJoin($join['join_table'], $alias, $condition);
        }
        else {
          // Fallback to join() which is typically INNER; if RIGHT required and not supported,
          // consider rewriting join logic or using a manual query.
          $query->join($join['join_table'], $alias, $condition);
        }
      }
      else {
        $query->join($join['join_table'], $alias, $condition);
      }
    }

    // Build grouped conditions: your existing code grouped by field & operator. We'll
    // support multiple operators per field by applying them in order.
    // NOTE: This is a relatively simple approach — if you need complex grouping
    // (OR groups inside AND groups) you can extend this to build Condition groups.
    $field_values = [];
    foreach ($conditions as $cond) {
      if (empty($cond['field']) || !isset($cond['operator'])) {
        continue;
      }
      $field = $cond['field'];
      $op = strtoupper($cond['operator']);
      $val = $cond['value'] ?? NULL;
      $field_values[$field]['operators'][] = $op;
      $field_values[$field]['values'][] = $val;
    }

    foreach ($field_values as $field => $data) {
      $operators = $data['operators'];
      $values = $data['values'];
      // Remove truly empty values from values array but keep indexes aligned to operators.
      // We'll just check per-index when applying.
      foreach ($operators as $i => $op) {
        $op = strtoupper($op);
        $value = $values[$i] ?? NULL;
        $qualified_field = $this->qualifyColumn($table, $field, $join_alias_map);
       
        if (in_array($op, ['NULL', 'IS NULL'], TRUE)) {
          // SelectQuery::isNull exists on Drupal select queries.
          if (method_exists($query, 'isNull')) {
            $query->isNull($qualified_field);
          }
          else {
            // Fallback to condition with SQL fragment (driver dependent).
            $query->condition($qualified_field, NULL, 'IS NULL');
          }
        }
        elseif ($op === 'IS NOT NULL') {
          if (method_exists($query, 'isNotNull')) {
            $query->isNotNull($qualified_field);
          }
          else {
            $query->condition($qualified_field, NULL, 'IS NOT NULL');
          }
        }
        elseif ($op === 'LIKE') {
          if ($value !== NULL && $value !== '') {
            $query->condition($qualified_field, '%' . $this->connection->escapeLike($value) . '%', 'LIKE');
          }
        }
        else {
          // Handle IN with string csv -> array and skip empty values.
          if ($op === 'IN' && is_string($value)) {
            $value = array_map('trim', explode(',', $value));
          }
          if ($value === '' || $value === NULL) {
            continue;
          }
          $query->condition($qualified_field, $value, $op);
        }
      }
    }

    return $query;
  }

  /**
 * Resolve a column name to a qualified alias (e.g. "t.id" or "j1.id").
 *
 * - If the column is already qualified (contains a dot), returns it unchanged.
 * - Otherwise, attempts to detect whether the column belongs to the main table
 *   ($main_table) or one of the join tables described by $join_alias_map.
 * - Uses a static cache for SHOW COLUMNS results to limit repeated schema queries.
 *
 * @param string $main_table
 *   Full main table name, e.g. 'asu_tuition_fee_rate'.
 * @param string $column
 *   Column name, possibly unqualified.
 * @param array $join_alias_map
 *   Associative array like ['j1' => 'other_table', 'j2' => 'another_table'].
 *
 * @return string
 *   Qualified column like 't.id' or 'j1.fee_code'.
 */
protected function qualifyColumn(string $main_table, string $column, array $join_alias_map = []): string {
  // Already qualified -> return as-is.
  if (strpos($column, '.') !== FALSE) {
    return $column;
  }

  static $columns_cache = [];

  $getColumns = function (string $table) use (&$columns_cache) {
    if (!isset($columns_cache[$table])) {
      try {
        $columns_cache[$table] = $this->connection->query("SHOW COLUMNS FROM {$table}")->fetchCol();
      }
      catch (\Throwable $e) {
        $columns_cache[$table] = [];
      }
    }
    return $columns_cache[$table];
  };

  // Check main table first.
  $main_cols = $getColumns($main_table);
  if (in_array($column, $main_cols, TRUE)) {
    return 't.' . $column;
  }

  // Check join tables defined in the alias map.
  foreach ($join_alias_map as $alias => $join_table) {
    $cols = $getColumns($join_table);
    if (in_array($column, $cols, TRUE)) {
      return $alias . '.' . $column;
    }
  }

  // Fallback to main table if we couldn't find it.
  return 't.' . $column;
}

}
