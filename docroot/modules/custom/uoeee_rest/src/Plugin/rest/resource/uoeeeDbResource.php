<?php

namespace Drupal\uoeee_rest\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Provides a Course Evaluation Resource
 *
 * @RestResource(
 *   id = "uoeee_db_api",
 *   label = @Translation("UOEEE batch database table edits"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "canonical" = "/api/db",
 *     "create" = "/api/db"
 *   }
 * )
 * @Cache(
 *   max-age = 0
 * )
 */


class UoeeeDbResource extends ResourceBase {
  protected $dbApiConfig;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    protected LoggerChannelFactoryInterface $loggerFactory,
    protected Connection $db,
    protected RequestStack $requestStack,
    protected AccountProxyInterface $currentUser,
    protected CsrfTokenGenerator $csrf,
    ConfigFactoryInterface $configFactory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $loggerFactory->get('uoeee_rest'));
    $this->dbApiConfig = $configFactory->get('uoeee_rest.uoeee_db_api');
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory'),
      $container->get('database'),
      $container->get('request_stack'),
      $container->get('current_user'),
      $container->get('csrf_token'),
      $container->get('config.factory')
    );
  }

  public function get() {
    $req = $this->request();
    $table = $this->requireTable($req);

    if ($req->query->has('debug_echo')) {
      return $this->json([
        'ok' => TRUE,
        'debug' => [
          'table' => $req->query->get('table'),
          'limit' => $req->query->get('limit'),
          'offset' => $req->query->get('offset'),
          'order' => $req->query->get('order'),
          'columns' => $req->query->get('columns'),
          'url' => $req->getUri(),
        ],
      ]);
    }

    $columns = $this->parseColumns($req->query->get('columns'));
    $filters = $this->parseJson($req->query->get('filters'), []);

    $cfg = $this->resolveAllowed($table);
    $this->assertAllowed($table, $columns, $filters, $cfg);

    $query = $this->db->select($table, 't');
    $fields = $columns ? array_values($columns) : $cfg['columns'];
    if (empty($fields)) {
      return $this->json(['ok' => FALSE, 'error' => 'No columns to select'], 400);
    }

    $aliasMap = [];
    $orderNameForCol = [];

    foreach ($fields as $col) {
      if ($this->isUnsafeIdent($col)) {
        $base = $this->placeholderToken($col);
        $uniq = $base;
        $i = 1;
        while (isset($aliasMap[$uniq])) {
          $uniq = $base . '_' . $i++;
        }
        $aliasMap[$uniq] = $col;
        $query->addExpression($this->quoteCol('t', $col), $uniq);
        $orderNameForCol[$col] = $uniq;
      } else {
        $query->addField('t', $col, $col);
        $orderNameForCol[$col] = "t.$col";
        $aliasMap[$col] = $col;
      }
    }

    if (!empty($filters)) {
      $this->applyConditions($query, $filters);
    }

    $orderSpecs = $this->parseOrder($req->query->get('order'), $cfg['columns']);
    if (empty($orderSpecs) && $req->query->has('limit')) {
      $orderSpecs = $this->defaultOrder($cfg['columns']);
    }
    foreach ($orderSpecs as [$col, $dir]) {
      $orderToken = $orderNameForCol[$col] ?? $col;
      $query->orderBy($orderToken, $dir);
    }

    if ($req->query->has('limit')) {
      $limit = (int)$req->query->get('limit', 0);
      $offset = (int)$req->query->get('offset', 0);
      if ($limit < 0 || $offset < 0) {
        return $this->json(['ok' => FALSE, 'error' => 'Invalid limit/offset'], 400);
      }
      $query->range($offset, $limit);
    }

    try {
      \Drupal::logger('uoeee_rest')->debug('UOEEE SQL: @sql', ['@sql' => (string) $query]);
    } catch (\Throwable $e) {}

    $rows = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

    $outRows = [];
    foreach ($rows as $r) {
      $out = [];
      foreach ($r as $k => $v) {
        if (isset($aliasMap[$k])) {
          $orig = $aliasMap[$k];
          $out[$orig] = $v;
        } else {
          $out[$k] = $v;
        }
      }
      $outRows[] = $out;
    }

    $resp = new ResourceResponse(['data' => array_values($outRows)]);
    $resp->getCacheableMetadata()->setCacheMaxAge(0);
    return $resp;
  }

  public function post($data = NULL) {
    $req = $this->request();
    $payload = is_array($data) ? $data : $this->parseJson($req->getContent(), []);
    $table = $this->requireField($payload, 'table');
    $action = strtolower($this->requireField($payload, 'action'));

    $cfg = $this->resolveAllowed($table);
    $this->assertAllowed($table);

    switch ($action) {
      case 'append':
        $rows = $payload['rows'] ?? [];
        $this->requireArray($rows, 'rows');
        if (!$rows) return $this->json(['ok' => TRUE, 'inserted' => 0]);

        $mappedRows = [];
        foreach ($rows as $i => $row) {
          if (!is_array($row)) continue;
          $clean = $this->filterColumns($table, $row);
          if ($clean) $mappedRows[] = $clean;
        }
        if (empty($mappedRows)) return $this->json(['ok' => TRUE, 'inserted' => 0]);

        $fields = [];
        foreach ($mappedRows as $r) {
          foreach (array_keys($r) as $c) {
            if (!in_array($c, $fields, TRUE)) $fields[] = $c;
          }
        }
        if (empty($fields)) return $this->json(['ok' => TRUE, 'inserted' => 0]);

        $inserted = $this->insertRawChunked($table, $fields, $mappedRows, $this->resolveChunkSize($payload));
        return $this->json(['ok' => TRUE, 'inserted' => (int)$inserted]);

      case 'update':
        return $this->update();

      case 'delete':
        $where = $payload['where'] ?? NULL;
        $ids = $payload['ids'] ?? NULL;
        $wheres = $payload['wheres'] ?? NULL;
        $chunkSize = isset($payload['chunkSize']) ? (int) $payload['chunkSize'] : (isset($payload['maxChunk']) ? (int)$payload['maxChunk'] : 50);
        $chunkSize = max(1, $chunkSize);

        $deleted_total = 0;
        $errors = [];

        if ($where !== NULL) {
          $this->requireAssoc($where, 'where');
          $deleted_total = $this->deleteRaw($table, $where);
          return $this->json(['ok' => TRUE, 'deleted' => $deleted_total, 'mode' => 'single-where']);
        }

        if ($ids !== NULL) {
          if (!is_array($ids) || empty($ids)) {
            return $this->json(['ok' => FALSE, 'error' => 'ids must be a non-empty array'], 400);
          }
          $id_field = $payload['id_field'] ?? NULL;
          if ($id_field === NULL) {
            $pks = $this->primaryKeyColumns($table);
            if (count($pks) === 1) {
              $id_field = $pks[0];
            } else {
              return $this->json(['ok' => FALSE, 'error' => 'id_field required when table has composite or no primary key'], 400);
            }
          }

          foreach (array_chunk($ids, $chunkSize) as $chunk) {
            try {
              $deleted_total += $this->deleteRaw($table, [$id_field => array_values($chunk)]);
            } catch (\Throwable $e) {
              $errors[] = ['mode' => 'ids_chunk', 'err' => $e->getMessage(), 'chunk_size' => count($chunk)];
            }
          }

          return $this->json(['ok' => TRUE, 'deleted' => $deleted_total, 'mode' => 'ids', 'errors' => $errors]);
        }

        if ($wheres !== NULL) {
          if (!is_array($wheres) || $wheres === []) {
            return $this->json(['ok' => FALSE, 'error' => 'wheres must be a non-empty array'], 400);
          }

          foreach ($wheres as $i => $w) {
            if (!is_array($w) || $this->isAssoc($w) === FALSE) {
              return $this->json(['ok' => FALSE, 'error' => "wheres[$i] must be an object (associative array)"], 400);
            }
          }

          $singleKeyName = NULL;
          $all_single_same = TRUE;
          foreach ($wheres as $w) {
            $keys = array_keys($w);
            if (count($keys) !== 1) { $all_single_same = FALSE; break; }
            if ($singleKeyName === NULL) $singleKeyName = $keys[0];
            elseif ($singleKeyName !== $keys[0]) { $all_single_same = FALSE; break; }
          }

          if ($all_single_same && $singleKeyName !== NULL) {
            $vals = array_map(function($w) use ($singleKeyName) { return $w[$singleKeyName]; }, $wheres);
            foreach (array_chunk($vals, $chunkSize) as $chunk) {
              try {
                $deleted_total += $this->deleteRaw($table, [$singleKeyName => array_values($chunk)]);
              } catch (\Throwable $e) {
                $errors[] = ['mode' => 'wheres_in_chunk', 'err' => $e->getMessage(), 'chunk_size' => count($chunk)];
              }
            }
            return $this->json(['ok' => TRUE, 'deleted' => $deleted_total, 'mode' => 'wheres-in', 'column' => $singleKeyName, 'errors' => $errors]);
          }

          foreach (array_chunk($wheres, $chunkSize) as $chunkIndex => $chunk) {
            try {
              foreach ($chunk as $w) {
                $deleted_total += $this->deleteRaw($table, $w);
              }
            } catch (\Throwable $e) {
              $errors[] = ['mode' => 'wheres_perrow_chunk', 'err' => $e->getMessage(), 'chunk_index' => $chunkIndex];
            }
          }

          return $this->json(['ok' => TRUE, 'deleted' => $deleted_total, 'mode' => 'wheres-per-row', 'errors' => $errors]);
        }

        return $this->json(['ok' => FALSE, 'error' => 'Invalid delete payload: provide where OR ids (+id_field) OR wheres[]'], 400);

      case 'copyedit':
        $where = $payload['where'] ?? [];
        $this->requireAssoc($where, 'where');

        $set   = $payload['set'] ?? [];
        $this->requireAssoc($set, 'set');

        $limit = isset($payload['limit']) ? (int) $payload['limit'] : NULL;
        $drop_pk_if_absent = array_key_exists('drop_pk_if_absent', $payload) ? (bool) $payload['drop_pk_if_absent'] : TRUE;

        $cfg = $this->resolveAllowed($table);
        $this->assertAllowed($table, [], $where, $cfg);
        foreach (array_keys($set) as $c) {
          if (!in_array($c, $cfg['writable'], TRUE)) {
            return $this->json(['ok' => FALSE, 'error' => "Column not writable: $c"], 400);
          }
        }

        $q = $this->db->select($table, 't');
        $q->fields('t', $cfg['columns']);
        foreach ($where as $c => $v) {
          $q->condition($c, $v);
        }
        if ($limit !== NULL) {
          $q->range(0, $limit);
        }
        $rows = $q->execute()->fetchAll(\PDO::FETCH_ASSOC);

        if (!$rows) {
          return $this->json(['ok' => TRUE, 'copied' => 0, 'note' => 'No source rows matched']);
        }

        $pk_cols = $this->primaryKeyColumns($table);

        $copied = 0;
        foreach ($rows as $row) {
          $new = $row + $set;
          foreach ($set as $k => $v) $new[$k] = $v;

          if ($drop_pk_if_absent && $pk_cols) {
            foreach ($pk_cols as $pk) {
              if (!array_key_exists($pk, $set)) unset($new[$pk]);
            }
          }

          $clean = $this->filterColumns($table, $new);
          if (!$clean) continue;

          $fields = array_keys($clean);
          $this->insertRawChunked($table, $fields, [$clean], $this->resolveChunkSize($payload));
          $copied++;
        }

        return $this->json(['ok' => TRUE, 'copied' => $copied]);

      default:
        return $this->json(['ok' => FALSE, 'error' => 'Unknown action'], 400);
    }
  }

  public function update(): ResourceResponse {
    $p = $this->getJsonPayload();
    $table = $p['table'] ?? null;
    $error_join = '';

    if (!$table) {
      return $this->json(['ok' => FALSE, 'error' => 'Missing table'], 400);
    }

    if (!empty($p['keys']) && is_array($p['keys']) && !empty($p['updates']) && is_array($p['updates'])) {
      $keys = array_values($p['keys']);
      $rows = [];

      foreach ($p['updates'] as $i => $u) {
        if (!isset($u['key']) || !is_array($u['key'])) {
          return $this->json(['ok'=>FALSE,'error'=>"updates[$i] missing key object"], 400);
        }
        foreach ($keys as $kc) {
          if (!array_key_exists($kc, $u['key'])) {
            return $this->json(['ok'=>FALSE,'error'=>"updates[$i] missing key column '$kc'"], 400);
          }
        }
        $vals = $this->filterColumns($table, $u['values'] ?? []);
        if (!$vals) continue;
        $rows[] = ['key' => $u['key'], 'values' => $vals];
      }

      if (!$rows) {
        return $this->json(['ok'=>FALSE,'error'=>'No updatable values in updates'], 400);
      }

      $maxChunk = isset($p['maxChunk']) && is_int($p['maxChunk']) && $p['maxChunk'] > 0 ? $p['maxChunk'] : 300;
      $total = 0; $mode = 'single-update';
      $attempted = count($rows);
      try {
        foreach (array_chunk($rows, $maxChunk) as $chunk) {
          $total += $this->batchUpdateJoin($table, $keys, $chunk);
        }
      } catch (\Throwable $e) {
        $this->logger->error('batchUpdateJoin failed: @msg', ['@msg' => $e->getMessage()]);
        $error_join = $e->getMessage();
        $trx = $this->db->startTransaction();
        $mode = 'per-row';
        foreach ($rows as $r) {
          $q = $this->db->update($table)->fields($r['values']);
          foreach ($keys as $kc) $q->condition($kc, $r['key'][$kc]);
          $total += (int)$q->execute();
        }
      }

      return $this->json(['ok' => TRUE, 'updated' => $total, 'attempted' => $attempted, 'error_join' => $error_join, 'mode' => $mode]);
    }

    if (!empty($p['where']) && is_array($p['where'])) {
      $where = $p['where'];
      $this->requireAssoc($where, 'where');
      $values = $this->filterColumns($table, $p['values'] ?? []);
      if (!$values) {
        return $this->json(['ok'=>FALSE, 'error'=>'No updatable columns provided'], 400);
      }
      $count = $this->updateRaw($table, $values, $where);
      return $this->json(['ok'=>TRUE, 'updated'=>$count, 'mode'=>'single-legacy-raw']);
    }

    return $this->json(['ok'=>FALSE, 'error'=>'Invalid payload: provide either {keys[], updates[]} for batch or {where, values} for single'], 400);
  }

  private function batchUpdateJoin(string $table, array $keys, array $rows): int {
    if (count($keys) !== 1) {
      throw new \InvalidArgumentException('batchUpdateJoin currently supports exactly one key column.');
    }
    $kcol = $keys[0];
    $conn = $this->db;

    $uniq = [];
    foreach ($rows as $r) $uniq[(string)$r['key'][$kcol]] = $r;
    $rows = array_values($uniq);

    $valueCols = [];
    foreach ($rows as $r) {
      foreach (array_keys($r['values']) as $c) $valueCols[$c] = true;
    }
    if (!$valueCols) return 0;
    $valueCols = array_keys($valueCols);

    $selects = []; $args = []; $i = 0;
    foreach ($rows as $r) {
      $i++;
      $cols = [];
      $pkPh = ":k{$i}";
      $cols[] = "$pkPh AS `{$kcol}`";
      $args[$pkPh] = $r['key'][$kcol];

      foreach ($valueCols as $c) {
        $token = $this->placeholderToken($c);
        $phV = ":" . $token . "_v{$i}";
        $phS = ":" . $token . "_s{$i}";

        $cols[] = "{$phV} AS `{$c}`";
        $cols[] = "{$phS} AS `{$c}__set`";

        $has = array_key_exists($c, $r['values']);
        $args[$phV] = $has ? $r['values'][$c] : NULL;
        $args[$phS] = $has ? 1 : 0;
      }
      $selects[] = 'SELECT ' . implode(', ', $cols);
    }
    $derived = implode("\nUNION ALL\n", $selects);

    $setClauses = [];
    foreach ($valueCols as $c) $setClauses[] = "t.`{$c}` = IF(u.`{$c}__set` = 1, u.`{$c}`, t.`{$c}`)";

    $diffPreds = [];
    foreach ($valueCols as $c) {
      $diffPreds[] =
        "(u.`{$c}__set`=1 AND (" .
          "(u.`{$c}` IS NULL AND t.`{$c}` IS NOT NULL) OR " .
          "(u.`{$c}` IS NOT NULL AND t.`{$c}` IS NULL) OR " .
          "(u.`{$c}` <> t.`{$c}`)" .
        "))";
    }
    $whereDiff = '(' . implode(' OR ', $diffPreds) . ')';

    $countSql =
      "SELECT COUNT(*)
      FROM {" . $table . "} t
      JOIN (
        {$derived}
      ) u ON u.`{$kcol}` = t.`{$kcol}`
      WHERE {$whereDiff}";
    $countSql = $conn->prefixTables($countSql);
    $willChange = (int) $conn->query($countSql, $args)->fetchField();

    if ($willChange === 0) return 0;

    $updateSql =
      "UPDATE {" . $table . "} t
      JOIN (
        {$derived}
      ) u ON u.`{$kcol}` = t.`{$kcol}`
      SET " . implode(', ', $setClauses) . "
      WHERE {$whereDiff}";
    $updateSql = $conn->prefixTables($updateSql);
    $conn->query($updateSql, $args);

    return $willChange;
  }

  protected function insertRawChunked(string $table, array $fields, array $rows, int $chunkSize): int {
    $total = 0;
    foreach (array_chunk($rows, $chunkSize) as $chunk) {
      $total += $this->insertRawMulti($table, $fields, $chunk);
    }
    return $total;
  }

  protected function insertRawMulti(string $table, array $fields, array $rows): int {
    $quoted = [];
    foreach ($fields as $c) $quoted[] = '`' . str_replace('`', '``', $c) . '`';

    $args = [];
    $groups = [];
    foreach ($rows as $r) {
      $vals = [];
      foreach ($fields as $f) $vals[] = array_key_exists($f, $r) ? $r[$f] : NULL;
      $groups[] = '(' . implode(',', array_fill(0, count($vals), '?')) . ')';
      foreach ($vals as $v) $args[] = $v;
    }

    $sql = "INSERT INTO {" . $table . "} (" . implode(',', $quoted) . ") VALUES " . implode(',', $groups);
    $sql = $this->db->prefixTables($sql);
    $this->db->query($sql, $args);
    return count($rows);
  }

  protected function updateRaw(string $table, array $values, array $where): int {
    $conn = $this->db;
    [$whereSql, $whereArgs] = $this->buildWhere($where);

    $countSql = "SELECT COUNT(*) FROM {" . $table . "} WHERE $whereSql";
    $countSql = $conn->prefixTables($countSql);
    $count = (int)$conn->query($countSql, $whereArgs)->fetchField();
    if ($count === 0) return 0;

    $set = [];
    $args = [];
    foreach ($values as $c => $v) {
      $set[] = '`' . str_replace('`', '``', $c) . '` = ?';
      $args[] = $v;
    }

    $sql = "UPDATE {" . $table . "} SET " . implode(',', $set) . " WHERE $whereSql";
    $sql = $conn->prefixTables($sql);
    $conn->query($sql, array_merge($args, $whereArgs));
    return $count;
  }

  protected function deleteRaw(string $table, array $where): int {
    $conn = $this->db;
    [$whereSql, $args] = $this->buildWhere($where);

    $countSql = "SELECT COUNT(*) FROM {" . $table . "} WHERE $whereSql";
    $countSql = $conn->prefixTables($countSql);
    $count = (int)$conn->query($countSql, $args)->fetchField();
    if ($count === 0) return 0;

    $sql = "DELETE FROM {" . $table . "} WHERE $whereSql";
    $sql = $conn->prefixTables($sql);
    $conn->query($sql, $args);
    return $count;
  }

  protected function buildWhere(array $where): array {
    $parts = [];
    $args = [];

    foreach ($where as $c => $expr) {
      $col = '`' . str_replace('`', '``', $c) . '`';

      if (!is_array($expr) || $this->isAssoc($expr) === FALSE) {
        if ($expr === NULL) {
          $parts[] = "$col IS NULL";
        } else {
          $parts[] = "$col = ?";
          $args[] = $expr;
        }
        continue;
      }

      $ops = array_change_key_case($expr, CASE_LOWER);

      if (array_key_exists('in', $ops)) {
        $vals = $ops['in'];
        if (!is_array($vals) || $vals === []) throw new BadRequestHttpException("Operator 'in' for $c requires a non-empty array");
        $place = implode(',', array_fill(0, count($vals), '?'));
        $parts[] = "$col IN ($place)";
        foreach ($vals as $v) $args[] = $v;
        continue;
      }

      if (array_key_exists('not_in', $ops)) {
        $vals = $ops['not_in'];
        if (!is_array($vals) || $vals === []) throw new BadRequestHttpException("Operator 'not_in' for $c requires a non-empty array");
        $place = implode(',', array_fill(0, count($vals), '?'));
        $parts[] = "$col NOT IN ($place)";
        foreach ($vals as $v) $args[] = $v;
        continue;
      }

      if (array_key_exists('like', $ops)) {
        $parts[] = "$col LIKE ?";
        $args[] = $ops['like'];
        continue;
      }

      if (array_key_exists('not_like', $ops)) {
        $parts[] = "$col NOT LIKE ?";
        $args[] = $ops['not_like'];
        continue;
      }

      if (array_key_exists('between', $ops)) {
        $bounds = $ops['between'];
        if (!is_array($bounds) || count($bounds) !== 2) throw new BadRequestHttpException("Operator 'between' for $c requires [min, max]");
        $parts[] = "$col BETWEEN ? AND ?";
        $args[] = $bounds[0];
        $args[] = $bounds[1];
        continue;
      }

      if (array_key_exists('is_null', $ops)) {
        $isNull = (bool)$ops['is_null'];
        $parts[] = $isNull ? "$col IS NULL" : "$col IS NOT NULL";
        continue;
      }

      $map = ['gt'=>'>', 'gte'=>'>=', 'lt'=>'<', 'lte'=>'<=', 'neq'=>'<>'];
      $matched = FALSE;
      foreach ($map as $k => $sqlop) {
        if (isset($ops[$k])) {
          $parts[] = "$col $sqlop ?";
          $args[] = $ops[$k];
          $matched = TRUE;
        }
      }
      if ($matched) continue;

      throw new BadRequestHttpException("Unsupported operator for $c");
    }

    return [implode(' AND ', $parts), $args];
  }

  protected function request(): Request {
    return $this->requestStack->getCurrentRequest();
  }

  protected function json(array $payload, int $status = 200): ResourceResponse {
    $resp = new ResourceResponse($payload, $status);
    $resp->getCacheableMetadata()->setCacheMaxAge(0);
    return $resp;
  }

  protected function getJsonPayload(): array {
    $req = $this->request();
    $raw = $req->getContent();
    $data = $this->parseJson($raw, []);
    return is_array($data) ? $data : [];
  }

  protected function quoteCol(string $alias, string $col): string {
    $col = str_replace('`', '``', $col);
    return "{$alias}.`{$col}`";
  }

  protected function isUnsafeIdent(string $col): bool {
    return (bool) preg_match('/[^A-Za-z0-9_]/', $col);
  }

  protected function placeholderToken(string $name): string {
    $t = preg_replace('/[^A-Za-z0-9_]/', '_', $name);
    $t = preg_replace('/_+/', '_', $t);
    $t = trim($t, '_');
    if ($t === '') return 'col_' . substr(md5($name), 0, 8);
    return $t;
  }

  protected function mapInputToColumn(string $input, array $allowedCols): ?string {
    if (in_array($input, $allowedCols, TRUE)) return $input;
    foreach ($allowedCols as $c) {
      if (strcasecmp($c, $input) === 0) return $c;
    }
    $normIn = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $input));
    foreach ($allowedCols as $c) {
      $normC = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $c));
      if ($normC !== '' && $normC === $normIn) return $c;
    }
    return NULL;
  }

  protected function defaultOrder(array $allowed): array {
    foreach (['updated', 'changed', 'created'] as $recentCol) {
      if (in_array($recentCol, $allowed, TRUE)) return [[$recentCol, 'DESC']];
    }
    if (in_array('id', $allowed, TRUE)) return [['id', 'ASC']];
    return [];
  }

  protected function applyConditions($query, array $where): void {
    foreach ($where as $col => $expr) {
      if (!is_array($expr) || $this->isAssoc($expr) === FALSE) {
        $query->condition($col, $expr);
        continue;
      }

      $ops = array_change_key_case($expr, CASE_LOWER);

      if (isset($ops['like'])) {
        $query->condition($col, (string) $ops['like'], 'LIKE');
        continue;
      }
      if (isset($ops['not_like'])) {
        $query->condition($col, (string) $ops['not_like'], 'NOT LIKE');
        continue;
      }
      if (isset($ops['in'])) {
        $vals = $ops['in'];
        if (!is_array($vals) || $vals === []) throw new BadRequestHttpException("Operator 'in' for $col requires a non-empty array");
        $query->condition($col, array_values($vals), 'IN');
        continue;
      }
      if (isset($ops['not_in'])) {
        $vals = $ops['not_in'];
        if (!is_array($vals) || $vals === []) throw new BadRequestHttpException("Operator 'not_in' for $col requires a non-empty array");
        $query->condition($col, array_values($vals), 'NOT IN');
        continue;
      }
      if (isset($ops['between'])) {
        $bounds = $ops['between'];
        if (!is_array($bounds) || count($bounds) !== 2) throw new BadRequestHttpException("Operator 'between' for $col requires [min, max]");
        $query->condition($col, $bounds[0], '>=');
        $query->condition($col, $bounds[1], '<=');
        continue;
      }
      if (array_key_exists('is_null', $ops)) {
        $isNull = (bool) $ops['is_null'];
        $query->isNull($col, $isNull);
        continue;
      }

      $map = ['gt' => '>', 'gte' => '>=', 'lt' => '<', 'lte' => '<=', 'neq' => '<>'];
      $matched = FALSE;
      foreach ($map as $k => $sqlop) {
        if (isset($ops[$k])) {
          $query->condition($col, $ops[$k], $sqlop);
          $matched = TRUE;
        }
      }
      if ($matched) continue;

      throw new BadRequestHttpException("Unsupported operator for $col");
    }
  }

  protected function isAssoc(array $arr): bool {
    return array_keys($arr) !== range(0, count($arr) - 1);
  }

  protected function parseOrder(?string $raw, array $allowed): array {
    if (!$raw) return [];
    $pairs = [];

    $json = json_decode($raw, TRUE);
    if (is_array($json)) {
      foreach ($json as $spec) {
        $col = $spec['col'] ?? $spec['column'] ?? null;
        $dir = strtoupper($spec['dir'] ?? $spec['direction'] ?? 'ASC');
        if ($col && in_array($col, $allowed, TRUE)) $pairs[] = [$col, $dir === 'DESC' ? 'DESC' : 'ASC'];
      }
      return $pairs;
    }

    foreach (explode(',', $raw) as $token) {
      $token = trim($token);
      if ($token === '') continue;
      $dir = 'ASC';
      if ($token[0] === '-') { $dir = 'DESC'; $token = substr($token, 1); }
      if ($token[0] === '+') { $token = substr($token, 1); }
      if (in_array($token, $allowed, TRUE)) $pairs[] = [$token, $dir];
    }
    return $pairs;
  }

  protected function parseJson($raw, $fallback = []) {
    if (is_array($raw)) return $raw;
    if (!is_string($raw) || $raw === '') return $fallback;
    $data = json_decode($raw, TRUE);
    return is_array($data) ? $data : $fallback;
  }

  protected function parseColumns(?string $csv): array {
    if (!$csv) return [];
    return array_values(array_filter(array_map('trim', explode(',', $csv))));
  }

  protected function primaryKeyColumns(string $table): array {
    static $cache = [];
    if (isset($cache[$table])) return $cache[$table];

    $schema = $this->db->query('SELECT DATABASE()')->fetchField();
    $sql = "
      SELECT k.COLUMN_NAME
      FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
      JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
        ON t.CONSTRAINT_NAME = k.CONSTRAINT_NAME
      AND t.TABLE_SCHEMA = k.TABLE_SCHEMA
      AND t.TABLE_NAME = k.TABLE_NAME
      WHERE t.TABLE_SCHEMA = :db
        AND t.TABLE_NAME = :table
        AND t.CONSTRAINT_TYPE = 'PRIMARY KEY'
      ORDER BY k.ORDINAL_POSITION
    ";
    $cols = $this->db->query($sql, [':db' => $schema, ':table' => $table])->fetchCol();
    return $cache[$table] = array_values($cols ?: []);
  }

  protected function normalizePolicy(string $table, array $cfg): array {
    $cols = $cfg['columns'] ?? '*';
    if ($cols === '*') $cfg['columns'] = $this->allColumns($table);
    elseif (is_string($cols)) $cfg['columns'] = [$cols];

    $w = $cfg['writable'] ?? $cfg['columns'];
    if ($w === '*') $cfg['writable'] = $cfg['columns'];
    elseif (is_string($w)) $cfg['writable'] = [$w];

    if (!empty($cfg['deny_writable'])) $cfg['writable'] = array_values(array_diff($cfg['writable'], $cfg['deny_writable']));

    return $cfg;
  }

  protected function resolveAllowed(string $table): array {
    $tables = $this->allowedTables();
    if (isset($tables[$table])) return $this->normalizePolicy($table, $tables[$table]);
    foreach ($this->tablePatterns() as $pattern => $cfg) {
      $phpPattern = str_replace('%', '*', $pattern);
      if (fnmatch($phpPattern, $table)) {
        $defaults = $this->dbApiConfig->get('defaults') ?? ['columns'=>'*','writable'=>'*','deny_writable'=>[]];
        return $this->normalizePolicy($table, $cfg + $defaults);
      }
    }
    throw new AccessDeniedHttpException('Table not allowed');
  }

  protected function allColumns(string $table): array {
    static $cache = [];
    if (isset($cache[$table])) return $cache[$table];

    $schema = $this->db->query('SELECT DATABASE()')->fetchField();
    $result = $this->db->query(
      "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table ORDER BY ORDINAL_POSITION",
      [':db' => $schema, ':table' => $table]
    )->fetchCol();

    return $cache[$table] = array_values($result ?: []);
  }

  protected function requireTable(Request $req): string {
    $table = $req->query->get('table');
    if (!$table) throw new BadRequestHttpException('Missing table');
    $this->assertAllowed($table);
    return $table;
  }

  protected function requireField(array $payload, string $key): mixed {
    if (!array_key_exists($key, $payload) || $payload[$key] === '' || $payload[$key] === NULL) {
      throw new BadRequestHttpException("Missing $key");
    }
    return $payload[$key];
  }

  protected function requireArray(mixed $v, string $name): void {
    if (!is_array($v) || $v === []) {
      throw new BadRequestHttpException("Missing/empty $name");
    }
  }

  protected function requireAssoc(mixed $v, string $name): void {
    $this->requireArray($v, $name);
    if (array_values($v) === $v) {
      throw new BadRequestHttpException("$name must be an object");
    }
  }

  protected function allowedTables(): array {
    $defaults = $this->dbApiConfig->get('defaults') ?? [
      'columns' => '*',
      'writable' => '*',
      'deny_writable' => [],
    ];
    $tables = $this->dbApiConfig->get('tables') ?? [];
    foreach ($tables as $name => &$cfg) $cfg = $cfg + $defaults;
    return $tables;
  }

  protected function tablePatterns(): array {
    return $this->dbApiConfig->get('patterns') ?? [];
  }

  protected function assertAllowed(string $table, array $columns = [], array $filters = [], ?array $cfg = NULL): void {
    $cfg = $cfg ?? $this->resolveAllowed($table);
    if ($columns) {
      foreach ($columns as $c) {
        if (!in_array($c, $cfg['columns'], TRUE)) throw new BadRequestHttpException("Column not allowed: $c");
      }
    }
    if ($filters) {
      foreach (array_keys($filters) as $c) {
        if (!in_array($c, $cfg['columns'], TRUE)) throw new BadRequestHttpException("Filter column not allowed: $c");
      }
    }
  }

  protected function filterColumns(string $table, array $row): array {
    $cfg = $this->resolveAllowed($table);
    $writable = $cfg['writable'] ?? [];
    $out = [];
    foreach ($row as $inputKey => $val) {
      $dbcol = $this->mapInputToColumn((string)$inputKey, $writable);
      if ($dbcol === NULL) continue;
      $out[$dbcol] = $val;
    }
    return $out;
  }

  protected function resolveChunkSize(array $payload): int {
    if (isset($payload['chunkSize']) && is_int($payload['chunkSize']) && $payload['chunkSize'] > 0) return (int)$payload['chunkSize'];
    if (isset($payload['maxChunk']) && is_int($payload['maxChunk']) && $payload['maxChunk'] > 0) return (int)$payload['maxChunk'];
    return 500;
  }
}
